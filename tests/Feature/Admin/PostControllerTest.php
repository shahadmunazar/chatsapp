<?php

use App\Models\Post;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    Storage::fake('public');
});

// Index Tests
test('admin can view posts list', function () {
    Post::factory()->count(10)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.posts.index'));

    $response->assertOk();
    $response->assertViewIs('admin.posts.index');
    $response->assertViewHas('posts');
});

test('posts list includes pagination', function () {
    Post::factory()->count(20)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.posts.index'));

    $posts = $response->viewData('posts');
    expect($posts)->toHaveProperty('total');
});

test('posts can be searched by content', function () {
    Post::factory()->create(['content' => 'Laravel is amazing']);
    Post::factory()->create(['content' => 'PHP is great']);

    $response = $this->actingAs($this->admin)->get(route('admin.posts.index', ['search' => 'Laravel']));

    $response->assertSee('Laravel is amazing');
    $response->assertDontSee('PHP is great');
});

test('posts can be filtered by user', function () {
    $user = User::factory()->create();
    Post::factory()->for($user)->create();
    Post::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.posts.index', ['user_id' => $user->id]));

    $response->assertOk();
});

// Create Tests
test('admin can view create post form', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.posts.create'));

    $response->assertOk();
    $response->assertViewIs('admin.posts.create');
});

test('admin can create post with content only', function () {
    $postData = [
        'content' => 'This is a test post from admin',
    ];

    $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), $postData);

    $response->assertRedirect(route('admin.posts.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('posts', [
        'content' => 'This is a test post from admin',
        'user_id' => $this->admin->id,
    ]);
});

test('admin can create post with image', function () {
    $image = UploadedFile::fake()->image('test-post.jpg', 800, 600);

    $postData = [
        'content' => 'Post with image',
        'image' => $image,
    ];

    $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), $postData);

    $response->assertRedirect(route('admin.posts.index'));

    $post = Post::where('content', 'Post with image')->first();
    expect($post)->not->toBeNull();
    expect($post->image)->not->toBeNull();

    Storage::disk('public')->assertExists($post->image);
});

test('admin can create post with image only', function () {
    $image = UploadedFile::fake()->image('test-post.jpg');

    $postData = [
        'image' => $image,
    ];

    $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), $postData);

    $response->assertRedirect(route('admin.posts.index'));
});

test('post creation validates image type', function () {
    $file = UploadedFile::fake()->create('document.pdf');

    $postData = [
        'content' => 'Test post',
        'image' => $file,
    ];

    $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), $postData);

    $response->assertSessionHasErrors(['image']);
});

test('post creation validates image size', function () {
    $file = UploadedFile::fake()->image('large.jpg')->size(6000); // 6MB, exceeds 5MB limit

    $postData = [
        'content' => 'Test post',
        'image' => $file,
    ];

    $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), $postData);

    $response->assertSessionHasErrors(['image']);
});

// Show Tests
test('admin can view post details', function () {
    $post = Post::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.posts.show', $post));

    $response->assertOk();
    $response->assertViewIs('admin.posts.show');
    $response->assertViewHas('post');
    $response->assertSee($post->content);
});

test('post details show user information', function () {
    $user = User::factory()->create(['name' => 'Post Author']);
    $post = Post::factory()->for($user)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.posts.show', $post));

    $response->assertSee('Post Author');
});

// Edit Tests
test('admin can view edit post form', function () {
    $post = Post::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.posts.edit', $post));

    $response->assertOk();
    $response->assertViewIs('admin.posts.edit');
    $response->assertViewHas('post');
});

test('admin can update post content', function () {
    $post = Post::factory()->create();

    $updateData = [
        'content' => 'Updated post content',
    ];

    $response = $this->actingAs($this->admin)->put(route('admin.posts.update', $post), $updateData);

    $response->assertRedirect(route('admin.posts.index'));
    $response->assertSessionHas('success');

    $post->refresh();
    expect($post->content)->toBe('Updated post content');
});

test('admin can update post image', function () {
    $post = Post::factory()->create(['image' => 'posts/old-image.jpg']);
    Storage::disk('public')->put('posts/old-image.jpg', 'old content');

    $newImage = UploadedFile::fake()->image('new-image.jpg');

    $updateData = [
        'content' => $post->content,
        'image' => $newImage,
    ];

    $response = $this->actingAs($this->admin)->put(route('admin.posts.update', $post), $updateData);

    $response->assertRedirect(route('admin.posts.index'));

    $post->refresh();
    Storage::disk('public')->assertExists($post->image);
    Storage::disk('public')->assertMissing('posts/old-image.jpg');
});

test('admin can remove post image', function () {
    $post = Post::factory()->create(['image' => 'posts/test-image.jpg']);
    Storage::disk('public')->put('posts/test-image.jpg', 'content');

    $updateData = [
        'content' => $post->content,
        'remove_image' => true,
    ];

    $response = $this->actingAs($this->admin)->put(route('admin.posts.update', $post), $updateData);

    $response->assertRedirect(route('admin.posts.index'));

    $post->refresh();
    expect($post->image)->toBeNull();
    Storage::disk('public')->assertMissing('posts/test-image.jpg');
});

// Delete Tests
test('admin can delete post', function () {
    $post = Post::factory()->create();

    $response = $this->actingAs($this->admin)->delete(route('admin.posts.destroy', $post));

    $response->assertRedirect(route('admin.posts.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

test('deleting post also deletes image', function () {
    $post = Post::factory()->create(['image' => 'posts/test-image.jpg']);
    Storage::disk('public')->put('posts/test-image.jpg', 'content');

    $response = $this->actingAs($this->admin)->delete(route('admin.posts.destroy', $post));

    $response->assertRedirect(route('admin.posts.index'));

    Storage::disk('public')->assertMissing('posts/test-image.jpg');
});

// Authorization Tests
test('regular user cannot access post management', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $response = $this->actingAs($user)->get(route('admin.posts.index'));

    $response->assertStatus(403);
});

test('moderator can access post management', function () {
    $moderator = User::factory()->moderator()->create();

    $response = $this->actingAs($moderator)->get(route('admin.posts.index'));

    $response->assertOk();
});

test('guest cannot access post management', function () {
    $response = $this->get(route('admin.posts.index'));

    $response->assertRedirect(route('login'));
});
