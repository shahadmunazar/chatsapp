<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('authenticated user can view own profile', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/profile');

    $response->assertStatus(200);
    $response->assertSee($user->name);
    $response->assertSee($user->email);
});

test('authenticated user can view another user profile', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $otherUser = User::factory()->create(['name' => 'Other User']);

    $response = $this->actingAs($user)->get("/profile/{$otherUser->id}");

    $response->assertStatus(200);
    $response->assertSee('Other User');
});

test('guest can view user profile', function () {
    $user = User::factory()->create(['name' => 'Public User']);

    $response = $this->get("/profile/{$user->id}");

    $response->assertStatus(200);
    $response->assertSee('Public User');
});

test('user can update profile information', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->post('/profile/update', [
        'name' => 'Updated Name',
        'email' => $user->email,
        'bio' => 'This is my bio',
        'phone' => '1234567890',
        'city' => 'New York',
        'school' => 'MIT',
        'work' => 'Tech Corp',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'bio' => 'This is my bio',
        'city' => 'New York',
    ]);
});

test('user cannot update with invalid email', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->post('/profile/update', [
        'name' => 'Updated Name',
        'email' => 'invalid-email',
    ]);

    $response->assertSessionHasErrors('email');
});

test('user cannot use duplicate email', function () {
    $user1 = User::factory()->create(['email' => 'user1@example.com', 'email_verified_at' => now()]);
    $user2 = User::factory()->create(['email' => 'user2@example.com']);

    $response = $this->actingAs($user1)->post('/profile/update', [
        'name' => 'User 1',
        'email' => 'user2@example.com',
    ]);

    $response->assertSessionHasErrors('email');
});

test('user can upload profile image', function () {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);

    $file = UploadedFile::fake()->image('avatar.jpg');

    $response = $this->actingAs($user)->post('/profile/upload-image', [
        'profile_image' => $file,
    ]);

    $response->assertSuccessful();
    expect($user->fresh()->profile_image)->not->toBeNull();
    Storage::disk('public')->assertExists($user->fresh()->profile_image);
});

test('user cannot upload non-image file as profile image', function () {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);

    $file = UploadedFile::fake()->create('document.pdf', 100);

    $response = $this->actingAs($user)->post('/profile/upload-image', [
        'profile_image' => $file,
    ]);

    $response->assertSessionHasErrors('profile_image');
});

test('user cannot upload oversized profile image', function () {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);

    $file = UploadedFile::fake()->image('avatar.jpg')->size(3000); // 3MB

    $response = $this->actingAs($user)->post('/profile/upload-image', [
        'profile_image' => $file,
    ]);

    $response->assertSessionHasErrors('profile_image');
});

test('user can remove profile image', function () {
    Storage::fake('public');
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'profile_image' => 'profile_images/test.jpg',
    ]);

    Storage::disk('public')->put('profile_images/test.jpg', 'fake image content');

    $response = $this->actingAs($user)->delete('/profile/remove-image');

    $response->assertSuccessful();
    expect($user->fresh()->profile_image)->toBeNull();
    Storage::disk('public')->assertMissing('profile_images/test.jpg');
});

test('profile update validates required fields', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->post('/profile/update', [
        'name' => '',
        'email' => '',
    ]);

    $response->assertSessionHasErrors(['name', 'email']);
});

test('profile shows user statistics', function () {
    User::factory()->count(5)->create();
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/profile');

    $response->assertStatus(200);
    $response->assertSee('6'); // Total users including the authenticated user
});
