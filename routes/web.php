<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\PabrikController;
use App\Http\Controllers\POJualController;

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





Route::get('/pabrik/po-jual', [POJualController::class, 'index'])->name('po-jual.index');

Route::get('/pabrik/po-jual', [POJualController::class, 'index'])->name('po-jual.index');
Route::post('/pabrik/po-jual', [POJualController::class, 'store'])->name('po-jual.store');
Route::post('/pabrik/po-jual/approve/{id}', [POJualController::class, 'approve'])->name('po-jual.approve');
Route::get('/pabrik/po-jual/edit/{id}', [POJualController::class, 'edit'])->name('po-jual.edit');
Route::post('/pabrik/po-jual/update/{id}', [POJualController::class, 'update'])->name('po-jual.update');

Route::get('/pabrik/po-jual/create', [POJualController::class, 'create'])->name('po-jual.create');

Route::middleware(['auth'])->prefix('pabrik')->group(function () {
    Route::get('/po-jual', [POJualController::class, 'index'])->name('po-jual.index');
    Route::get('/po-jual/create', [POJualController::class, 'create'])->name('po-jual.create');
    Route::post('/po-jual/store', [POJualController::class, 'store'])->name('po-jual.store');
    Route::get('/po-jual/{id}/edit', [POJualController::class, 'edit'])->name('po-jual.edit');
    Route::post('/po-jual/{id}/update', [POJualController::class, 'update'])->name('po-jual.update');
    Route::post('/po-jual/{id}/approve', [POJualController::class, 'approve'])->name('po-jual.approve');
    Route::post('/po-jual/{id}/cancel', [POJualController::class, 'cancel'])->name('po-jual.cancel');
});

Route::get('/pabrik/po-jual/{id}/approve', [POJualController::class, 'approve'])->name('po-jual.approve');

