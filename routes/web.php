<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CatalogController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Admin\InvitationGuestController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderFormTemplateController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ServicePackageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\InvitationGuestController as CustomerInvitationGuestController;
use App\Http\Controllers\DanaWebhookController;
use App\Http\Controllers\GuestChatController;
use App\Http\Controllers\PublicInvitationAssetController;
use App\Http\Controllers\PublicInvitationController;
use App\Http\Controllers\PublicInvoiceController;
use App\Http\Controllers\PublicOrderController;
use App\Http\Controllers\PublicPackageController;
use App\Http\Controllers\PublicPaymentController;
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
Route::patch('/pemesanan/{order:uuid}', [PublicOrderController::class, 'update'])->middleware('throttle:8,1')->name('public.orders.update');
Route::get('/paket-harga', [PublicPackageController::class, 'index'])->name('public.packages.index');

Route::post('/payment/dana/generate', [PublicPaymentController::class, 'store'])->middleware('throttle:5,1')->name('public.payments.dana.store');
Route::get('/payment/{payment:uuid}', [PublicPaymentController::class, 'show'])->name('public.payments.show');
Route::get('/payment/{payment:uuid}/status', [PublicPaymentController::class, 'status'])->middleware('throttle:30,1')->name('public.payments.status');
Route::get('/invoice/{invoice}', [PublicInvoiceController::class, 'show'])->name('public.invoices.show');
Route::post('/v1.0/debit/notify', DanaWebhookController::class)->middleware('throttle:120,1')->name('dana.webhook');

Route::prefix('customer')->name('customer.')->group(function (): void {
    Route::get('/login', [CustomerAuthController::class, 'create'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');

    Route::middleware('customer.invitation')->group(function (): void {
        Route::get('/dashboard', CustomerDashboardController::class)->name('dashboard');
        Route::post('/logout', [CustomerAuthController::class, 'destroy'])->name('logout');
        Route::get('/tamu/create', [CustomerInvitationGuestController::class, 'create'])->name('guests.create');
        Route::post('/tamu', [CustomerInvitationGuestController::class, 'store'])->name('guests.store');
        Route::get('/tamu/{guest}/edit', [CustomerInvitationGuestController::class, 'edit'])->name('guests.edit');
        Route::put('/tamu/{guest}', [CustomerInvitationGuestController::class, 'update'])->name('guests.update');
        Route::delete('/tamu/{guest}', [CustomerInvitationGuestController::class, 'destroy'])->name('guests.destroy');
    });
});

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
    Route::post('/admin/undangan/{invitation}/regenerate-kode-customer', [InvitationController::class, 'regenerateCustomerAccessCode'])->name('admin.invitations.regenerate-customer-code');
    Route::scopeBindings()->group(function (): void {
        Route::get('/admin/undangan/{invitation}/tamu/create', [InvitationGuestController::class, 'create'])->name('admin.invitation-guests.create');
        Route::post('/admin/undangan/{invitation}/tamu', [InvitationGuestController::class, 'store'])->name('admin.invitation-guests.store');
        Route::get('/admin/undangan/{invitation}/tamu/{guest}/edit', [InvitationGuestController::class, 'edit'])->name('admin.invitation-guests.edit');
        Route::put('/admin/undangan/{invitation}/tamu/{guest}', [InvitationGuestController::class, 'update'])->name('admin.invitation-guests.update');
        Route::delete('/admin/undangan/{invitation}/tamu/{guest}', [InvitationGuestController::class, 'destroy'])->name('admin.invitation-guests.destroy');
    });
    Route::get('/admin/pembayaran/export', [PaymentController::class, 'export'])->name('admin.payments.export');
    Route::resource('/admin/pembayaran', PaymentController::class)
        ->parameters(['pembayaran' => 'payment'])
        ->names('admin.payments');
    Route::resource('/admin/metode-pembayaran', PaymentMethodController::class)
        ->parameters(['metode-pembayaran' => 'paymentMethod'])
        ->names('admin.payment-methods');
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
