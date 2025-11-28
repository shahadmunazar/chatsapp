<?php

use App\Models\User;
use App\UserRole;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guest is redirected to login', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});

test('regular user is denied access to admin routes', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertStatus(403);
});

test('moderator can access admin routes', function () {
    $moderator = User::factory()->moderator()->create();

    $response = $this->actingAs($moderator)->get(route('admin.dashboard'));

    $response->assertOk();
});

test('admin can access admin routes', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
});

test('unverified admin cannot access admin routes', function () {
    $admin = User::factory()->admin()->unverified()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertRedirect(route('verification.notice'));
});

test('unverified moderator cannot access admin routes', function () {
    $moderator = User::factory()->moderator()->unverified()->create();

    $response = $this->actingAs($moderator)->get(route('admin.dashboard'));

    $response->assertRedirect(route('verification.notice'));
});

test('role middleware checks all admin routes', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $adminRoutes = [
        ['GET', route('admin.dashboard')],
        ['GET', route('admin.users.index')],
        ['GET', route('admin.users.create')],
        ['GET', route('admin.posts.index')],
        ['GET', route('admin.posts.create')],
    ];

    foreach ($adminRoutes as [$method, $route]) {
        $response = $this->actingAs($user)->call($method, $route);
        expect($response->status())->toBe(403);
    }
});

test('moderator has same access as admin', function () {
    $moderator = User::factory()->moderator()->create();

    $adminRoutes = [
        ['GET', route('admin.dashboard')],
        ['GET', route('admin.users.index')],
        ['GET', route('admin.posts.index')],
    ];

    foreach ($adminRoutes as [$method, $route]) {
        $response = $this->actingAs($moderator)->call($method, $route);
        expect($response->status())->toBe(200);
    }
});
