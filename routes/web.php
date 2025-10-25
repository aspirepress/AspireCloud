<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/', 'home');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        // Temporary redirect to the profile page until we have a dashboard to show
        return redirect()->route('profile.show');
    })->name('dashboard');
});

require __DIR__ . '/inc/admin-web.php';
