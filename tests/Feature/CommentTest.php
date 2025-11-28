<?php

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Post;
use App\Models\PostShare;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Comment Creation Tests
test('authenticated user can post a comment on a post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();

    $response = $this->actingAs($user)->postJson('/comments', [
        'post_id' => $post->id,
        'content' => 'This is a test comment',
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('comments', [
        'post_id' => $post->id,
        'user_id' => $user->id,
        'content' => 'This is a test comment',
        'parent_id' => null,
    ]);
});

test('guest cannot post a comment', function () {
    $post = Post::factory()->create();

    $response = $this->postJson('/comments', [
        'post_id' => $post->id,
        'content' => 'This is a test comment',
    ]);

    $response->assertUnauthorized();
});

test('comment content is required', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();

    $response = $this->actingAs($user)->postJson('/comments', [
        'post_id' => $post->id,
        'content' => '',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('content');
});

test('comment content cannot exceed 1000 characters', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();

    $response = $this->actingAs($user)->postJson('/comments', [
        'post_id' => $post->id,
        'content' => str_repeat('a', 1001),
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('content');
});

// Reply Tests
test('user can reply to a comment', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();
    $parentComment = Comment::factory()->create(['post_id' => $post->id]);

    $response = $this->actingAs($user)->postJson('/comments', [
        'post_id' => $post->id,
        'parent_id' => $parentComment->id,
        'content' => 'This is a reply',
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('comments', [
        'post_id' => $post->id,
        'user_id' => $user->id,
        'parent_id' => $parentComment->id,
        'content' => 'This is a reply',
    ]);
});

test('parent comment must exist for replies', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();

    $response = $this->actingAs($user)->postJson('/comments', [
        'post_id' => $post->id,
        'parent_id' => 999999,
        'content' => 'This is a reply',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('parent_id');
});

// Get Comments Tests
test('anyone can get comments for a post', function () {
    $post = Post::factory()->create();
    $comment1 = Comment::factory()->create(['post_id' => $post->id]);
    $comment2 = Comment::factory()->create(['post_id' => $post->id]);

    $response = $this->get("/posts/{$post->id}/comments");

    $response->assertSuccessful();
    $response->assertJsonCount(2);
});

test('comments include user information', function () {
    $post = Post::factory()->create();
    $comment = Comment::factory()->create(['post_id' => $post->id]);

    $response = $this->get("/posts/{$post->id}/comments");

    $response->assertSuccessful();
    $data = $response->json();
    expect($data[0])->toHaveKey('user');
    expect($data[0]['user'])->toHaveKeys(['id', 'name', 'email', 'profile_image']);
});

test('comments include replies', function () {
    $post = Post::factory()->create();
    $comment = Comment::factory()->create(['post_id' => $post->id]);
    $reply = Comment::factory()->create([
        'post_id' => $post->id,
        'parent_id' => $comment->id,
    ]);

    $response = $this->get("/posts/{$post->id}/comments");

    $response->assertSuccessful();
    $data = $response->json();
    expect($data[0])->toHaveKey('replies');
    expect(count($data[0]['replies']))->toBe(1);
});

// Delete Comment Tests
test('user can delete own comment', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $comment = Comment::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/comments/{$comment->id}");

    $response->assertSuccessful();
    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
});

test('user cannot delete others comment', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create(['email_verified_at' => now()]);
    $comment = Comment::factory()->create(['user_id' => $user2->id]);

    $response = $this->actingAs($user1)->deleteJson("/comments/{$comment->id}");

    $response->assertNotFound();
    $this->assertDatabaseHas('comments', ['id' => $comment->id]);
});

test('guest cannot delete comments', function () {
    $comment = Comment::factory()->create();

    $response = $this->deleteJson("/comments/{$comment->id}");

    $response->assertUnauthorized();
    $this->assertDatabaseHas('comments', ['id' => $comment->id]);
});

test('deleting comment also deletes replies', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $comment = Comment::factory()->create(['user_id' => $user->id]);
    $reply = Comment::factory()->create(['parent_id' => $comment->id]);

    $response = $this->actingAs($user)->deleteJson("/comments/{$comment->id}");

    $response->assertSuccessful();
    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    $this->assertDatabaseMissing('comments', ['id' => $reply->id]);
});

// Comment Reactions Tests
test('user can react to a comment', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $comment = Comment::factory()->create();

    $response = $this->actingAs($user)->postJson("/comments/{$comment->id}/react", [
        'reaction_type' => 'like',
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('comment_reactions', [
        'comment_id' => $comment->id,
        'user_id' => $user->id,
        'reaction_type' => 'like',
    ]);
});

test('user can toggle reaction on comment', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $comment = Comment::factory()->create();
    CommentReaction::create([
        'comment_id' => $comment->id,
        'user_id' => $user->id,
        'reaction_type' => 'like',
    ]);

    $response = $this->actingAs($user)->postJson("/comments/{$comment->id}/react", [
        'reaction_type' => 'like',
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseMissing('comment_reactions', [
        'comment_id' => $comment->id,
        'user_id' => $user->id,
    ]);
});

test('user can only have one reaction per comment', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $comment = Comment::factory()->create();

    // First reaction
    $this->actingAs($user)->postJson("/comments/{$comment->id}/react", [
        'reaction_type' => 'like',
    ]);

    // Second reaction (different type)
    $response = $this->actingAs($user)->postJson("/comments/{$comment->id}/react", [
        'reaction_type' => 'love',
    ]);

    $response->assertSuccessful();
    // Should only have love reaction now
    $this->assertDatabaseHas('comment_reactions', [
        'comment_id' => $comment->id,
        'user_id' => $user->id,
        'reaction_type' => 'love',
    ]);
    $this->assertDatabaseMissing('comment_reactions', [
        'comment_id' => $comment->id,
        'user_id' => $user->id,
        'reaction_type' => 'like',
    ]);
});

test('reaction type must be valid', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $comment = Comment::factory()->create();

    $response = $this->actingAs($user)->postJson("/comments/{$comment->id}/react", [
        'reaction_type' => 'invalid',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('reaction_type');
});

test('guest cannot react to comments', function () {
    $comment = Comment::factory()->create();

    $response = $this->postJson("/comments/{$comment->id}/react", [
        'reaction_type' => 'like',
    ]);

    $response->assertUnauthorized();
});

// Post Share Tests
test('user can share a post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();

    $response = $this->actingAs($user)->postJson("/posts/{$post->id}/share");

    $response->assertSuccessful();
    $this->assertDatabaseHas('post_shares', [
        'post_id' => $post->id,
        'user_id' => $user->id,
    ]);
});

test('user can share post with a message', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();

    $response = $this->actingAs($user)->postJson("/posts/{$post->id}/share", [
        'message' => 'Check this out!',
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('post_shares', [
        'post_id' => $post->id,
        'user_id' => $user->id,
        'message' => 'Check this out!',
    ]);
});

test('user can toggle share on post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();
    PostShare::create([
        'post_id' => $post->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->postJson("/posts/{$post->id}/share");

    $response->assertSuccessful();
    $response->assertJson(['shared' => false]);
    $this->assertDatabaseMissing('post_shares', [
        'post_id' => $post->id,
        'user_id' => $user->id,
    ]);
});

test('guest cannot share posts', function () {
    $post = Post::factory()->create();

    $response = $this->postJson("/posts/{$post->id}/share");

    $response->assertUnauthorized();
});

test('share message cannot exceed 500 characters', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();

    $response = $this->actingAs($user)->postJson("/posts/{$post->id}/share", [
        'message' => str_repeat('a', 501),
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('message');
});

// Mention Search Tests
test('anyone can search for mentions', function () {
    $user1 = User::factory()->create(['name' => 'John Doe']);
    $user2 = User::factory()->create(['name' => 'Jane Smith']);

    $response = $this->get('/mentions/search?q=John');

    $response->assertSuccessful();
    $data = $response->json();
    expect($data)->toHaveKey('results');
    expect(count($data['results']))->toBeGreaterThanOrEqual(1);
});

test('mention search requires minimum 2 characters', function () {
    $response = $this->get('/mentions/search?q=J');

    $response->assertSuccessful();
    $data = $response->json();
    expect($data['results'])->toBeArray();
    expect($data['results'])->toBeEmpty();
});

test('mention search returns user details', function () {
    $user = User::factory()->create(['name' => 'TestUser']);

    $response = $this->get('/mentions/search?q=Test');

    $response->assertSuccessful();
    $data = $response->json();
    expect($data['results'][0])->toHaveKeys(['id', 'name', 'profile_image']);
});

// Integration Tests
test('posts include comment count', function () {
    $post = Post::factory()->create();
    Comment::factory(3)->create(['post_id' => $post->id]);

    $response = $this->get('/posts');

    $response->assertSuccessful();
    $data = $response->json();
    expect($data[0]['comments_count'])->toBe(3);
});

test('posts include share count', function () {
    $post = Post::factory()->create();
    $users = User::factory(2)->create(['email_verified_at' => now()]);

    foreach ($users as $user) {
        PostShare::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    $response = $this->get('/posts');

    $response->assertSuccessful();
    $data = $response->json();
    expect($data[0]['shares_count'])->toBe(2);
});

test('posts indicate if current user has shared', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create(['user_id' => $user->id]);
    PostShare::create([
        'post_id' => $post->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get('/posts');

    $response->assertSuccessful();
    $data = $response->json();
    expect($data[0]['is_shared'])->toBeTrue();
});
