<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeveloperController;

Route::get('/', function () {
    return redirect('/login');
});

// Login Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Middleware Manual dalam Route
Route::get('/redirect', function (Request $request) {
    if (!Auth::check()) {
        return redirect('/login');
    }

    $role = Auth::user()->role;

    return match ($role) {
        'developer' => redirect('/developer'),
        'kantor' => redirect('/kantor'),
        'pabrik' => redirect('/pabrik'),
        default => redirect('/login')
    };
});

// Route untuk masing-masing role
Route::get('/developer', function () {
    if (Auth::check() && Auth::user()->role === 'developer') {
        return view('developer');
    }
    return redirect('/login');
})->name('developer');

Route::get('/kantor', function () {
    if (Auth::check() && Auth::user()->role === 'kantor') {
        return view('kantor');
    }
    return redirect('/login');
})->name('kantor');

Route::get('/pabrik', function () {
    if (Auth::check() && Auth::user()->role === 'pabrik') {
        return view('pabrik');
    }
    return redirect('/login');
})->name('pabrik');





Route::middleware(['auth'])->group(function () {
    Route::get('/developer', [DeveloperController::class, 'dashboard'])->name('developer.dashboard');
    Route::post('/developer/register', [DeveloperController::class, 'registerUser'])->name('developer.registerUser');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/developer', [DeveloperController::class, 'dashboard'])->name('developer.dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::delete('/developer/user/{id}', [DeveloperController::class, 'deleteUser'])->name('developer.deleteUser');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/developer/user/{id}/edit', [DeveloperController::class, 'editUser'])->name('developer.editUser');
    Route::put('/developer/user/{id}', [DeveloperController::class, 'updateUser'])->name('developer.updateUser');
});

