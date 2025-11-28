<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\UserRole;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guest cannot access admin dashboard', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});

test('regular user cannot access admin dashboard', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertStatus(403);
});

test('moderator can access admin dashboard', function () {
    $moderator = User::factory()->moderator()->create();

    $response = $this->actingAs($moderator)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertViewIs('admin.dashboard');
});

test('admin can access admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertViewIs('admin.dashboard');
    $response->assertViewHas('stats');
});

test('dashboard shows correct statistics', function () {
    $admin = User::factory()->admin()->create();

    //Get current counts
    $initialUserCount = User::count();
    $initialVerifiedCount = User::whereNotNull('email_verified_at')->count();
    $initialPostCount = Post::count();
    $initialCommentCount = Comment::count();

    // Create test data
    User::factory()->count(5)->create();
    User::factory()->count(3)->create(['email_verified_at' => now()]);
    Post::factory()->count(10)->create();
    Comment::factory()->count(15)->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $stats = $response->viewData('stats');

    // Verify counts increased by expected amounts
    expect($stats['total_users'])->toBe($initialUserCount + 8); // 5 + 3 new users
    expect($stats['verified_users'])->toBe($initialVerifiedCount + 3);
    expect($stats['total_posts'])->toBeGreaterThanOrEqual($initialPostCount + 10);
    expect($stats['total_comments'])->toBeGreaterThanOrEqual($initialCommentCount + 15);
});

test('dashboard includes recent users and posts', function () {
    $admin = User::factory()->admin()->create();

    // Create test data
    $recentUsers = User::factory()->count(5)->create();
    $recentPosts = Post::factory()->count(5)->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $stats = $response->viewData('stats');

    expect($stats['recent_users'])->toHaveCount(5);
    expect($stats['recent_posts'])->toHaveCount(5);
});

test('unverified user cannot access admin dashboard', function () {
    $admin = User::factory()->admin()->unverified()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertRedirect(route('verification.notice'));
});
