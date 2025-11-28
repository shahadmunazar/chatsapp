<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FriendRequestController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// Landing page (Wall)
Route::get('/', [PostController::class, 'index'])->name('wall');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        return redirect()->intended('/home');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
})->name('login.attempt');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');

// Registration routes
Route::get('/register', function () {
    return view('register');
})->name('register');

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    event(new Registered($user));

    Auth::login($user);
    $request->session()->regenerate();

    return redirect()->route('verification.notice');
})->name('register.store');

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/home')->with('success', 'Email verified successfully!');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.resend');

// Public statistics
Route::get('/statistics', [ProfileController::class, 'statistics']);

// Public Posts, Comments & Users (accessible to everyone)
Route::get('/posts', [PostController::class, 'getPosts']);
Route::get('/posts/user/{userId}', [PostController::class, 'getUserPosts']);
Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
Route::get('/users/all', [ProfileController::class, 'allUsers']);
Route::get('/users/search', [ProfileController::class, 'searchUsers']);
Route::get('/mentions/search', [CommentController::class, 'searchMentions']);
Route::get('/profile/{id}', [ProfileController::class, 'view'])->name('profile.view');

// Authenticated routes (email verification required)
Route::middleware(['auth', 'verified'])->group(function () {
    // Home/Friends
    Route::get('/home', [FriendRequestController::class, 'index'])->name('home');
    Route::get('/friends/all', [FriendRequestController::class, 'allUsers']);
    Route::get('/friends/requests', [FriendRequestController::class, 'pending']);
    Route::post('/friends/send', [FriendRequestController::class, 'send']);
    Route::post('/friends/accept/{id}', [FriendRequestController::class, 'accept']);
    Route::post('/friends/reject/{id}', [FriendRequestController::class, 'reject']);
    Route::delete('/friends/cancel/{id}', [FriendRequestController::class, 'cancel']);

    // Profile (Own)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/profile/upload-image', [ProfileController::class, 'uploadImage']);
    Route::delete('/profile/remove-image', [ProfileController::class, 'removeImage']);

    // Posts/Wall (Actions)
    Route::post('/posts', [PostController::class, 'store']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::post('/posts/{id}/like', [PostController::class, 'toggleLike']);
    Route::post('/posts/{id}/share', [CommentController::class, 'sharePost']);

    // Comments (Actions)
    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    Route::post('/comments/{id}/react', [CommentController::class, 'toggleReaction']);

    // Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::get('/chat/users', [ChatController::class, 'users']);
    Route::get('/chat/history/{userId}', [ChatController::class, 'history']);
    Route::post('/chat/send', [ChatController::class, 'send']);
    Route::post('/chat/activity', [ChatController::class, 'updateActivity']);
});

// Admin routes (role-based access)
Route::middleware(['auth', 'verified', 'role:admin,moderator'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', AdminUserController::class);

    // Post Management
    Route::resource('posts', AdminPostController::class);
});
