<?php

use App\Http\Controllers\BkashController;
use App\Http\Controllers\OrderInvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShortUrlController;
use App\Http\Controllers\SocialAuthController;
use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\CartPage;
use App\Livewire\CategoryPage;
use App\Livewire\CheckoutPage;
use App\Livewire\HomePage;
use App\Livewire\MyOrdersPage;
use App\Livewire\OrderSuccessPage;
use App\Livewire\PageShow;
use App\Livewire\ProductDetailPage;
use App\Livewire\ProfilePage;
use App\Livewire\TrackOrderPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public Storefront Routes
Route::get('/', HomePage::class)->name('home');
Route::get('/category/{slug}', CategoryPage::class)->name('category.show');

// Legal Pages
Route::view('/privacy-policy', 'pages.privacy-policy')->name('privacy');
Route::view('/terms-and-conditions', 'pages.terms-and-conditions')->name('terms');
Route::view('/refund-policy', 'pages.refund-policy')->name('refund');
Route::get('/product/{slug}', ProductDetailPage::class)->name('product.detail');
Route::get('/cart', CartPage::class)->name('cart');
Route::get('/checkout', CheckoutPage::class)->name('checkout');
Route::get('/order-success/{orderNumber}', OrderSuccessPage::class)->name('order.success');
Route::get('/track-order/{orderNumber?}', TrackOrderPage::class)->name('order.track');
Route::get('/s/{code}', [ShortUrlController::class, 'redirect'])->name('short.url');
Route::get('/order/{orderNumber}/download-invoice', [OrderInvoiceController::class, 'download'])->name('order.invoice.download');

// Customer Authentication Routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', LoginPage::class)->name('login');
    Route::get('/register', RegisterPage::class)->name('register');
});

// Social OAuth Authentication Routes (Google & Facebook)
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('home')->with('success', 'You have been signed out.');
})->name('logout');

// Authenticated Customer Profile & Orders
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', ProfilePage::class)->name('profile');
    Route::get('/my-orders', MyOrdersPage::class)->name('my-orders');
});

// SSLCommerz Gateway Callbacks & IPN Webhook Routes
Route::post('/payment/sslcommerz/success', [PaymentController::class, 'success'])->name('payment.sslcommerz.success');
Route::post('/payment/sslcommerz/fail', [PaymentController::class, 'fail'])->name('payment.sslcommerz.fail');
Route::post('/payment/sslcommerz/cancel', [PaymentController::class, 'cancel'])->name('payment.sslcommerz.cancel');
Route::post('/payment/sslcommerz/ipn', [PaymentController::class, 'ipn'])->name('payment.sslcommerz.ipn');
Route::get('/payment/mock-sandbox/{orderNumber}', [PaymentController::class, 'mockSandbox'])->name('payment.mock');

// bKash Tokenized Checkout Callback & Sandbox Routes
Route::match(['get', 'post'], '/payment/bkash/callback', [BkashController::class, 'callback'])->name('payment.bkash.callback');
Route::get('/payment/bkash/mock-sandbox/{orderNumber}', [BkashController::class, 'mockSandbox'])->name('payment.bkash.mock');

// Admin Cache Clear Route
Route::middleware(['auth'])->get('/admin/system/clear-cache', function () {
    /** @var \App\Models\User $user */
    $user = auth()->user();
    if (!$user || (!$user->isAdmin() && !$user->isManager())) {
        abort(403, 'Unauthorized');
    }
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    
    // Attempt to notify in Filament
    if (class_exists(\Filament\Notifications\Notification::class)) {
        \Filament\Notifications\Notification::make()
            ->title('System Cache Cleared Successfully')
            ->success()
            ->send();
    }
    return redirect()->back();
})->name('admin.clear-cache');

// Admin Backup Download Route
Route::middleware(['auth'])->get('/admin/backups/download/{filename}', function (string $filename) {
    /** @var \App\Models\User $user */
    $user = auth()->user();
    if (!$user || (!$user->isAdmin() && !$user->isManager())) {
        abort(403, 'Unauthorized access to store backups.');
    }
    return \App\Services\BackupService::downloadBackup($filename);
})->name('admin.backups.download');

// Legacy /page/{slug} redirect to direct /{slug}
Route::get('/page/{slug}', function (string $slug) {
    return redirect()->to('/' . $slug, 301);
});

// Top-Level Custom Slug Pages (e.g. /about-us, /authenticity-guarantee)
Route::get('/{slug}', PageShow::class)->name('page.show');