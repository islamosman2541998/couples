<?php

use App\Http\Controllers\Admin\CardController;
use App\Http\Controllers\Admin\CardLevelController;
use App\Http\Controllers\Admin\ChallengeCardController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GameManageController;
use App\Http\Controllers\Admin\KnowMeController;
use App\Http\Controllers\Admin\ScratchCardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SpinnerController;
use App\Http\Controllers\Admin\SubscriptionManageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WhoQuestionController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

// ==================== Frontend ====================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/games/{slug}/scratch-cards/{number}', [GameController::class, 'scratchCard'])
    ->whereNumber('number')
    ->name('games.scratch-card');
Route::get('/games/{slug}', [GameController::class, 'show'])->name('games.show');
Route::get('/games/{slug}/play', [GameController::class, 'play'])->name('games.play');
Route::get('/subscribe/success', [SubscriptionController::class, 'success'])->name('subscribe.success');
Route::get('/subscribe/{gameId}', [SubscriptionController::class, 'create'])->middleware('auth')->name('subscribe.create');
Route::post('/subscribe', [SubscriptionController::class, 'store'])->middleware('auth')->name('subscribe.store');

// Static pages
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

// User Profile
Route::middleware(['auth'])->group(function () {
    Route::get('/my-profile', [UserProfileController::class, 'index'])->name('profile.index');
    Route::get('/my-profile/edit', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/my-profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::delete('/my-profile', [UserProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==================== Admin ====================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Games
    Route::resource('games', GameManageController::class)->except(['show']);
    Route::patch('games/{game}/toggle', [GameManageController::class, 'toggle'])->name('games.toggle');

    // Cards
    Route::resource('cards', CardController::class)->except(['show']);
    Route::resource('card-levels', CardLevelController::class)->except(['show']);

    // Spinner Images
    Route::resource('spinner-images', SpinnerController::class)->except(['show']);
    Route::patch('spinner-images/{image}/toggle', [SpinnerController::class, 'toggle'])->name('spinner.toggle');

    // Scratch Cards
    Route::resource('scratch-cards', ScratchCardController::class)->except(['show']);

    // Who Questions
    Route::resource('who-questions', WhoQuestionController::class)->except(['show']);

    // Challenge Cards
    Route::resource('challenge-cards', ChallengeCardController::class)->except(['show']);

    // Know Me Questions
    Route::resource('know-me', KnowMeController::class)->except(['show']);

    // Subscriptions
    Route::get('subscriptions', [SubscriptionManageController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/{subscription}', [SubscriptionManageController::class, 'show'])->name('subscriptions.show');
    Route::get('subscriptions/{subscription}/receipt', [SubscriptionManageController::class, 'receipt'])->name('subscriptions.receipt');
    Route::patch('subscriptions/{subscription}/approve', [SubscriptionManageController::class, 'approve'])->name('subscriptions.approve');
    Route::patch('subscriptions/{subscription}/reject', [SubscriptionManageController::class, 'reject'])->name('subscriptions.reject');

    // Users
    Route::resource('users', UserController::class)->except(['create', 'store']);
    Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/logo', [SettingController::class, 'uploadLogo'])->name('settings.logo');
});

// Card Game API
Route::get('/api/cards/{levelSlug}', [\App\Http\Controllers\CardApiController::class, 'getCards'])->name('api.cards');

require __DIR__.'/auth.php';
