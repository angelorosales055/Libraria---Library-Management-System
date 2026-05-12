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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PayMongoController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\PortalRenewController;

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
    Route::get('/portal/fines', [UserPortalController::class, 'fines'])->name('portal.fines');
    Route::post('/portal/pay-fine/{txn}', [UserPortalController::class, 'payFine'])->name('portal.pay-fine');
    Route::post('/portal/borrow/{book}', [UserPortalController::class, 'borrow'])->name('portal.borrow');
    Route::patch('/portal/return/{transaction}', [UserPortalController::class, 'return'])->name('portal.return');
    Route::get('/portal/renew/{transaction}', [PortalRenewController::class, 'showForm'])->name('portal.renew.form');
    Route::post('/portal/renew-request/{transaction}', [PortalRenewController::class, 'request'])->name('portal.renew.request');
    Route::post('/portal/resubmit-request/{transaction}', [UserPortalController::class, 'resubmitRequest'])->name('portal.resubmit.request');
    Route::patch('/portal/renew/{transaction}', [UserPortalController::class, 'renew'])->name('portal.renew');

    // Profile & password
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::put('/change-password', [AuthController::class, 'changePassword'])->name('password.update');

    // Receipt (accessible by staff and the transaction owner)
    Route::get('/receipt/{txn}', [CirculationController::class, 'receipt'])->name('receipt.show');
    Route::get('/receipt/{txn}/download', [CirculationController::class, 'downloadReceipt'])->name('receipt.download');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // PayMongo Payment
    Route::get('/paymongo/create/{txn}', [PayMongoController::class, 'createPayment'])->name('paymongo.create');
    Route::get('/paymongo/checkout/{txn}', [PayMongoController::class, 'checkout'])->name('paymongo.checkout');
    Route::post('/paymongo/success/{txn}', [PayMongoController::class, 'success'])->name('paymongo.success');
    Route::get('/paymongo/failed/{txn}', [PayMongoController::class, 'failed'])->name('paymongo.failed');

    // Staff-only routes (admin and librarian)
    Route::middleware('role:admin,librarian')->group(function () {
        // Books
        Route::resource('books', BookController::class);

        // Circulation
        Route::get('/circulation',              [CirculationController::class, 'index'])->name('circulation.index');
        Route::get('/circulation/create',       [CirculationController::class, 'create'])->name('circulation.create');
        Route::post('/circulation/checkout',    [CirculationController::class, 'checkout'])->name('circulation.checkout');
        Route::get('/circulation/{txn}/fine-amount', [CirculationFineAmountController::class, '__invoke'])->name('circulation.fine-amount'); 
        Route::patch('/circulation/{txn}/return', [CirculationController::class, 'approveReturn'])->name('circulation.return');
        Route::patch('/circulation/{txn}/fine',   [CirculationController::class, 'collectFine'])->name('circulation.fine');
        Route::patch('/circulation/{txn}/notify-fine', [CirculationController::class, 'notifyFine'])->name('circulation.notify-fine');
        Route::patch('/circulation/{txn}/renew',  [CirculationController::class, 'renew'])->name('circulation.renew');
        Route::patch('/circulation/{txn}/damage-return', [CirculationController::class, 'damageReturn'])->name('circulation.damage-return');
        Route::match(['POST', 'PATCH'], '/circulation/{txn}/approve', [CirculationController::class, 'approve'])->name('circulation.approve');
        Route::post('/circulation/{txn}/reject', [CirculationController::class, 'reject'])->name('circulation.reject');
        Route::match(['POST', 'PATCH'], '/circulation/{txn}/approve-renew', [CirculationController::class, 'approveRenew'])->name('circulation.approve.renew');

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
        Route::get('/payments',    [ReportController::class, 'payments'])->name('payments');
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

    // Activity Log (admin + librarian)
    Route::middleware('role:admin')->prefix('activity-log')->name('activity-log.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
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
    Route::get('/transactions/{id}', [ApiController::class, 'transactionDetails']);
});
