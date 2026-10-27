<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\BadgeController as AdminBadgeController;
use App\Http\Controllers\Admin\ChallengeController as AdminChallengeController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\ChallengeParticipationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Pendukung: pencatatan aktivitas minimal (versi lengkap = PBI-04).
    Route::resource('aktivitas', ActivityController::class)
        ->only(['index', 'store', 'destroy'])
        ->parameters(['aktivitas' => 'activity'])
        ->names('activities');

    // PBI-13 Manajemen Lencana: galeri lencana pengguna.
    Route::get('/lencana', [BadgeController::class, 'index'])->name('badges.index');

    // PBI-14 Manajemen Tantangan: daftar & detail tantangan.
    Route::get('/tantangan', [ChallengeController::class, 'index'])->name('challenges.index');
    Route::get('/tantangan/{challenge}', [ChallengeController::class, 'show'])->name('challenges.show');

    // PBI-15 Keikutsertaan Tantangan.
    Route::get('/tantangan-saya', [ChallengeParticipationController::class, 'index'])->name('participations.index');
    Route::post('/tantangan-saya/{challenge}', [ChallengeParticipationController::class, 'store'])->name('participations.store');
    Route::patch('/tantangan-saya/{challenge}', [ChallengeParticipationController::class, 'update'])->name('participations.update');
    Route::delete('/tantangan-saya/{challenge}', [ChallengeParticipationController::class, 'destroy'])->name('participations.destroy');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // PBI-13 Manajemen Lencana (CRUD administrator).
        Route::resource('lencana', AdminBadgeController::class)
            ->except('show')
            ->parameters(['lencana' => 'badge'])
            ->names('badges');

        // PBI-14 Manajemen Tantangan (CRUD administrator).
        Route::resource('tantangan', AdminChallengeController::class)
            ->parameters(['tantangan' => 'challenge'])
            ->names('challenges');
    });
});

require __DIR__.'/auth.php';
