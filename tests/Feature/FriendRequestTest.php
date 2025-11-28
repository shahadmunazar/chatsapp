<?php

use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view home page with users', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    User::factory()->count(5)->create();

    $response = $this->actingAs($user)->get('/home');

    $response->assertStatus(200);
});

test('user can get all users list', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    User::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/friends/all');

    $response->assertSuccessful();
    $response->assertJsonCount(3); // Should return 3 users (excluding current user)
});

test('user can send friend request', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    $response = $this->actingAs($user1)->postJson('/friends/send', [
        'receiver_id' => $user2->id,
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('friend_requests', [
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'status' => 'pending',
    ]);
});

test('user cannot send friend request to themselves', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->postJson('/friends/send', [
        'receiver_id' => $user->id,
    ]);

    $response->assertUnprocessable();
});

test('user cannot send duplicate friend request', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    FriendRequest::create([
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user1)->postJson('/friends/send', [
        'receiver_id' => $user2->id,
    ]);

    $response->assertStatus(400);
});

test('user can view pending friend requests', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    FriendRequest::create([
        'sender_id' => $user2->id,
        'receiver_id' => $user1->id,
        'status' => 'pending',
    ]);

    FriendRequest::create([
        'sender_id' => $user3->id,
        'receiver_id' => $user1->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user1)->get('/friends/requests');

    $response->assertSuccessful();
    $response->assertJsonCount(2);
});

test('user can accept friend request', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    $request = FriendRequest::create([
        'sender_id' => $user2->id,
        'receiver_id' => $user1->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user1)->post("/friends/accept/{$request->id}");

    $response->assertSuccessful();
    $this->assertDatabaseHas('friend_requests', [
        'id' => $request->id,
        'status' => 'accepted',
    ]);
});

test('user can reject friend request', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    $request = FriendRequest::create([
        'sender_id' => $user2->id,
        'receiver_id' => $user1->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user1)->post("/friends/reject/{$request->id}");

    $response->assertSuccessful();
    $this->assertDatabaseHas('friend_requests', [
        'id' => $request->id,
        'status' => 'rejected',
    ]);
});

test('user can cancel sent friend request', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    $request = FriendRequest::create([
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user1)->delete("/friends/cancel/{$request->id}");

    $response->assertSuccessful();
    $this->assertDatabaseMissing('friend_requests', [
        'id' => $request->id,
    ]);
});

test('user cannot accept someone elses friend request', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $request = FriendRequest::create([
        'sender_id' => $user2->id,
        'receiver_id' => $user3->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user1)->post("/friends/accept/{$request->id}");

    $response->assertNotFound();
});

test('user cannot cancel someone elses friend request', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $request = FriendRequest::create([
        'sender_id' => $user2->id,
        'receiver_id' => $user3->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user1)->delete("/friends/cancel/{$request->id}");

    $response->assertNotFound();
});

test('users list shows correct friendship status', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    $user4 = User::factory()->create();

    // User1 sent request to User2
    FriendRequest::create([
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'status' => 'pending',
    ]);

    // User3 sent request to User1
    FriendRequest::create([
        'sender_id' => $user3->id,
        'receiver_id' => $user1->id,
        'status' => 'pending',
    ]);

    // User1 and User4 are friends
    FriendRequest::create([
        'sender_id' => $user1->id,
        'receiver_id' => $user4->id,
        'status' => 'accepted',
    ]);

    $response = $this->actingAs($user1)->get('/friends/all');

    $response->assertSuccessful();
    $data = $response->json();

    // Check User2 status is 'sent'
    $user2Data = collect($data)->firstWhere('id', $user2->id);
    expect($user2Data['friendship_status'])->toBe('sent');

    // Check User3 status is 'received'
    $user3Data = collect($data)->firstWhere('id', $user3->id);
    expect($user3Data['friendship_status'])->toBe('received');

    // Check User4 status is 'friends'
    $user4Data = collect($data)->firstWhere('id', $user4->id);
    expect($user4Data['friendship_status'])->toBe('friends');
});
