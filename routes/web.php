<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\PabrikController;
use App\Http\Controllers\ItemsController;

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
        'owner' => redirect('/owner'),
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

Route::get('/owner', function () {
    if (Auth::check() && Auth::user()->role === 'owner') {
        return view('owner');
    }
    return redirect('/login');
})->name('owner');

// Developer route group
Route::middleware(['auth'])->group(function () {
    Route::get('/developer', [DeveloperController::class, 'dashboard'])->name('developer.dashboard');
    Route::post('/developer/register', [DeveloperController::class, 'registerUser'])->name('developer.registerUser');
    Route::delete('/developer/user/{id}', [DeveloperController::class, 'deleteUser'])->name('developer.deleteUser');
    Route::get('/developer/user/{id}/edit', [DeveloperController::class, 'editUser'])->name('developer.editUser');
    Route::put('/developer/user/{id}', [DeveloperController::class, 'updateUser'])->name('developer.updateUser');
});

Route::get('/pabrik/po-jual', function () {
    return view('pabrik.po-jual');
})->middleware('auth')->name('pabrik.po-jual');

Route::get('/pabrik/po-jual/create', function () {
    return view('pabrik.create-po-jual');
})->name('pabrik.po-jual.create');


// Rute PO Penjualan
Route::middleware(['auth'])->group(function () {
    Route::get('/pabrik/po-jual', [PabrikController::class, 'showPoJual'])->name('pabrik.po-jual');
    Route::get('/pabrik/po-jual/create', [PabrikController::class, 'createPoJual'])->name('pabrik.po-jual.create');
    Route::post('/pabrik/po-jual', [PabrikController::class, 'storePoJual'])->name('pabrik.po-jual.store');
    Route::get('/pabrik/po-jual/{id}', [PabrikController::class, 'showDetailPoJual'])->name('pabrik.po-jual.show');
    Route::get('/pabrik/po-jual/{id}/edit', [PabrikController::class, 'editPoJual'])->name('pabrik.po-jual.edit');
    Route::put('/pabrik/po-jual/{id}', [PabrikController::class, 'updatePoJual'])->name('pabrik.po-jual.update');
    Route::delete('/pabrik/po-jual/{id}/cancel', [PabrikController::class, 'cancelPoJual'])->name('pabrik.po-jual.cancel');
    Route::post('/pabrik/po-jual/{id}/approve', [PabrikController::class, 'approvePoJual'])->name('pabrik.po-jual.approve');
    Route::get('/pabrik/po-jual/{id}/surat-jalan', [PabrikController::class, 'generateSuratJalan'])->name('pabrik.po-jual.surat-jalan');
    Route::get('/pabrik/po-jual/{id}/invoice', [PabrikController::class, 'generateInvoice'])->name('pabrik.po-jual.invoice');
    Route::post('/pabrik/po-jual/{id}/cancel-approved', [PabrikController::class, 'cancelApprovedPoJual'])->name('pabrik.po-jual.cancel-approved');
    Route::get('/pabrik/po-jual/{id}/edit-approved', [PabrikController::class, 'editApprovedPoJual'])->name('pabrik.po-jual.edit-approved');
});

// Rute Items Pabrik
Route::middleware(['auth'])->group(function () {
    Route::get('/pabrik/item', [ItemsController::class, 'index'])->name('pabrik.item');
});

