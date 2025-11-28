<?php

use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guest can view wall page', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('authenticated user can view wall page', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/');

    $response->assertStatus(200);
});

test('guest can get all posts', function () {
    $user = User::factory()->create();
    Post::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->get('/posts');

    $response->assertSuccessful();
    $response->assertJsonCount(3);
});

test('user can create post with text only', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->postJson('/posts', [
        'content' => 'This is my first post!',
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('posts', [
        'user_id' => $user->id,
        'content' => 'This is my first post!',
    ]);
});

test('user can create post with image only', function () {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);

    $image = UploadedFile::fake()->image('photo.jpg');

    $response = $this->actingAs($user)->post('/posts', [
        'image' => $image,
    ]);

    $response->assertSuccessful();
    $post = Post::where('user_id', $user->id)->first();
    expect($post->image)->not->toBeNull();
    Storage::disk('public')->assertExists($post->image);
});

test('user can create post with text and image', function () {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);

    $image = UploadedFile::fake()->image('photo.jpg');

    $response = $this->actingAs($user)->post('/posts', [
        'content' => 'Check this out!',
        'image' => $image,
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('posts', [
        'user_id' => $user->id,
        'content' => 'Check this out!',
    ]);

    $post = Post::where('user_id', $user->id)->first();
    expect($post->image)->not->toBeNull();
});

test('user cannot create post without content or image', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->postJson('/posts', []);

    $response->assertUnprocessable();
});

test('user cannot upload oversized image for post', function () {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);

    $image = UploadedFile::fake()->image('photo.jpg')->size(6000); // 6MB (limit is 5MB)

    $response = $this->actingAs($user)->post('/posts', [
        'content' => 'Test',
        'image' => $image,
    ]);

    $response->assertSessionHasErrors('image');
});

test('user cannot upload non-image file for post', function () {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);

    $file = UploadedFile::fake()->create('document.pdf', 100);

    $response = $this->actingAs($user)->post('/posts', [
        'content' => 'Test',
        'image' => $file,
    ]);

    $response->assertSessionHasErrors('image');
});

test('user can get their own posts', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    Post::factory()->count(3)->create(['user_id' => $user->id]);

    $otherUser = User::factory()->create();
    Post::factory()->count(2)->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->get("/posts/user/{$user->id}");

    $response->assertSuccessful();
    $response->assertJsonCount(3);
});

test('user can like a post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();

    $response = $this->actingAs($user)->post("/posts/{$post->id}/like");

    $response->assertSuccessful();
    $this->assertDatabaseHas('post_likes', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);
});

test('user can unlike a post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();

    PostLike::create([
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);

    $response = $this->actingAs($user)->post("/posts/{$post->id}/like");

    $response->assertSuccessful();
    $this->assertDatabaseMissing('post_likes', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);
});

test('like toggle returns correct status', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create();

    // Like
    $response = $this->actingAs($user)->postJson("/posts/{$post->id}/like");
    $response->assertJson(['liked' => true]);

    // Unlike
    $response = $this->actingAs($user)->postJson("/posts/{$post->id}/like");
    $response->assertJson(['liked' => false]);
});

test('user can delete own post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->delete("/posts/{$post->id}");

    $response->assertSuccessful();
    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);
});

test('user cannot delete others post', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user2->id]);

    $response = $this->actingAs($user1)->delete("/posts/{$post->id}");

    $response->assertNotFound();
});

test('deleting post also deletes associated image', function () {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);

    $post = Post::factory()->create([
        'user_id' => $user->id,
        'image' => 'posts/test.jpg',
    ]);

    Storage::disk('public')->put('posts/test.jpg', 'fake image content');

    $this->actingAs($user)->delete("/posts/{$post->id}");

    Storage::disk('public')->assertMissing('posts/test.jpg');
});

test('post includes likes count', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    $post = Post::factory()->create();

    PostLike::create(['user_id' => $user1->id, 'post_id' => $post->id]);
    PostLike::create(['user_id' => $user2->id, 'post_id' => $post->id]);
    PostLike::create(['user_id' => $user3->id, 'post_id' => $post->id]);

    $response = $this->get('/posts');

    $response->assertSuccessful();
    $data = $response->json();
    expect($data[0]['likes_count'])->toBe(3);
});

test('post indicates if current user liked it', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = Post::factory()->create(['user_id' => $user->id]);

    PostLike::create(['user_id' => $user->id, 'post_id' => $post->id]);

    $response = $this->actingAs($user)->get('/posts');

    $response->assertSuccessful();
    $data = $response->json();
    expect($data[0]['is_liked'])->toBeTrue();
});

test('guest cannot like posts', function () {
    $post = Post::factory()->create();

    $response = $this->postJson("/posts/{$post->id}/like");

    $response->assertUnauthorized();
});

test('guest cannot create posts', function () {
    $response = $this->postJson('/posts', [
        'content' => 'Test post',
    ]);

    $response->assertUnauthorized();
});

test('guest cannot delete posts', function () {
    $post = Post::factory()->create();

    $response = $this->deleteJson("/posts/{$post->id}");

    $response->assertUnauthorized();
});

test('posts are ordered by newest first', function () {
    $user = User::factory()->create();

    $post1 = Post::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(3)]);
    $post2 = Post::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(1)]);
    $post3 = Post::factory()->create(['user_id' => $user->id, 'created_at' => now()]);

    $response = $this->get('/posts');

    $response->assertSuccessful();
    $data = $response->json();

    expect($data[0]['id'])->toBe($post3->id);
    expect($data[1]['id'])->toBe($post2->id);
    expect($data[2]['id'])->toBe($post1->id);
});

test('post content can be up to 5000 characters', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $longContent = str_repeat('a', 5000);

    $response = $this->actingAs($user)->postJson('/posts', [
        'content' => $longContent,
    ]);

    $response->assertSuccessful();
});

test('post content cannot exceed 5000 characters', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $tooLongContent = str_repeat('a', 5001);

    $response = $this->actingAs($user)->postJson('/posts', [
        'content' => $tooLongContent,
    ]);

    $response->assertUnprocessable();
});

test('posts include user information', function () {
    $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
    Post::factory()->create(['user_id' => $user->id]);

    $response = $this->get('/posts');

    $response->assertSuccessful();
    $data = $response->json();
    expect($data[0]['user']['name'])->toBe('Test User');
    expect($data[0]['user']['email'])->toBe('test@example.com');
});
