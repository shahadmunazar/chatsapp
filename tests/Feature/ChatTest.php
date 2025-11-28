<?php

use App\Models\FriendRequest;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('authenticated user can view chat page', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/chat');

    $response->assertStatus(200);
});

test('user can get list of friends for chat', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    // Make user2 and user3 friends with user1
    FriendRequest::create([
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'status' => 'accepted',
    ]);

    FriendRequest::create([
        'sender_id' => $user3->id,
        'receiver_id' => $user1->id,
        'status' => 'accepted',
    ]);

    $response = $this->actingAs($user1)->get('/chat/users');

    $response->assertSuccessful();
    $response->assertJsonCount(2);
});

test('user can send text message to friend', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    FriendRequest::create([
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'status' => 'accepted',
    ]);

    $response = $this->actingAs($user1)->postJson('/chat/send', [
        'receiver_id' => $user2->id,
        'message' => 'Hello friend!',
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('messages', [
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'message' => 'Hello friend!',
    ]);
});

test('user cannot send empty message without file', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    $response = $this->actingAs($user1)->postJson('/chat/send', [
        'receiver_id' => $user2->id,
    ]);

    $response->assertUnprocessable();
});

test('user can send message with file attachment', function () {
    Storage::fake('public');
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    $file = UploadedFile::fake()->image('photo.jpg');

    $response = $this->actingAs($user1)->post('/chat/send', [
        'receiver_id' => $user2->id,
        'message' => 'Check this out!',
        'file' => $file,
    ]);

    $response->assertSuccessful();
    $message = Message::where('sender_id', $user1->id)->first();

    expect($message->file_path)->not->toBeNull();
    expect($message->file_name)->toBe('photo.jpg');
    expect($message->file_type)->toBe('image');
    Storage::disk('public')->assertExists($message->file_path);
});

test('user can send file without message text', function () {
    Storage::fake('public');
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    $file = UploadedFile::fake()->image('document.pdf');

    $response = $this->actingAs($user1)->post('/chat/send', [
        'receiver_id' => $user2->id,
        'file' => $file,
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('messages', [
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
    ]);
});

test('user cannot send file larger than 10MB', function () {
    Storage::fake('public');
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    $file = UploadedFile::fake()->create('largefile.pdf', 11000); // 11MB

    $response = $this->actingAs($user1)->post('/chat/send', [
        'receiver_id' => $user2->id,
        'file' => $file,
    ]);

    $response->assertSessionHasErrors('file');
});

test('user can get chat history with friend', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    // Create messages
    Message::create([
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'message' => 'Hello',
    ]);

    Message::create([
        'sender_id' => $user2->id,
        'receiver_id' => $user1->id,
        'message' => 'Hi there',
    ]);

    Message::create([
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'message' => 'How are you?',
    ]);

    $response = $this->actingAs($user1)->get("/chat/history/{$user2->id}");

    $response->assertSuccessful();
    $response->assertJsonCount(3);
});

test('chat history marks messages as read', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    $message = Message::create([
        'sender_id' => $user2->id,
        'receiver_id' => $user1->id,
        'message' => 'Unread message',
        'is_read' => false,
    ]);

    $this->actingAs($user1)->get("/chat/history/{$user2->id}");

    expect($message->fresh()->is_read)->toBeTrue();
});

test('user can update activity status', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->post('/chat/activity');

    $response->assertSuccessful();
    expect($user->fresh()->last_seen_at)->not->toBeNull();
});

test('file type is correctly determined for images', function () {
    Storage::fake('public');
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    $file = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($user1)->post('/chat/send', [
        'receiver_id' => $user2->id,
        'file' => $file,
    ]);

    $message = Message::where('sender_id', $user1->id)->first();
    expect($message->file_type)->toBe('image');
});

test('file type is correctly determined for documents', function () {
    Storage::fake('public');
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    $file = UploadedFile::fake()->create('document.pdf', 100);

    $this->actingAs($user1)->post('/chat/send', [
        'receiver_id' => $user2->id,
        'file' => $file,
    ]);

    $message = Message::where('sender_id', $user1->id)->first();
    expect($message->file_type)->toBe('document');
});

test('file type is correctly determined for text files', function () {
    Storage::fake('public');
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    $file = UploadedFile::fake()->create('notes.txt', 10);

    $this->actingAs($user1)->post('/chat/send', [
        'receiver_id' => $user2->id,
        'file' => $file,
    ]);

    $message = Message::where('sender_id', $user1->id)->first();
    expect($message->file_type)->toBe('text');
});

test('user list shows last message', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    FriendRequest::create([
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'status' => 'accepted',
    ]);

    Message::create([
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'message' => 'Latest message',
    ]);

    $response = $this->actingAs($user1)->get('/chat/users');

    $response->assertSuccessful();
    $data = $response->json();
    expect($data[0]['last_message'])->toBe('Latest message');
});

test('user list shows unread count', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create();

    FriendRequest::create([
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'status' => 'accepted',
    ]);

    // Create unread messages
    Message::create([
        'sender_id' => $user2->id,
        'receiver_id' => $user1->id,
        'message' => 'Unread 1',
        'is_read' => false,
    ]);

    Message::create([
        'sender_id' => $user2->id,
        'receiver_id' => $user1->id,
        'message' => 'Unread 2',
        'is_read' => false,
    ]);

    $response = $this->actingAs($user1)->get('/chat/users');

    $response->assertSuccessful();
    $data = $response->json();
    expect($data[0]['unread_count'])->toBe(2);
});
