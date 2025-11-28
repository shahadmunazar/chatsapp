<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show user's own profile.
     */
    public function show()
    {
        $user = Auth::user();
        $totalUsers = User::count();
        $onlineUsers = User::where('last_seen_at', '>=', now()->subMinutes(5))->count();

        return view('profile.show', compact('user', 'totalUsers', 'onlineUsers'));
    }

    /**
     * Show another user's profile.
     */
    public function view($id)
    {
        $user = User::findOrFail($id);
        $totalUsers = User::count();
        $onlineUsers = User::where('last_seen_at', '>=', now()->subMinutes(5))->count();

        return view('profile.view', compact('user', 'totalUsers', 'onlineUsers'));
    }

    /**
     * Update user profile.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.Auth::id(),
            'bio' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|string|in:male,female,other,prefer_not_to_say',
            'school' => 'nullable|string|max:255',
            'college' => 'nullable|string|max:255',
            'work' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
        ]);

        Auth::user()->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Upload profile image.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Delete old image if exists
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Store new image
        $path = $request->file('profile_image')->store('profiles', 'public');

        $user->update(['profile_image' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Profile image uploaded successfully',
            'image_url' => Storage::url($path),
        ]);
    }

    /**
     * Remove profile image.
     */
    public function removeImage()
    {
        $user = Auth::user();

        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->update(['profile_image' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Profile image removed successfully',
        ]);
    }

    /**
     * Get user statistics.
     */
    public function statistics()
    {
        $totalUsers = User::count();
        $onlineUsers = User::where('last_seen_at', '>=', now()->subMinutes(5))->count();
        $offlineUsers = $totalUsers - $onlineUsers;

        return response()->json([
            'total_users' => $totalUsers,
            'online_users' => $onlineUsers,
            'offline_users' => $offlineUsers,
        ]);
    }

    /**
     * Get all users (public).
     */
    public function allUsers()
    {
        $users = User::select('id', 'name', 'email', 'profile_image', 'last_seen_at')
            ->latest()
            ->limit(100)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_image' => $user->profile_image ? asset('storage/'.$user->profile_image) : null,
                    'is_online' => $user->isOnline(),
                    'last_seen' => $user->last_seen,
                ];
            });

        return response()->json($users);
    }

    /**
     * Search users by name or email (public).
     */
    public function searchUsers(Request $request)
    {
        $search = $request->input('q', '');

        if (empty($search)) {
            return response()->json([]);
        }

        $users = User::where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->select('id', 'name', 'email', 'profile_image', 'last_seen_at')
            ->limit(20)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_image' => $user->profile_image ? asset('storage/'.$user->profile_image) : null,
                    'is_online' => $user->isOnline(),
                    'registered' => true,
                ];
            });

        return response()->json([
            'results' => $users,
            'count' => $users->count(),
        ]);
    }
}
