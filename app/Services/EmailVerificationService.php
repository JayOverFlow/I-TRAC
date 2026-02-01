<?php
namespace App\Services;

use App\Models\EmailVerification;

class EmailVerificationService
{
    // Generate a 6 digit verification code
    public function generateCode() {
        return rand(100000, 999999);
    }

    public function createVerificationCode($email) {
        // Delete exiting code for this email
        $this->deleteActiveCodes($email);

        // Also clean up any expired codes for this email
        EmailVerification::getExpiredCodes()
            ->where('email', $email)
            ->delete();

        // Generate a new 6 digit code
        $code = $this->generateCode();

        // Store the code to the database corresponds to the email
        EmailVerification::create([
            'email' => $email,
            'verification_code' => $code,
            'expires_at' => now()->addMinutes(15)
        ]);

        return $code;
    }

    // Valdiate the code
    public function validateCode($email, $userCode) {
        // Clean up expired codes for this email
        EmailVerification::getExpiredCodes()
            ->where('email', $email)
            ->delete();

        // Get the active code that corresponds to email
        $verification = EmailVerification::getActiveCodes()
            ->where('email', $email)
            ->where('verification_code', $userCode)
            ->first();

        if ($verification && $verification->isCodeValid()) {
            // Delete the used code
            $verification->delete();
            return true; // true means the code valid
        }

        return false; // false mean the code is not present or invalid
    }

    // Delete all the active codes that corresponds to email
    public function deleteActiveCodes($email) {
        EmailVerification::getActiveCodes()
            ->where('email', $email)
            ->delete();
    }

    // Clean up expired codes for maintance
    public function cleanupExpiredCodes() {
        return EmailVerification::getExpiredCodes()->delete();
    }

    // Check if user can request new code for rate limiting
    public function canRequestNewCode($email) {
        // Get the recent code sent to the email
        $recentCode = EmailVerification::getActiveCodes()
            ->where('email' , $email)
            ->where('created_at', '>', now()->subMinutes(1))
            ->first();

        return !$recentCode; 
    }
}