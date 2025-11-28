<?php

namespace App\Models;

use App\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_image',
        'last_seen_at',
        'bio',
        'phone',
        'date_of_birth',
        'gender',
        'school',
        'college',
        'work',
        'address',
        'city',
        'state',
        'country',
        'website',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'role' => UserRole::class,
        ];
    }

    /**
     * Check if user is online (active within last 5 minutes).
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(5));
    }

    /**
     * Get formatted last seen time.
     */
    public function getLastSeenAttribute(): ?string
    {
        if (! $this->last_seen_at) {
            return null;
        }

        if ($this->isOnline()) {
            return 'Online';
        }

        return $this->last_seen_at->diffForHumans();
    }

    /**
     * Check if this user is friends with another user.
     */
    public function isFriendsWith(User $user): bool
    {
        return $this->sentFriendRequests()
            ->where('receiver_id', $user->id)
            ->where('status', 'accepted')
            ->exists()
            || $this->receivedFriendRequests()
                ->where('sender_id', $user->id)
                ->where('status', 'accepted')
                ->exists();
    }

    /**
     * Friend requests sent by this user.
     */
    public function sentFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'sender_id');
    }

    /**
     * Friend requests received by this user.
     */
    public function receivedFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'receiver_id');
    }

    /**
     * Get all friends of this user.
     */
    public function friends()
    {
        $sentFriends = $this->sentFriendRequests()
            ->where('status', 'accepted')
            ->with('receiver')
            ->get()
            ->pluck('receiver');

        $receivedFriends = $this->receivedFriendRequests()
            ->where('status', 'accepted')
            ->with('sender')
            ->get()
            ->pluck('sender');

        return $sentFriends->merge($receivedFriends);
    }

    /**
     * Get the posts for the user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Send the email verification notification (queued).
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\QueuedVerifyEmail);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Check if user is a moderator.
     */
    public function isModerator(): bool
    {
        return $this->role === UserRole::Moderator;
    }

    /**
     * Check if user has admin or moderator role.
     */
    public function isStaff(): bool
    {
        return $this->isAdmin() || $this->isModerator();
    }
}
