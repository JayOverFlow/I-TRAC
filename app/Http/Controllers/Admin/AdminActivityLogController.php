<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AdminActivityLogController extends Controller
{
    /**
     * Display the Admin Activity Logs / Audit Trail page.
     * This page shows logs for all 5 admins.
     */
    public function index()
    {
        // Fetch all logs with the associated admin username
        $logs = ActivityLog::with('admin')
            ->orderBy('log_created_at', 'desc')
            ->get();

        return view('admin.pages.activity-logs', compact('logs'));
    }

    /**
     * Get the latest 5 logs for the current admin for dynamic header updates.
     */
    public function getLatestLogs()
    {
        $adminId = session('admin_id');
        $logs = ActivityLog::where('log_admin_id', $adminId)
            ->orderBy('log_created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'short_description' => $log->log_short_description,
                    'created_at_human' => \Carbon\Carbon::parse($log->log_created_at)->diffForHumans(),
                ];
            });

        return response()->json($logs);
    }
}
