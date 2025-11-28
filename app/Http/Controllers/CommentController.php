<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Post;
use App\Models\PostShare;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Get comments for a post.
     */
    public function index($postId)
    {
        $post = Post::findOrFail($postId);
        $currentUser = Auth::user();

        $comments = Comment::where('post_id', $postId)
            ->whereNull('parent_id') // Only top-level comments
            ->with(['user:id,name,email,profile_image', 'replies.user:id,name,email,profile_image', 'reactions'])
            ->latest()
            ->get()
            ->map(function ($comment) use ($currentUser) {
                return $this->formatComment($comment, $currentUser);
            });

        return response()->json($comments);
    }

    /**
     * Store a new comment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'post_id' => $validated['post_id'],
            'user_id' => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        $comment->load('user:id,name,email,profile_image', 'replies');

        return response()->json([
            'success' => true,
            'message' => 'Comment posted successfully',
            'comment' => $this->formatComment($comment, Auth::user()),
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroy($id)
    {
        $comment = Comment::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Toggle reaction on a comment.
     */
    public function toggleReaction(Request $request, $id)
    {
        $validated = $request->validate([
            'reaction_type' => 'required|string|in:like,love,laugh,wow,sad,angry',
        ]);

        $comment = Comment::findOrFail($id);
        $userId = Auth::id();

        $reaction = CommentReaction::where('comment_id', $id)
            ->where('user_id', $userId)
            ->where('reaction_type', $validated['reaction_type'])
            ->first();

        if ($reaction) {
            $reaction->delete();
            $reacted = false;
        } else {
            // Remove other reactions from this user on this comment
            CommentReaction::where('comment_id', $id)
                ->where('user_id', $userId)
                ->delete();

            CommentReaction::create([
                'comment_id' => $id,
                'user_id' => $userId,
                'reaction_type' => $validated['reaction_type'],
            ]);
            $reacted = true;
        }

        // Get reaction counts
        $reactionCounts = CommentReaction::where('comment_id', $id)
            ->selectRaw('reaction_type, COUNT(*) as count')
            ->groupBy('reaction_type')
            ->pluck('count', 'reaction_type')
            ->toArray();

        return response()->json([
            'success' => true,
            'reacted' => $reacted,
            'reaction_type' => $validated['reaction_type'],
            'reaction_counts' => $reactionCounts,
        ]);
    }

    /**
     * Share a post.
     */
    public function sharePost(Request $request, $postId)
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:500',
        ]);

        $post = Post::findOrFail($postId);
        $userId = Auth::id();

        $existingShare = PostShare::where('post_id', $postId)
            ->where('user_id', $userId)
            ->first();

        if ($existingShare) {
            $existingShare->delete();
            $shared = false;
        } else {
            PostShare::create([
                'post_id' => $postId,
                'user_id' => $userId,
                'message' => $validated['message'] ?? null,
            ]);
            $shared = true;
        }

        $sharesCount = PostShare::where('post_id', $postId)->count();

        return response()->json([
            'success' => true,
            'shared' => $shared,
            'shares_count' => $sharesCount,
        ]);
    }

    /**
     * Get mentioned users for autocomplete.
     */
    public function searchMentions(Request $request)
    {
        $query = $request->input('q');

        if (! $query || strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $users = User::where('name', 'like', '%'.$query.'%')
            ->limit(10)
            ->get(['id', 'name', 'profile_image']);

        return response()->json([
            'results' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_image' => $user->profile_image ? asset('storage/'.$user->profile_image) : null,
                ];
            }),
        ]);
    }

    /**
     * Format comment for response.
     */
    private function formatComment(Comment $comment, ?User $currentUser): array
    {
        $reactionCounts = $comment->reactions()
            ->selectRaw('reaction_type, COUNT(*) as count')
            ->groupBy('reaction_type')
            ->pluck('count', 'reaction_type')
            ->toArray();

        $userReaction = null;
        if ($currentUser) {
            $reaction = $comment->reactions()
                ->where('user_id', $currentUser->id)
                ->first();
            $userReaction = $reaction ? $reaction->reaction_type : null;
        }

        return [
            'id' => $comment->id,
            'post_id' => $comment->post_id,
            'user_id' => $comment->user_id,
            'parent_id' => $comment->parent_id,
            'content' => $comment->content,
            'created_at' => $comment->created_at->diffForHumans(),
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'email' => $comment->user->email,
                'profile_image' => $comment->user->profile_image ? asset('storage/'.$comment->user->profile_image) : null,
            ],
            'replies' => $comment->replies->map(function ($reply) use ($currentUser) {
                return $this->formatComment($reply, $currentUser);
            }),
            'reaction_counts' => $reactionCounts,
            'user_reaction' => $userReaction,
            'replies_count' => $comment->replies()->count(),
        ];
    }
}
