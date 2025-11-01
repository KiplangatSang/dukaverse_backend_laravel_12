<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerificationController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    // use VerifiesEmails;

    // /**
    //  * Where to redirect users after verification.
    //  *
    //  * @var string
    //  */
    // protected $redirectTo = RouteServiceProvider::HOME;

    // /**
    //  * Create a new controller instance.
    //  *
    //  * @return void
    //  */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('signed')->only('verify');
    //     $this->middleware('throttle:6,1')->only('verify', 'resend');
    // }

    // public function verify(Request $request, $id, $hash)
    // {
    //     $user = User::findOrFail($id);

    //     if (! Hash::check($hash, $user->getEmailForVerificationToken())) {
    //         return response()->json(['message' => 'Invalid verification token.'], 400);
    //     }

    //     $user->markEmailAsVerified();
    //     event(new Verified($user)); // Fire the verified event

    //     return response()->json(['message' => 'Your email has been verified successfully.']);
    // }

    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Check if the hash matches the SHA-1 of the user's email.
        // This assumes that your User model implements MustVerifyEmail, which provides getEmailForVerification().
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification token.'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect("/login");
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect("/login");
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->sendError('You have already verified your email.', ["user" => $user, "message" => 'You have already veirified your email.'], 501);
        }

        $user->sendEmailVerificationNotification();

        return $this->sendResponse(["user" => $user, 'message' => 'Verification email has been sent!'], 'Verification email has been sent!', 200);

    }
}
