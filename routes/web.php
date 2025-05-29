<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\PabrikController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\PoBeliController;

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
    Route::get('/pabrik/po-jual/{id}/print-detail', [PabrikController::class, 'printPoJualDetail'])->name('pabrik.po-jual.print-detail');
    Route::post('/pabrik/po-jual/{id}/cancel-approved', [PabrikController::class, 'cancelApprovedPoJual'])->name('pabrik.po-jual.cancel-approved');
    Route::get('/pabrik/po-jual/{id}/edit-approved', [PabrikController::class, 'editApprovedPoJual'])->name('pabrik.po-jual.edit-approved');
    Route::post('/pabrik/po-jual/{id}/complete', [PabrikController::class, 'completePoJual'])->name('pabrik.po-jual.complete'); // New route for completing PO
    Route::get('/po-jual/{id}/return', [PabrikController::class, 'showReturnPoJual'])->name('pabrik.po-jual.return');
    Route::post('/po-jual/{id}/process-return', [PabrikController::class, 'processReturnPoJual'])->name('pabrik.po-jual.process-return');
});

// Rute Items Pabrik
Route::middleware(['auth'])->group(function () {
    Route::get('/pabrik/item', [ItemsController::class, 'index'])->name('pabrik.item');

    // Rute Pelanggan Pabrik
    Route::get('/pabrik/pelanggan', [ClientsController::class, 'index'])->name('pabrik.pelanggan');
    Route::post('/pabrik/pelanggan', [ClientsController::class, 'store'])->name('pabrik.pelanggan.store');
    Route::get('/pabrik/pelanggan/{id}/edit', [ClientsController::class, 'edit'])->name('pabrik.pelanggan.edit');
    Route::put('/pabrik/pelanggan/{id}', [ClientsController::class, 'update'])->name('pabrik.pelanggan.update');
    Route::delete('/pabrik/pelanggan/{id}', [ClientsController::class, 'destroy'])->name('pabrik.pelanggan.destroy');
});

// Rute PO Pembelian
Route::middleware(['auth'])->group(function () {
    Route::get('/pabrik/po-beli', [PoBeliController::class, 'showPoBeli'])->name('pabrik.po-beli');
    Route::get('/pabrik/po-beli/create', [PoBeliController::class, 'createPoBeli'])->name('pabrik.po-beli.create');
    Route::post('/pabrik/po-beli', [PoBeliController::class, 'storePoBeli'])->name('pabrik.po-beli.store');
    Route::get('/pabrik/po-beli/{id}', [PoBeliController::class, 'showDetailPoBeli'])->name('pabrik.po-beli.show');
    
    // Routes for editing draft PO
    Route::get('/pabrik/po-beli/{id}/edit', [PoBeliController::class, 'editPoBeli'])->name('pabrik.po-beli.edit');
    Route::put('/pabrik/po-beli/{id}', [PoBeliController::class, 'updatePoBeli'])->name('pabrik.po-beli.update');
    
    // Routes for editing approved PO
    Route::get('/pabrik/po-beli/{id}/edit-approved', [PoBeliController::class, 'editApprovedPoBeli'])->name('pabrik.po-beli.edit-approved');
    Route::put('/pabrik/po-beli/{id}/update-approved', [PoBeliController::class, 'updateApprovedPoBeli'])->name('pabrik.po-beli.update-approved');
    
    // Other PO beli routes
    Route::delete('/pabrik/po-beli/{id}/cancel', [PoBeliController::class, 'cancelPoBeli'])->name('pabrik.po-beli.cancel');
    Route::post('/pabrik/po-beli/{id}/approve', [PoBeliController::class, 'approvePoBeli'])->name('pabrik.po-beli.approve');
    Route::get('/pabrik/po-beli/{id}/print-detail', [PoBeliController::class, 'printPoBeliDetail'])->name('pabrik.po-beli.print-detail');
    Route::post('/pabrik/po-beli/{id}/cancel-approved', [PoBeliController::class, 'cancelApprovedPoBeli'])->name('pabrik.po-beli.cancel-approved');
    Route::post('/pabrik/po-beli/{id}/complete', [PoBeliController::class, 'completePoBeli'])->name('pabrik.po-beli.complete');
});


