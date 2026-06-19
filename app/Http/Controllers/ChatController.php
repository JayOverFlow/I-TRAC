<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Get the list of users for the chat sidebar.
     * Orders by latest message interaction.
     */
    public function getUsers()
    {
        $authUserId = Auth::id();

        // Get users who have exchanged messages with auth user
        $userIds = Message::where('sender_id', $authUserId)
            ->orWhere('receiver_id', $authUserId)
            ->get()
            ->flatMap(function ($msg) use ($authUserId) {
                return [$msg->sender_id, $msg->receiver_id];
            })
            ->reject(function ($id) use ($authUserId) {
                return $id == $authUserId;
            })
            ->unique()
            ->toArray();

        // Get those specific users
        $users = User::whereIn('user_id', $userIds)->with('departments')->get();

        $usersWithData = $users->map(function ($user) use ($authUserId) {
            $latestMessage = Message::where(function ($query) use ($authUserId, $user) {
                $query->where('sender_id', $authUserId)->where('receiver_id', $user->user_id);
            })->orWhere(function ($query) use ($authUserId, $user) {
                $query->where('sender_id', $user->user_id)->where('receiver_id', $authUserId);
            })->latest('created_at')->first();

            $unreadCount = Message::where('sender_id', $user->user_id)
                ->where('receiver_id', $authUserId)
                ->whereNull('read_at')
                ->count();

            $user->latest_message = $latestMessage ? $latestMessage->message : null;
            $user->latest_message_time = $latestMessage ? $latestMessage->created_at->format('g:i A') : null;
            $user->latest_message_date = $latestMessage ? $latestMessage->created_at : null;
            $user->unread_count = $unreadCount;
            $user->is_online = \Illuminate\Support\Facades\Cache::has('last_seen_user_' . $user->user_id);
            $user->department_name = $user->departments->isNotEmpty()
                ? $user->departments->pluck('dep_name')->implode(', ')
                : 'User';

            return $user;
        });

        // Sort by latest message date descending
        $sortedUsers = $usersWithData->sortByDesc('latest_message_date')->values();

        return response()->json([
            'users' => $sortedUsers
        ]);
    }

    /**
     * Search all users globally.
     */
    public function searchUsers(Request $request)
    {
        $query = $request->input('q');
        $authUserId = Auth::id();

        if (empty($query)) {
            return response()->json(['users' => []]);
        }

        $users = User::where('user_id', '!=', $authUserId)
            ->where(function ($q) use ($query) {
                $q->where('user_firstname', 'LIKE', "%{$query}%")
                    ->orWhere('user_middlename', 'LIKE', "%{$query}%")
                    ->orWhere('user_lastname', 'LIKE', "%{$query}%");
            })
            ->with('departments')
            ->limit(20)
            ->get();

                // Add actual message info and online status for searched users
        $users->transform(function ($user) use ($authUserId) {
            $latestMessage = Message::where(function ($query) use ($authUserId, $user) {
                $query->where('sender_id', $authUserId)->where('receiver_id', $user->user_id);
            })->orWhere(function ($query) use ($authUserId, $user) {
                $query->where('sender_id', $user->user_id)->where('receiver_id', $authUserId);
            })->latest('created_at')->first();

            $unreadCount = Message::where('sender_id', $user->user_id)
                ->where('receiver_id', $authUserId)
                ->whereNull('read_at')
                ->count();

            $user->latest_message = $latestMessage ? $latestMessage->message : 'Click to start chatting';
            $user->latest_message_time = $latestMessage ? $latestMessage->created_at->format('g:i A') : '';
            $user->latest_message_date = $latestMessage ? $latestMessage->created_at : null;
            $user->unread_count = $unreadCount;
            $user->is_online = \Illuminate\Support\Facades\Cache::has('last_seen_user_' . $user->user_id);
            $user->department_name = $user->departments->isNotEmpty()
                ? $user->departments->pluck('dep_name')->implode(', ')
                : 'User';

            return $user;
        });

        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * Get messages for a specific user and mark them as read.
     */
    public function getMessages($userId)
    {
        $authUserId = Auth::id();

        // Mark incoming messages from this user as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $authUserId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Fetch messages
        $messages = Message::where(function ($query) use ($authUserId, $userId) {
            $query->where('sender_id', $authUserId)->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($authUserId, $userId) {
            $query->where('sender_id', $userId)->where('receiver_id', $authUserId);
        })
            ->orderBy('created_at', 'asc')
            ->get();

        $targetUser = User::with('departments')->find($userId);
        if ($targetUser) {
            $targetUser->is_online = \Illuminate\Support\Facades\Cache::has('last_seen_user_' . $targetUser->user_id);
            $targetUser->department_name = $targetUser->departments->isNotEmpty()
                ? $targetUser->departments->pluck('dep_name')->implode(', ')
                : 'User';
        }

        }

        return response()->json([
            'messages' => $messages,
            'target_user' => $targetUser
        ]);
    }

    /**
     * Send a new message.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,user_id',
            'message' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Get unread counts + top 3 newest unread messages and unread notifications.
     * Notifications = tasks assigned to the user that have not been read yet.
     */
    public function getUnreadCount()
    {
        $userId = Auth::id();

        // Unread messages = messages received where read_at is null
        $unreadMessagesCount = Message::where('receiver_id', $userId)
            ->whereNull('read_at')
            ->count();

        // Unread notifications = tasks assigned to user that have NOT been read yet
        $unreadNotificationsCount = \App\Models\Task::where('assigned_to', $userId)
            ->whereNull('read_at')
            ->count();

        // Top 3 newest messages (whether read or unread)
        $recentMessages = Message::where('receiver_id', $userId)
            ->with('sender')
            ->latest('message_id')
            ->take(3)
            ->get()
            ->map(function ($msg) {
                return [
                    'id'           => $msg->message_id,
                    'sender_name'  => $msg->sender->user_fullname_no_middle ?? 'User',
                    'sender_avatar'=> $msg->sender->user_profile_photo
                                        ? asset($msg->sender->user_profile_photo)
                                        : asset('img/profiles/blank.avif'),
                    'message'      => $msg->message,
                    'time'         => $msg->created_at ? $msg->created_at->diffForHumans() : '',
                    'is_read'      => !is_null($msg->read_at),
                ];
            });

        // Top 3 newest notifications (whether read or unread)
        // Covers: PR Assignment, PR Submitted, PO Submitted notification types
        $recentNotifications = \App\Models\Task::where('assigned_to', $userId)
            ->with('assignedBy')
            ->latest('task_id')
            ->take(3)
            ->get()
            ->map(function ($task) {
                $type = $task->task_type;
                // Derive a clean label per type
                $typeLabel = match($type) {
                    'PR Submitted'  => 'PR Submitted',
                    'PO Submitted'  => 'PO Submitted',
                    'PR Assignment' => 'PR Assigned',
                    'Purchase Request' => 'PR Assigned',
                    default         => 'Notification',
                };
                return [
                    'task_id'          => $task->task_id,
                    'task_description' => $task->task_description,
                    'task_type'        => $type,
                    'type_label'       => $typeLabel,
                    'time'             => $task->created_at
                                            ? \Carbon\Carbon::parse($task->created_at)->diffForHumans()
                                            : '',
                    'assigned_by_name' => $task->assignedBy->user_fullname_no_middle ?? 'System',
                    'url'              => route('show.tasks'),
                    'is_read'          => !is_null($task->read_at),
                ];
            });

        return response()->json([
            'unread_messages'      => $unreadMessagesCount,
            'unread_notifications' => $unreadNotificationsCount,
            'total_unread'         => $unreadMessagesCount + $unreadNotificationsCount,
            'messages_list'        => $recentMessages,
            'notifications_list'   => $recentNotifications,
        ]);
    }

    /**
     * Mark all unread notifications (tasks) for the current user as read.
     */
    public function markNotificationsRead()
    {
        \App\Models\Task::where('assigned_to', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark a single notification (task) as read by task_id.
     */
    public function markSingleNotificationRead(Request $request)
    {
        $taskId = $request->input('task_id');

        \App\Models\Task::where('task_id', $taskId)
            ->where('assigned_to', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

}

