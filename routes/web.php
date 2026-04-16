<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CirculationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\UserPortalController;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Protected App Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User portal pages for normal users
    Route::get('/portal', [UserPortalController::class, 'home'])->name('portal.home');
    Route::get('/portal/collection', [UserPortalController::class, 'collection'])->name('portal.collection');
    Route::get('/portal/transactions', [UserPortalController::class, 'transactions'])->name('portal.transactions');
    Route::post('/portal/borrow/{book}', [UserPortalController::class, 'borrow'])->name('portal.borrow');
    Route::patch('/portal/return/{transaction}', [UserPortalController::class, 'return'])->name('portal.return');
    Route::patch('/portal/renew/{transaction}', [UserPortalController::class, 'renew'])->name('portal.renew');

    // Staff-only routes (admin and librarian)
    Route::middleware('role:admin,librarian')->group(function () {
        // Books
        Route::resource('books', BookController::class);

        // Circulation
        Route::get('/circulation',              [CirculationController::class, 'index'])->name('circulation.index');
        Route::get('/circulation/create',       [CirculationController::class, 'create'])->name('circulation.create');
        Route::post('/circulation/checkout',    [CirculationController::class, 'checkout'])->name('circulation.checkout');
        Route::patch('/circulation/{txn}/return', [CirculationController::class, 'processReturn'])->name('circulation.return');
        Route::patch('/circulation/{txn}/fine',   [CirculationController::class, 'collectFine'])->name('circulation.fine');
        Route::patch('/circulation/{txn}/renew',  [CirculationController::class, 'renew'])->name('circulation.renew');

        // Members
        Route::resource('members', MemberController::class);
    });

    // Reports (admin + librarian)
    Route::middleware('role:admin,librarian')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/',            [ReportController::class, 'index'])->name('index');
        Route::get('/circulation', [ReportController::class, 'circulation'])->name('circulation');
        Route::get('/inventory',   [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/overdue',     [ReportController::class, 'overdue'])->name('overdue');
        Route::get('/fines',       [ReportController::class, 'fines'])->name('fines');
        Route::get('/members',     [ReportController::class, 'members'])->name('members');
        Route::get('/export',      [ReportController::class, 'export'])->name('export');
    });

    // User Management (admin only)
    Route::middleware('role:admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/',          [UserController::class, 'index'])->name('index');
        Route::post('/',         [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit',  [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}',    [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Internal API (AJAX)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/members/lookup', [ApiController::class, 'memberLookup']);
    Route::get('/books/lookup',   [ApiController::class, 'bookLookup']);
});
