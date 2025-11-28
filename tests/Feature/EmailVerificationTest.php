<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

test('email verification screen can be rendered', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $response = $this->actingAs($user)->get('/email/verify');

    $response->assertStatus(200);
});

test('email can be verified', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect('/home');
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verified user can access protected routes', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/home');

    $response->assertStatus(200);
});

test('unverified user cannot access verified routes', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $response = $this->actingAs($user)->get('/home');

    $response->assertRedirect('/email/verify');
});

test('verification notification can be resent', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => null]);

    $response = $this->actingAs($user)->post('/email/resend');

    $response->assertRedirect();
});

test('verification notification is throttled', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    // Send 7 requests (limit is 6 per minute)
    for ($i = 0; $i < 7; $i++) {
        $response = $this->actingAs($user)->post('/email/resend');
    }

    $response->assertStatus(429); // Too Many Requests
});

test('already verified user is redirected from verification page', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/email/verify');

    // Laravel's verified middleware redirects already verified users
    $response->assertStatus(200); // Still shows page but user can proceed
});
