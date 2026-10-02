<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CatalogController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Admin\InvitationGuestController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderFormTemplateController;
use App\Http\Controllers\Admin\ServicePackageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\GuestChatController;
use App\Http\Controllers\PublicInvitationAssetController;
use App\Http\Controllers\PublicInvitationController;
use App\Http\Controllers\PublicOrderController;
use App\Http\Controllers\PublicPackageController;
use App\Models\Invitation;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('live-chat')->name('guest.chat.')->group(function (): void {
    Route::post('/conversations', [GuestChatController::class, 'store'])->name('store');
    Route::get('/conversations/{guestToken}', [GuestChatController::class, 'show'])->name('show');
    Route::post('/conversations/{guestToken}/messages', [GuestChatController::class, 'message'])->name('message');
    Route::patch('/conversations/{guestToken}/close', [GuestChatController::class, 'close'])->name('close');
});

Route::get('/pemesanan/katalog', [PublicOrderController::class, 'catalogs'])->name('public.orders.catalogs');
Route::get('/pemesanan/katalog/{catalog}/form', [PublicOrderController::class, 'form'])->name('public.orders.form');
Route::post('/pemesanan', [PublicOrderController::class, 'store'])->middleware('throttle:8,1')->name('public.orders.store');
Route::get('/paket-harga', [PublicPackageController::class, 'index'])->name('public.packages.index');

Route::get('/invitation-assets/{templateSlug}/{path}', [PublicInvitationAssetController::class, 'show'])
    ->where([
        'templateSlug' => '[a-z0-9]+(?:-[a-z0-9]+)*',
        'path' => '.*',
    ])
    ->name('public.invitation-assets.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'store'])->name('admin.login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');
    Route::resource('/admin/pesanan', OrderController::class)
        ->parameters(['pesanan' => 'order'])
        ->names('admin.orders');
    Route::resource('/admin/katalog', CatalogController::class)
        ->parameters(['katalog' => 'catalog'])
        ->names('admin.catalog')
        ->except('show');
    Route::resource('/admin/pelanggan', CustomerController::class)
        ->parameters(['pelanggan' => 'customer'])
        ->names('admin.customers');
    Route::resource('/admin/form-pesanan', OrderFormTemplateController::class)
        ->parameters(['form-pesanan' => 'orderForm'])
        ->names('admin.order-forms')
        ->except('show');
    Route::resource('/admin/paket-harga', ServicePackageController::class)
        ->parameters(['paket-harga' => 'package'])
        ->names('admin.packages')
        ->except('show');
    Route::patch('/admin/template-undangan/{template}/publish', [TemplateController::class, 'togglePublication'])->name('admin.templates.publish');
    Route::get('/admin/template-undangan/{template}/preview', [TemplateController::class, 'preview'])->name('admin.templates.preview');
    Route::delete('/admin/template-undangan/{template}/files', [TemplateController::class, 'destroyFile'])->name('admin.templates.files.destroy');
    Route::resource('/admin/template-undangan', TemplateController::class)
        ->parameters(['template-undangan' => 'template'])
        ->names('admin.templates')
        ->except('show');
    Route::resource('/admin/undangan', InvitationController::class)
        ->parameters(['undangan' => 'invitation'])
        ->names('admin.invitations');
    Route::scopeBindings()->group(function (): void {
        Route::get('/admin/undangan/{invitation}/tamu/create', [InvitationGuestController::class, 'create'])->name('admin.invitation-guests.create');
        Route::post('/admin/undangan/{invitation}/tamu', [InvitationGuestController::class, 'store'])->name('admin.invitation-guests.store');
        Route::get('/admin/undangan/{invitation}/tamu/{guest}/edit', [InvitationGuestController::class, 'edit'])->name('admin.invitation-guests.edit');
        Route::put('/admin/undangan/{invitation}/tamu/{guest}', [InvitationGuestController::class, 'update'])->name('admin.invitation-guests.update');
        Route::delete('/admin/undangan/{invitation}/tamu/{guest}', [InvitationGuestController::class, 'destroy'])->name('admin.invitation-guests.destroy');
    });
    Route::view('/admin/pembayaran', 'admin.payments')->name('admin.payments.index');
    Route::get('/admin/live-chat', [ChatController::class, 'index'])->name('admin.chats.index');
    Route::get('/admin/live-chat/{conversation}', [ChatController::class, 'show'])->name('admin.chats.show');
    Route::post('/admin/live-chat/{conversation}/reply', [ChatController::class, 'reply'])->name('admin.chats.reply');
    Route::patch('/admin/live-chat/{conversation}/status', [ChatController::class, 'updateStatus'])->name('admin.chats.status');
    Route::get('/admin/pengaturan', [SettingController::class, 'edit'])->name('admin.settings.edit');
    Route::put('/admin/pengaturan', [SettingController::class, 'update'])->name('admin.settings.update');
    Route::post('/admin/logout', [AuthController::class, 'destroy'])->name('admin.logout');
});

$reservedSlugs = implode('|', array_map(preg_quote(...), Invitation::RESERVED_SLUGS));
$invitationSlugPattern = '(?!(?:'.$reservedSlugs.')(?:/|$))[a-z0-9]+(?:-[a-z0-9]+)*';
$guestSlugPattern = '[a-z0-9]+(?:-[a-z0-9]+)*';

Route::get('/{invitationSlug}/{guestSlug?}', [PublicInvitationController::class, 'show'])
    ->where(['invitationSlug' => $invitationSlugPattern, 'guestSlug' => $guestSlugPattern])
    ->name('public.invitations.show');
