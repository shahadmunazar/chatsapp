<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendRequestController extends Controller
{
    /**
     * Show the home page with all users.
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Get all users with their friend request status.
     */
    public function allUsers()
    {
        $currentUserId = Auth::id();

        $users = User::where('id', '!=', $currentUserId)
            ->select('id', 'name', 'email', 'profile_image', 'last_seen_at', 'bio', 'city', 'school', 'college', 'work')
            ->get()
            ->map(function ($user) use ($currentUserId) {
                // Check friend request status
                $sentRequest = FriendRequest::where('sender_id', $currentUserId)
                    ->where('receiver_id', $user->id)
                    ->first();

                $receivedRequest = FriendRequest::where('sender_id', $user->id)
                    ->where('receiver_id', $currentUserId)
                    ->first();

                $friendshipStatus = 'none';
                $requestId = null;

                if ($sentRequest) {
                    $friendshipStatus = $sentRequest->status === 'accepted' ? 'friends' : 'sent';
                    $requestId = $sentRequest->id;
                } elseif ($receivedRequest) {
                    $friendshipStatus = $receivedRequest->status === 'accepted' ? 'friends' : 'received';
                    $requestId = $receivedRequest->id;
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_image' => $user->profile_image ? asset('storage/'.$user->profile_image) : null,
                    'is_online' => $user->isOnline(),
                    'last_seen' => $user->last_seen,
                    'bio' => $user->bio,
                    'city' => $user->city,
                    'school' => $user->school,
                    'college' => $user->college,
                    'work' => $user->work,
                    'friendship_status' => $friendshipStatus,
                    'request_id' => $requestId,
                ];
            });

        return response()->json($users);
    }

    /**
     * Get pending friend requests.
     */
    public function pending()
    {
        $requests = FriendRequest::where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->with('sender:id,name,email,profile_image,last_seen_at')
            ->latest()
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'sender' => [
                        'id' => $request->sender->id,
                        'name' => $request->sender->name,
                        'email' => $request->sender->email,
                        'profile_image' => $request->sender->profile_image ? asset('storage/'.$request->sender->profile_image) : null,
                        'is_online' => $request->sender->isOnline(),
                    ],
                    'created_at' => $request->created_at->diffForHumans(),
                ];
            });

        return response()->json($requests);
    }

    /**
     * Send a friend request.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        // Check if sending to themselves
        if ($validated['receiver_id'] == Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot send a friend request to yourself',
            ], 422);
        }

        // Check if request already exists
        $existing = FriendRequest::where(function ($query) use ($validated) {
            $query->where('sender_id', Auth::id())
                ->where('receiver_id', $validated['receiver_id']);
        })->orWhere(function ($query) use ($validated) {
            $query->where('sender_id', $validated['receiver_id'])
                ->where('receiver_id', Auth::id());
        })->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Friend request already exists',
            ], 400);
        }

        $friendRequest = FriendRequest::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Friend request sent successfully',
            'data' => $friendRequest,
        ]);
    }

    /**
     * Accept a friend request.
     */
    public function accept($id)
    {
        $friendRequest = FriendRequest::where('id', $id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $friendRequest->update(['status' => 'accepted']);

        return response()->json([
            'success' => true,
            'message' => 'Friend request accepted',
        ]);
    }

    /**
     * Reject a friend request.
     */
    public function reject($id)
    {
        $friendRequest = FriendRequest::where('id', $id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $friendRequest->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Friend request rejected',
        ]);
    }

    /**
     * Cancel a sent friend request.
     */
    public function cancel($id)
    {
        $friendRequest = FriendRequest::where('id', $id)
            ->where('sender_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $friendRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Friend request cancelled',
        ]);
    }
}
