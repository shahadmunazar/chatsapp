<?php

use App\Models\User;
use App\UserRole;

test('user has role method works correctly', function () {
    $admin = User::factory()->admin()->make();
    $moderator = User::factory()->moderator()->make();
    $user = User::factory()->make();

    expect($admin->hasRole(UserRole::Admin))->toBeTrue();
    expect($moderator->hasRole(UserRole::Moderator))->toBeTrue();
    expect($user->hasRole(UserRole::User))->toBeTrue();
});

test('user is admin method works correctly', function () {
    $admin = User::factory()->admin()->make();
    $moderator = User::factory()->moderator()->make();
    $user = User::factory()->make();

    expect($admin->isAdmin())->toBeTrue();
    expect($moderator->isAdmin())->toBeFalse();
    expect($user->isAdmin())->toBeFalse();
});

test('user is moderator method works correctly', function () {
    $admin = User::factory()->admin()->make();
    $moderator = User::factory()->moderator()->make();
    $user = User::factory()->make();

    expect($admin->isModerator())->toBeFalse();
    expect($moderator->isModerator())->toBeTrue();
    expect($user->isModerator())->toBeFalse();
});

test('user is staff method works correctly', function () {
    $admin = User::factory()->admin()->make();
    $moderator = User::factory()->moderator()->make();
    $user = User::factory()->make();

    expect($admin->isStaff())->toBeTrue();
    expect($moderator->isStaff())->toBeTrue();
    expect($user->isStaff())->toBeFalse();
});

test('user role enum has correct values', function () {
    $values = UserRole::values();

    expect($values)->toContain('admin', 'moderator', 'user');
    expect($values)->toHaveCount(3);
});

test('user role enum has correct labels', function () {
    expect(UserRole::Admin->label())->toBe('Administrator');
    expect(UserRole::Moderator->label())->toBe('Moderator');
    expect(UserRole::User->label())->toBe('User');
});

test('user role casts correctly to enum', function () {
    $user = User::factory()->create(['role' => 'admin']);

    expect($user->role)->toBeInstanceOf(UserRole::class);
    expect($user->role)->toBe(UserRole::Admin);
});

test('user role enum value matches database value', function () {
    $user = User::factory()->create(['role' => 'moderator']);

    expect($user->role->value)->toBe('moderator');
});
