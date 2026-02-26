<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\User;


class AuthController extends Controller
{
    // --------------------------------------------------
    // LOGIN
    // --------------------------------------------------
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_email' => 'required|email',
            'user_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $credentials = [
            'user_email' => $request->user_email,
            'password' => $request->user_password
        ];

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials provided.'
            ], 401);
        }

        // If authentication is successful, create a token for the user
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user // Optionally return user data
        ]);
    }public function checkTupId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_tupid' => 'required|string|size:6|unique:users,user_tupid',
        ], [
            'user_tupid.unique' => 'TUP ID already exists.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first('user_tupid'),
            ], 422);
        }
        return response()->json(['status' => 'success', 'message' => 'TUP ID is available.']);
    }
    // --------------------------------------------------
    // CHECK EMAIL — called live as user types
    // --------------------------------------------------
    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_email' => 'required|email|regex:/^.+@tup\.edu\.ph$/i|unique:users,user_email',
        ], [
            'user_email.unique' => 'This email already exists.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first('user_email'),
            ], 422);
        }
        return response()->json(['status' => 'success', 'message' => 'Email is available.']);
    }
    // --------------------------------------------------
    // REGISTER — validates data, stores temporarily, sends OTP
    // --------------------------------------------------
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_firstname'         => 'required|string|max:50',
            'user_middlename'        => 'nullable|string|max:50',
            'user_lastname'          => 'required|string|max:50',
            'user_suffix'            => 'nullable|string|max:10',
            'user_tupid'             => 'required|string|size:6|unique:users,user_tupid',
            'user_email'             => 'required|email|regex:/^.+@tup\.edu\.ph$/i|unique:users,user_email',
            'user_password'          => 'required|string|min:8',
            'user_type'              => 'required|in:Faculty,Staff',
            'selected_department_id' => 'required|integer|exists:departments_tbl,dep_id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }
        // Generate a 6-digit OTP
        $otp = strval(random_int(100000, 999999));
        // Store registration data + OTP in cache for 15 minutes
        // Key is the user's email so we can retrieve it during verify
        Cache::put('reg_' . $request->user_email, [
            'data' => $request->only([
                'user_firstname', 'user_middlename', 'user_lastname',
                'user_suffix', 'user_tupid', 'user_email', 'user_password',
                'user_type', 'selected_department_id',
            ]),
            'otp' => $otp,
        ], now()->addMinutes(15));
        // Send the OTP via email
        Mail::raw("Your verification code is: $otp", function ($message) use ($request) {
            $message->to($request->user_email)
                    ->subject('Your I-TRAC Verification Code');
        });
        return response()->json([
            'status'  => 'success',
            'message' => 'OTP sent to your email. Please verify within 15 minutes.',
        ]);
    }
    // --------------------------------------------------
    // RESEND OTP
    // --------------------------------------------------
    public function resendOtp(Request $request)
    {
        $cached = Cache::get('reg_' . $request->user_email);
        if (!$cached) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Session expired. Please register again.',
            ], 400);
        }
        // Generate a fresh OTP and reset the 15-minute timer
        $otp = strval(random_int(100000, 999999));
        $cached['otp'] = $otp;
        Cache::put('reg_' . $request->user_email, $cached, now()->addMinutes(15));
        Mail::raw("Your new verification code is: $otp", function ($message) use ($request) {
            $message->to($request->user_email)
                    ->subject('Your I-TRAC Verification Code');
        });
        return response()->json([
            'status'  => 'success',
            'message' => 'A new OTP has been sent to your email.',
        ]);
    }
    // --------------------------------------------------
    // VERIFY OTP — creates the user if OTP matches
    // --------------------------------------------------
    public function verify(Request $request)
    {
        $cached = Cache::get('reg_' . $request->user_email);
        if (!$cached) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Session expired. Please register again.',
            ], 400);
        }
        if ($request->otp !== $cached['otp']) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid verification code.',
            ], 422);
        }
        // OTP is correct — create the user
        try {
            $data = $cached['data'];
            User::create([
                'user_firstname'         => $data['user_firstname'],
                'user_middlename'        => $data['user_middlename'] ?? null,
                'user_lastname'          => $data['user_lastname'],
                'user_suffix'            => $data['user_suffix'] ?? null,
                'user_tupid'             => $data['user_tupid'],
                'user_email'             => $data['user_email'],
                'user_password'          => bcrypt($data['user_password']),
                'user_type'              => $data['user_type'],
                'selected_department_id' => $data['selected_department_id'],
                'email_verified_at'      => now(),
            ]);
            Cache::forget('reg_' . $request->user_email);
            return response()->json([
                'status'  => 'success',
                'message' => 'Registration successful! You can now log in.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}