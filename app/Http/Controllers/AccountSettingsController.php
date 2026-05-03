<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AccountSettingsController extends Controller
{
    public function showAccountSettings()
    {
        $user = Auth::user();

        $userRole = $user->roles()->first();

        if (!$userRole) {
            abort(403, 'Unassigned Role');
        }

        // Use switch statement to redirect user
        if ($userRole->gen_role === 'Head') {
            $apps = $user->appParents;
            return view('head.pages.head-account-settings', compact('user', 'apps'));
        }

        abort(404, 'Account settings not available for this role yet.');
    }

    /**
     * Update the authenticated user's personal information.
     * Read-only fields (TUP-ID, email, user_type, department) are intentionally excluded.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'user_firstname'  => ['required', 'string', 'max:100'],
            'user_middlename' => ['required', 'string', 'max:100'],
            'user_lastname'   => ['required', 'string', 'max:100'],
            'user_suffix'     => ['nullable', 'string', 'max:20'],
            'user_contactno'  => ['nullable', 'string', 'max:20'],
        ], [
            'user_middlename.required' => 'Middle name could not be left blank.',
            'user_lastname.required' => 'Last name could not be left blank.',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
        ]);
    }

    /**
     * Update the authenticated user's password.
     * Verifies current password before hashing and saving the new one.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password'  => ['required', 'string'],
            'new_password'      => [
                'required', 
                'string', 
                'min:8', 
                'max:128', 
                'regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$/'
            ],
            'confirm_password'  => ['required', 'string', 'same:new_password'],
        ], [
            'new_password.regex' => 'Password must contain at least one letter and one number.',
        ]);

        // Verify that the provided current password matches the stored hash
        if (!Hash::check($request->current_password, $user->user_password)) {
            return response()->json([
                'success' => false,
                'errors'  => ['current_password' => ['The current password is incorrect.']],
            ], 422);
        }

        $user->update(['user_password' => $request->new_password]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    /**
     * Upload and save a new profile photo for the authenticated user.
     * Old photo is deleted if it exists. Stored in public/img/profiles/.
     */
    public function updateAvatar(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        // Delete the old photo file if it exists
        if ($user->user_profile_photo) {
            $oldPath = public_path($user->user_profile_photo);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Store the new photo and save the relative path
        $file     = $request->file('profile_photo');
        $filename = 'user_' . $user->user_id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('img/profiles'), $filename);

        $user->update(['user_profile_photo' => 'img/profiles/' . $filename]);

        return response()->json([
            'success'   => true,
            'message'   => 'Profile photo updated successfully.',
            'photo_url' => asset('img/profiles/' . $filename),
        ]);
    }

    /**
     * Delete the authenticated user's profile photo.
     * Removes the file and clears the column.
     */
    public function deleteAvatar(Request $request)
    {
        $user = Auth::user();

        if ($user->user_profile_photo) {
            $oldPath = public_path($user->user_profile_photo);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            $user->update(['user_profile_photo' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile photo removed.',
        ]);
    }
}
