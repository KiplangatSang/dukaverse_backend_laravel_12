<?php

namespace App\Http\Controllers;

use App\Models\EmailConfig;
use App\Models\EmailNotification;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmailController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Get all email configs
     */
    public function getConfigs(): JsonResponse
    {
        $configs = EmailConfig::all();
        return response()->json($configs);
    }

    /**
     * Create or update email config
     */
    public function storeConfig(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_name' => 'required|string|unique:email_configs,client_name',
            'imap_host' => 'required|string',
            'imap_port' => 'integer',
            'imap_encryption' => 'string',
            'imap_username' => 'required|string',
            'imap_password' => 'required|string',
            'smtp_host' => 'required|string',
            'smtp_port' => 'integer',
            'smtp_encryption' => 'string',
            'smtp_username' => 'required|string',
            'smtp_password' => 'required|string',
            'from_email' => 'required|email',
            'from_name' => 'string|nullable',
            'active' => 'boolean',
        ]);

        $config = EmailConfig::create($data);
        return response()->json($config, 201);
    }

    /**
     * Update email config
     */
    public function updateConfig(Request $request, EmailConfig $config): JsonResponse
    {
        $data = $request->validate([
            'client_name' => 'string|unique:email_configs,client_name,' . $config->id,
            'imap_host' => 'string',
            'imap_port' => 'integer',
            'imap_encryption' => 'string',
            'imap_username' => 'string',
            'imap_password' => 'string',
            'smtp_host' => 'string',
            'smtp_port' => 'integer',
            'smtp_encryption' => 'string',
            'smtp_username' => 'string',
            'smtp_password' => 'string',
            'from_email' => 'email',
            'from_name' => 'string|nullable',
            'active' => 'boolean',
        ]);

        $config->update($data);
        return response()->json($config);
    }

    /**
     * Get email notifications
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $query = EmailNotification::with('emailConfig');

        if ($request->has('processed')) {
            $query->where('processed', $request->boolean('processed'));
        }

        $notifications = $query->paginate(50);
        return response()->json($notifications);
    }

    /**
     * Send email
     */
    public function sendEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'config_id' => 'required|exists:email_configs,id',
            'to' => 'required|array',
            'to.*' => 'email',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        $config = EmailConfig::find($data['config_id']);
        $this->emailService->sendEmail($data, $config);

        return response()->json(['message' => 'Email sent successfully']);
    }

    /**
     * Mark notification as processed
     */
    public function markProcessed(EmailNotification $notification): JsonResponse
    {
        $notification->update(['processed' => true]);
        return response()->json($notification);
    }
}
