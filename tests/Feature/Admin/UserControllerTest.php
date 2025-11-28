<?php

use App\Models\User;
use App\UserRole;
use Illuminate\Support\Facades\Hash;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

// Index Tests
test('admin can view users list', function () {
    User::factory()->count(10)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

    $response->assertOk();
    $response->assertViewIs('admin.users.index');
    $response->assertViewHas('users');
});

test('users list includes pagination', function () {
    User::factory()->count(20)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

    $users = $response->viewData('users');
    expect($users)->toHaveProperty('total');
});

test('users can be searched by name', function () {
    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'John']));

    $response->assertSee('John Doe');
    $response->assertDontSee('Jane Smith');
});

test('users can be filtered by role', function () {
    User::factory()->moderator()->create();
    User::factory()->create(['role' => UserRole::User]);

    $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['role' => 'moderator']));

    $response->assertOk();
});

// Create Tests
test('admin can view create user form', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.users.create'));

    $response->assertOk();
    $response->assertViewIs('admin.users.create');
    $response->assertViewHas('roles');
});

test('admin can create new user', function () {
    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ];

    $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $userData);

    $response->assertRedirect(route('admin.users.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'user',
    ]);
});

test('admin can create user with admin role', function () {
    $userData = [
        'name' => 'Admin User',
        'email' => 'newadmin@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin',
    ];

    $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $userData);

    $response->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'newadmin@example.com')->first();
    expect($user->role)->toBe(UserRole::Admin);
});

test('user creation validates required fields', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.users.store'), []);

    $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
});

test('user creation validates unique email', function () {
    $existingUser = User::factory()->create();

    $userData = [
        'name' => 'Test User',
        'email' => $existingUser->email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ];

    $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $userData);

    $response->assertSessionHasErrors(['email']);
});

// Show Tests
test('admin can view user details', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.users.show', $user));

    $response->assertOk();
    $response->assertViewIs('admin.users.show');
    $response->assertViewHas('user');
    $response->assertSee($user->name);
    $response->assertSee($user->email);
});

// Edit Tests
test('admin can view edit user form', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $user));

    $response->assertOk();
    $response->assertViewIs('admin.users.edit');
    $response->assertViewHas('user');
    $response->assertViewHas('roles');
});

test('admin can update user', function () {
    $user = User::factory()->create();

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'role' => 'moderator',
    ];

    $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user), $updateData);

    $response->assertRedirect(route('admin.users.index'));
    $response->assertSessionHas('success');

    $user->refresh();
    expect($user->name)->toBe('Updated Name');
    expect($user->email)->toBe('updated@example.com');
    expect($user->role)->toBe(UserRole::Moderator);
});

test('admin can update user password', function () {
    $user = User::factory()->create();

    $updateData = [
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role->value,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ];

    $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user), $updateData);

    $response->assertRedirect(route('admin.users.index'));

    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
});

test('user update validates unique email except own', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $updateData = [
        'name' => $user->name,
        'email' => $otherUser->email,
        'role' => $user->role->value,
    ];

    $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user), $updateData);

    $response->assertSessionHasErrors(['email']);
});

// Delete Tests
test('admin can delete user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user));

    $response->assertRedirect(route('admin.users.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('admin cannot delete themselves', function () {
    $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

    $response->assertRedirect(route('admin.users.index'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
});

// Authorization Tests
test('regular user cannot access user management', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $response = $this->actingAs($user)->get(route('admin.users.index'));

    $response->assertStatus(403);
});

test('moderator can access user management', function () {
    $moderator = User::factory()->moderator()->create();

    $response = $this->actingAs($moderator)->get(route('admin.users.index'));

    $response->assertOk();
});
