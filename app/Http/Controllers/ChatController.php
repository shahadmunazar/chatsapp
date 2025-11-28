<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /**
     * Show the chat interface.
     */
    public function index()
    {
        return view('chat.index');
    }

    /**
     * Get list of friends with last message info.
     */
    public function users()
    {
        $currentUserId = Auth::id();
        $currentUser = Auth::user();

        // Get only friends
        $friends = $currentUser->friends();

        $users = $friends->map(function ($user) use ($currentUserId) {
            // Ensure we have all necessary fields
            if (! isset($user->id)) {
                return null;
            }

            $user = User::find($user->id);
            if (! $user) {
                return null;
            }

            $lastMessage = Message::where(function ($query) use ($currentUserId, $user) {
                $query->where('sender_id', $currentUserId)
                    ->where('receiver_id', $user->id);
            })->orWhere(function ($query) use ($currentUserId, $user) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', $currentUserId);
            })->latest()->first();

            $unreadCount = Message::where('sender_id', $user->id)
                ->where('receiver_id', $currentUserId)
                ->where('is_read', false)
                ->count();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_image' => $user->profile_image ? asset('storage/'.$user->profile_image) : null,
                'last_message' => $lastMessage ? $lastMessage->message : null,
                'last_message_time' => $lastMessage ? $lastMessage->created_at : null,
                'unread_count' => $unreadCount,
                'is_online' => $user->isOnline(),
                'last_seen' => $user->last_seen,
            ];
        })->filter();

        return response()->json($users);
    }

    /**
     * Get chat history between current user and another user.
     */
    public function history($userId)
    {
        $currentUserId = Auth::id();

        $messages = Message::where(function ($query) use ($currentUserId, $userId) {
            $query->where('sender_id', $currentUserId)
                ->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($currentUserId, $userId) {
            $query->where('sender_id', $userId)
                ->where('receiver_id', $currentUserId);
        })->with(['sender:id,name', 'receiver:id,name'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                $data = $message->toArray();
                if ($message->file_path) {
                    $data['file_url'] = Storage::url($message->file_path);
                }

                return $data;
            });

        // Mark messages as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    /**
     * Send a message to another user.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required_without:file|nullable|string|max:1000',
            'file' => 'required_without:message|nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,mp4,mov,avi,mkv,zip,rar',
        ]);

        $receiver = User::findOrFail($validated['receiver_id']);
        $sender = Auth::user();

        $messageData = [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => $validated['message'] ?? '',
        ];

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $size = $file->getSize();

            // Determine file type category
            $fileType = $this->determineFileType($extension);

            // Store file
            $path = $file->store('chat_files', 'public');

            $messageData['file_path'] = $path;
            $messageData['file_name'] = $originalName;
            $messageData['file_type'] = $fileType;
            $messageData['file_size'] = $size;

            // If no message text, set a default based on file type
            if (empty($messageData['message'])) {
                $messageData['message'] = '📎 Sent a '.$fileType;
            }
        }

        // Store message in database
        $message = Message::create($messageData);

        // Load relationships for broadcast
        $message->load(['sender:id,name', 'receiver:id,name']);

        // Broadcast message
        event(new MessageSent($sender, $receiver, $messageData['message']));

        // Return message with file URL if exists
        $response = $message->toArray();
        if ($message->file_path) {
            $response['file_url'] = Storage::url($message->file_path);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $response,
        ]);
    }

    /**
     * Determine file type category based on extension.
     */
    private function determineFileType(string $extension): string
    {
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        $videoTypes = ['mp4', 'mov', 'avi', 'mkv', 'webm', 'flv'];
        $documentTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
        $archiveTypes = ['zip', 'rar', '7z', 'tar', 'gz'];
        $textTypes = ['txt', 'csv', 'log'];

        $extension = strtolower($extension);

        if (in_array($extension, $imageTypes)) {
            return 'image';
        } elseif (in_array($extension, $videoTypes)) {
            return 'video';
        } elseif (in_array($extension, $documentTypes)) {
            return 'document';
        } elseif (in_array($extension, $archiveTypes)) {
            return 'archive';
        } elseif (in_array($extension, $textTypes)) {
            return 'text';
        }

        return 'file';
    }

    /**
     * Update user activity (heartbeat).
     */
    public function updateActivity()
    {
        Auth::user()->update([
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
