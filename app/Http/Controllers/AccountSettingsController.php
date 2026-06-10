<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Models\PrParent;
use App\Models\AppParent;

class AccountSettingsController extends Controller
{
    public function showAccountSettings()
    {
        $user = Auth::user();

        // Resolve active role dynamically based on active session context
        $activeRoleId = session('active_role_id');
        $userRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $genRole = $userRole ? $userRole->gen_role : 'Unassigned';

        // Resolve the active department ID from the active role context to scope records.
        $depId = $userRole ? $userRole->role_dep_id_fk : null;

        $activeAppId = session('active_app_id_' . $depId);
        
        // Fetch active APP of this department from database if none set in session
        if (!$activeAppId && $depId) {
            $dbActiveApp = AppParent::where('app_dep_id_fk', $depId)
                ->where('is_active', true)
                ->first();
            if ($dbActiveApp) {
                $activeAppId = $dbActiveApp->app_id;
                session(['active_app_id_' . $depId => $activeAppId]);
            }
        }

        $activeApp = $activeAppId ? AppParent::find($activeAppId) : null;

        // Use switch statement or conditional structure to redirect user
        if ($genRole === 'Head') {
            // Retrieve only the Annual Procurement Plans (APPs) that belong strictly to the active department context.
            // This prevents APPs created in another department from leaking/displaying under the current department view.
            $appsQuery = AppParent::query();
            if ($depId) {
                $appsQuery->where('app_dep_id_fk', $depId);
            } else {
                $appsQuery->whereIn('app_dep_id_fk', $user->departments()->pluck('dep_id'));
            }
            $apps = $appsQuery->get();

            return view('head.pages.head-account-settings', compact('user', 'apps', 'activeAppId', 'activeApp'));
        } elseif ($genRole === 'Procurement') {
            // Procurement/Supply users can filter their plans by the active department if one exists.
            $appsQuery = AppParent::query();
            if ($depId) {
                $appsQuery->where('app_dep_id_fk', $depId);
            } else {
                $appsQuery->whereIn('app_dep_id_fk', $user->departments()->pluck('dep_id'));
            }
            $apps = $appsQuery->get();

            // Fetch PRs retrieved by this user
            $loadedPrs = PrParent::where('retrieved_by', $user->user_id)->get();
            
            return view('procurement.pages.procurement-account-settings', compact('user', 'apps', 'loadedPrs', 'activeAppId', 'activeApp'));
        } elseif ($genRole === 'Supply') {
            $appsQuery = AppParent::query();
            if ($depId) {
                $appsQuery->where('app_dep_id_fk', $depId);
            } else {
                $appsQuery->whereIn('app_dep_id_fk', $user->departments()->pluck('dep_id'));
            }
            $apps = $appsQuery->get();
            return view('supply.pages.supply-account-settings', compact('user', 'apps', 'activeAppId', 'activeApp'));
        } elseif ($genRole === 'Unassigned') {
            return view('unassigned.pages.unassigned-account-settings', compact('user'));
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

        // 1. Verify that the current password matches the stored hash first
        if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->user_password)) {
            return response()->json([
                'success' => false,
                'errors'  => ['current_password' => ['The current password is incorrect.']],
            ], 422);
        }

        // 2. Then validate strength constraints for the new password
        $request->validate([
            'new_password'      => [
                'required', 
                'string', 
                'min:8', 
                'max:128', 
                'regex:/^(?=.*[A-Za-z])(?=.*\d).{8,128}$/'
            ],
            'confirm_password'  => ['required', 'string', 'same:new_password'],
        ], [
            'new_password.regex' => 'Password must contain at least one letter and one number.',
        ]);

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

    /**
     * Fetch the related projects (appItems) and purchase orders for a given APP.
     */
    public function getArchiveAppData($app_id)
    {
        $app = AppParent::with(['appItems'])->findOrFail($app_id);
        
        // Eager load PRs and their POs, and relations to get requestor details
        $appWithPrs = AppParent::with(['purchaseRequests.purchaseOrders', 'purchaseRequests.requestor', 'purchaseRequests.savedBy'])->findOrFail($app_id);
        
        $purchaseRequests = $appWithPrs->purchaseRequests;
        
        $purchaseOrders = collect();
        foreach ($purchaseRequests as $pr) {
            foreach ($pr->purchaseOrders as $po) {
                $purchaseOrders->push($po);
            }
        }

        // Map PRs to include formatted requested_by and required columns
        $formattedPrs = $purchaseRequests->map(function ($pr) {
            $requestorName = '-';
            if ($pr->requestor) {
                $requestorName = $pr->requestor->user_fullname;
            } elseif ($pr->savedBy) {
                $requestorName = $pr->savedBy->user_fullname;
            }
            
            return [
                'pr_id' => $pr->pr_id,
                'pr_no' => $pr->pr_no,
                'pr_unique_code' => $pr->pr_unique_code,
                'pr_purpose' => $pr->pr_purpose,
                'pr_total' => $pr->pr_total,
                'requested_by' => $requestorName,
            ];
        });

        return response()->json([
            'app_unique_code' => $app->app_unique_code,
            'appItems' => $app->appItems,
            'purchaseRequests' => $formattedPrs,
            'purchaseOrders' => $purchaseOrders
        ]);
    }

    /**
     * Set the active APP for the user's department context in session.
     */
    public function setActiveApp(Request $request)
    {
        $request->validate([
            'app_id' => 'required|integer|exists:app_tbl,app_id',
        ]);

        $user = Auth::user();
        $activeRoleId = session('active_role_id');
        $userRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $depId = $userRole ? $userRole->role_dep_id_fk : null;

        // Verify that this APP belongs to the user's department
        $app = AppParent::findOrFail($request->input('app_id'));
        if ($depId && $app->app_dep_id_fk !== $depId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized APP selection.',
            ], 403);
        }

        // Persist the active selection in the database under a transaction
        \Illuminate\Support\Facades\DB::transaction(function () use ($app, $depId) {
            AppParent::where('app_dep_id_fk', $depId)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $app->update(['is_active' => true]);
        });

        // Store active APP in the session (scoped to department/role context)
        session(['active_app_id_' . $depId => $app->app_id]);

        return response()->json([
            'success' => true,
            'message' => 'Active Annual Procurement Plan set successfully.',
            'app_title' => $app->app_title,
        ]);
    }
}
