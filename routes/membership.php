<?php

use App\Http\Controllers\Membership\MemberController;
use App\Http\Controllers\Membership\SubscriptionController;
use App\Http\Controllers\Membership\FeeController;
use App\Http\Controllers\Membership\CardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Membership Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard Route
Route::get('/membership', function () {
    return view('membership.dashboard');
})->name('membership.dashboard')->middleware(['auth', 'verified']);

Route::middleware(['auth', 'verified'])->group(function () {
    // Member Routes
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
    Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
    
    // Member Family Routes
    Route::post('/members/{member}/family-member', [MemberController::class, 'addFamilyMember'])->name('members.addFamilyMember');
    Route::put('/members/{member}/deactivate', [MemberController::class, 'deactivate'])->name('members.deactivate');
    Route::put('/members/{member}/suspend', [MemberController::class, 'suspend'])->name('members.suspend');
    Route::put('/members/{member}/reactivate', [MemberController::class, 'reactivate'])->name('members.reactivate');
    
    // Member Card Routes
    Route::get('/members/{member}/print-card', [MemberController::class, 'printCard'])->name('members.printCard');
    Route::get('/members/{member}/download-card/{path}', [MemberController::class, 'downloadCard'])->name('members.downloadCard');
    
    // Member Statistics Routes
    Route::get('/members/statistics', [MemberController::class, 'statistics'])->name('members.statistics');
    Route::get('/members/expiring', [MemberController::class, 'expiring'])->name('members.expiring');

    // Subscription Routes
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/create', [SubscriptionController::class, 'create'])->name('subscriptions.create');
    Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::get('/subscriptions/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('subscriptions.edit');
    Route::put('/subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::delete('/subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
    
    // Subscription Management Routes
    Route::put('/subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');
    Route::put('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::put('/subscriptions/{subscription}/suspend', [SubscriptionController::class, 'suspend'])->name('subscriptions.suspend');
    Route::post('/subscriptions/process-auto-renewals', [SubscriptionController::class, 'processAutoRenewals'])->name('subscriptions.processAutoRenewals');
    
    // Subscription Statistics Routes
    Route::get('/subscriptions/statistics', [SubscriptionController::class, 'statistics'])->name('subscriptions.statistics');
    Route::get('/subscriptions/expiring', [SubscriptionController::class, 'expiring'])->name('subscriptions.expiring');

    // Fee Routes
    Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
    Route::get('/fees/create', [FeeController::class, 'create'])->name('fees.create');
    Route::post('/fees', [FeeController::class, 'store'])->name('fees.store');
    Route::get('/fees/{fee}', [FeeController::class, 'show'])->name('fees.show');
    Route::get('/fees/{fee}/edit', [FeeController::class, 'edit'])->name('fees.edit');
    Route::put('/fees/{fee}', [FeeController::class, 'update'])->name('fees.update');
    Route::delete('/fees/{fee}', [FeeController::class, 'destroy'])->name('fees.destroy');
    
    // Fee Management Routes
    Route::put('/fees/{fee}/process-payment', [FeeController::class, 'processPayment'])->name('fees.processPayment');
    Route::put('/fees/{fee}/waive', [FeeController::class, 'waive'])->name('fees.waive');
    Route::post('/fees/generate-overdue-fees', [FeeController::class, 'generateOverdueFees'])->name('fees.generateOverdueFees');
    
    // Fee Statistics Routes
    Route::get('/fees/statistics', [FeeController::class, 'statistics'])->name('fees.statistics');
    Route::get('/fees/{member}/summary', [FeeController::class, 'memberSummary'])->name('fees.memberSummary');

    // Card Printing Routes
    Route::get('/cards', [CardController::class, 'index'])->name('cards.index');
    Route::get('/cards/batch', [CardController::class, 'batch'])->name('cards.batch');
    Route::post('/cards/preview-member', [CardController::class, 'previewMember'])->name('cards.previewMember');
    Route::post('/cards/preview-family-member', [CardController::class, 'previewFamilyMember'])->name('cards.previewFamilyMember');
    Route::post('/cards/generate-member-card', [CardController::class, 'generateMemberCard'])->name('cards.generateMemberCard');
    Route::post('/cards/generate-batch-cards', [CardController::class, 'generateBatchCards'])->name('cards.generateBatchCards');
    Route::get('/cards/download/{path}', [CardController::class, 'download'])->name('cards.download');
    
    // Card Statistics Routes
    Route::get('/cards/statistics', [CardController::class, 'statistics'])->name('cards.statistics');
    Route::get('/cards/templates', [CardController::class, 'templates'])->name('cards.templates');
    Route::get('/cards/settings', [CardController::class, 'settings'])->name('cards.settings');
});