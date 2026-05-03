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
        $users = User::whereIn('user_id', $userIds)->get();

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
            ->where(function($q) use ($query) {
                $q->where('user_firstname', 'LIKE', "%{$query}%")
                  ->orWhere('user_middlename', 'LIKE', "%{$query}%")
                  ->orWhere('user_lastname', 'LIKE', "%{$query}%");
            })
            ->limit(20)
            ->get();

        // Add basic placeholder info needed for the frontend render
        $users->transform(function ($user) {
            $user->latest_message = 'Click to start chatting';
            $user->latest_message_time = '';
            $user->latest_message_date = null;
            $user->unread_count = 0;
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

        return response()->json([
            'messages' => $messages,
            'target_user' => User::find($userId)
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
}
