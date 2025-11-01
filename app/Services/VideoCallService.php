<?php

namespace App\Services;

use App\Models\VideoCall;
use App\Models\VideoCallParticipant;
use App\Models\VideoCallPermission;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class VideoCallService extends BaseService
{
    protected $firestore;
    protected $messaging;

    public function __construct(
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);

        // Initialize Firebase
        $firebase = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));

        $this->firestore = $firebase->createFirestore();
        $this->messaging = $firebase->createMessaging();
    }

    public function createRoom(array $data)
    {
        try {
            $roomId = $this->generateRoomId();
            $user = $this->user();

            $videoCall = VideoCall::create([
                'room_id' => $roomId,
                'initiator_id' => $user->id,
                'participants' => [$user->id],
                'status' => 'waiting',
                'settings' => $data['settings'] ?? []
            ]);

            // Add initiator as participant
            VideoCallParticipant::create([
                'video_call_id' => $videoCall->id,
                'user_id' => $user->id,
                'role' => 'host',
                'joined_at' => now()
            ]);

            // Create Firestore chat collection
            $this->initializeChatCollection($roomId);

            return $this->responseHelper->respond([
                'room_id' => $roomId,
                'video_call' => $videoCall
            ], 'Video call room created successfully');

        } catch (\Exception $e) {
            return $this->responseHelper->error('Failed to create video call room', 500);
        }
    }

    public function joinRoom(string $roomId)
    {
        try {
            $videoCall = VideoCall::where('room_id', $roomId)->first();

            if (!$videoCall) {
                return $this->responseHelper->error('Video call room not found', 404);
            }

            if ($videoCall->isEnded()) {
                return $this->responseHelper->error('Video call has ended', 400);
            }

            $user = $this->user();

            // Check if user is already a participant
            $existingParticipant = $videoCall->participants()
                ->where('user_id', $user->id)
                ->whereNull('left_at')
                ->first();

            if ($existingParticipant) {
                return $this->responseHelper->error('Already joined this call', 400);
            }

            // Add as participant
            $participant = VideoCallParticipant::create([
                'video_call_id' => $videoCall->id,
                'user_id' => $user->id,
                'role' => 'participant',
                'joined_at' => now()
            ]);

            // Update call status if first participant joins
            if ($videoCall->status === 'waiting') {
                $videoCall->update([
                    'status' => 'active',
                    'started_at' => now()
                ]);
            }

            // Send notification to other participants
            $this->notifyParticipants($videoCall, $user, 'joined');

            return $this->responseHelper->respond([
                'participant' => $participant,
                'video_call' => $videoCall->load('participants')
            ], 'Joined video call successfully');

        } catch (\Exception $e) {
            return $this->responseHelper->error('Failed to join video call', 500);
        }
    }

    public function leaveRoom(string $roomId)
    {
        try {
            $videoCall = VideoCall::where('room_id', $roomId)->first();
            $user = $this->user();

            if (!$videoCall) {
                return $this->responseHelper->error('Video call room not found', 404);
            }

            $participant = $videoCall->participants()
                ->where('user_id', $user->id)
                ->whereNull('left_at')
                ->first();

            if (!$participant) {
                return $this->responseHelper->error('Not a participant in this call', 400);
            }

            // Mark participant as left
            $participant->update(['left_at' => now()]);

            // Send notification
            $this->notifyParticipants($videoCall, $user, 'left');

            // Check if call should end (no active participants)
            $activeParticipants = $videoCall->getActiveParticipants();
            if ($activeParticipants->isEmpty()) {
                $videoCall->update([
                    'status' => 'ended',
                    'ended_at' => now()
                ]);
            }

            return $this->responseHelper->respond([], 'Left video call successfully');

        } catch (\Exception $e) {
            return $this->responseHelper->error('Failed to leave video call', 500);
        }
    }

    public function sendMessage(string $roomId, string $message, string $type = 'text')
    {
        try {
            $videoCall = VideoCall::where('room_id', $roomId)->first();
            $user = $this->user();

            if (!$videoCall) {
                return $this->responseHelper->error('Video call room not found', 404);
            }

            // Check if user can send messages
            $permission = $this->getUserPermission($user->id);
            if (!$permission || !$permission->can_send_messages) {
                return $this->responseHelper->error('Not authorized to send messages', 403);
            }

            // Store message in Firestore
            $messageData = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'message' => $message,
                'type' => $type,
                'timestamp' => now()->toISOString()
            ];

            $collection = $this->firestore->database()->collection('video_calls')
                ->document($roomId)
                ->collection('messages');

            $collection->add($messageData);

            // Send real-time notification
            $this->notifyParticipants($videoCall, $user, 'message', $messageData);

            return $this->responseHelper->respond($messageData, 'Message sent successfully');

        } catch (\Exception $e) {
            return $this->responseHelper->error('Failed to send message', 500);
        }
    }

    public function getMessages(string $roomId)
    {
        try {
            $collection = $this->firestore->database()->collection('video_calls')
                ->document($roomId)
                ->collection('messages');

            $documents = $collection->orderBy('timestamp')->documents();
            $messages = [];

            foreach ($documents as $document) {
                $messages[] = $document->data();
            }

            return $this->responseHelper->respond($messages, 'Messages retrieved successfully');

        } catch (\Exception $e) {
            return $this->responseHelper->error('Failed to retrieve messages', 500);
        }
    }

    protected function generateRoomId(): string
    {
        do {
            $roomId = Str::random(10);
        } while (VideoCall::where('room_id', $roomId)->exists());

        return $roomId;
    }

    protected function initializeChatCollection(string $roomId)
    {
        // Create initial chat collection in Firestore
        $this->firestore->database()->collection('video_calls')->document($roomId);
    }

    protected function notifyParticipants(VideoCall $videoCall, $sender, string $action, array $data = [])
    {
        $participants = $videoCall->getActiveParticipants()
            ->where('user_id', '!=', $sender->id);

        foreach ($participants as $participant) {
            $message = CloudMessage::withTarget('token', $participant->user->fcm_token)
                ->withNotification(Notification::create(
                    "Video Call Update",
                    "{$sender->name} {$action} the call"
                ))
                ->withData([
                    'room_id' => $videoCall->room_id,
                    'action' => $action,
                    'sender_id' => $sender->id,
                    'data' => json_encode($data)
                ]);

            try {
                $this->messaging->send($message);
            } catch (\Exception $e) {
                // Log notification failure
                Log::error('Failed to send FCM notification: ' . $e->getMessage());
            }
        }
    }

    protected function getUserPermission(int $userId): ?VideoCallPermission
    {
        return VideoCallPermission::where('user_id', $userId)->first();
    }
}
