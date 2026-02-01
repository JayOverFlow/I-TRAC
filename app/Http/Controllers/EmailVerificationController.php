<?php

namespace App\Http\Controllers;

use App\Mail\VerificationCodeMail;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    protected $emailVerificationService;
    public function __construct(EmailVerificationService $emailVerificationService) {
        $this->emailVerificationService = $emailVerificationService;
    }

    // Send the 6 digit verification code to the email
    public function sendVerificationCode(Request $request) {
        try {
            // Get the user email
            $email = $request->email;

            // If passed the guard block
            // Create code
            $code = $this->emailVerificationService->createVerificationCode($email);

            // Send code
            Mail::to($email)->send(new VerificationCodeMail($code));

            // Feedback
            return response()->json([
                'success' => true,
                'message' => '6-digit verification code sent successfully',
                'code' => $code // Remove in production
            ]);
        } catch (\Exception $e) {
            Log::error('Email verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    // Verify the 6 digit code from the user if valid
    public function verifyCode(Request $request) {
        // Valdite the code
        $isValid = $this->emailVerificationService->validateCode(
            $request->email,
            $request->code
        );
        
        // If code is valid
        if ($isValid) {
            return response()->json([
                'success' => true,
                'message' => '6-digit code verified successfully',
                ]
            );
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired verification code',
            ],
            400 // This code means Bad Request
        );
    }
    
}
