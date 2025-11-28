<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Show the wall/feed with all posts.
     */
    public function index()
    {
        return view('wall.index');
    }

    /**
     * Get all posts for the feed.
     */
    public function getPosts()
    {
        $currentUser = Auth::user();

        // If logged in, show friends + self posts
        // If guest, show all public posts
        if ($currentUser) {
            $friendIds = $currentUser->friends()->pluck('id')->toArray();
            $friendIds[] = $currentUser->id;
            $posts = Post::whereIn('user_id', $friendIds);
        } else {
            // Guests see all posts
            $posts = Post::query();
        }

        $posts = $posts->with(['user:id,name,email,profile_image', 'likes', 'comments', 'shares'])
            ->latest()
            ->limit(50) // Limit for performance
            ->get()
            ->map(function ($post) use ($currentUser) {
                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'image' => $post->image ? asset('storage/'.$post->image) : null,
                    'created_at' => $post->created_at->diffForHumans(),
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $post->user->name,
                        'email' => $post->user->email,
                        'profile_image' => $post->user->profile_image ? asset('storage/'.$post->user->profile_image) : null,
                    ],
                    'likes_count' => $post->likes()->count(),
                    'is_liked' => $post->isLikedBy($currentUser),
                    'comments_count' => $post->comments()->count(),
                    'shares_count' => $post->shares()->count(),
                    'is_shared' => $post->isSharedBy($currentUser),
                ];
            });

        return response()->json($posts);
    }

    /**
     * Get posts for a specific user.
     */
    public function getUserPosts($userId)
    {
        $user = User::findOrFail($userId);
        $currentUser = Auth::user();

        $posts = Post::where('user_id', $userId)
            ->with(['user:id,name,email,profile_image', 'likes', 'comments', 'shares'])
            ->latest()
            ->get()
            ->map(function ($post) use ($currentUser) {
                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'image' => $post->image ? asset('storage/'.$post->image) : null,
                    'created_at' => $post->created_at->diffForHumans(),
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $post->user->name,
                        'email' => $post->user->email,
                        'profile_image' => $post->user->profile_image ? asset('storage/'.$post->user->profile_image) : null,
                    ],
                    'likes_count' => $post->likes()->count(),
                    'is_liked' => $post->isLikedBy($currentUser),
                    'comments_count' => $post->comments()->count(),
                    'shares_count' => $post->shares()->count(),
                    'is_shared' => $post->isSharedBy($currentUser),
                ];
            });

        return response()->json($posts);
    }

    /**
     * Store a new post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required_without:image|nullable|string|max:5000',
            'image' => 'required_without:content|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'content.required_without' => 'Please provide content or an image.',
            'image.required_without' => 'Please provide content or an image.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            try {
                $imagePath = $request->file('image')->store('posts', 'public');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload image: '.$e->getMessage(),
                ], 500);
            }
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $validated['content'] ?? '',
            'image' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'post' => [
                'id' => $post->id,
                'content' => $post->content,
                'image' => $post->image ? asset('storage/'.$post->image) : null,
                'created_at' => $post->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Delete a post.
     */
    public function destroy($id)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Delete image if exists
        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }

    /**
     * Toggle like on a post.
     */
    public function toggleLike($id)
    {
        $post = Post::findOrFail($id);
        $userId = Auth::id();

        $like = PostLike::where('post_id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            PostLike::create([
                'post_id' => $id,
                'user_id' => $userId,
            ]);
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $post->likes()->count(),
        ]);
    }
}
