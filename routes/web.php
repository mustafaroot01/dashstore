<?php

use App\Http\Controllers\Admin;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\LogAdminActivity;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Admin Panel
|--------------------------------------------------------------------------
*/

// ── Auth (Guest only) ─────────────────────────────────────
Route::prefix('panel')->name('admin.')->group(function () {

    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login'])->name('login.post');
    });

    Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');

    // ── Protected Routes ──────────────────────────────────
    Route::middleware([AdminAuth::class, LogAdminActivity::class])->group(function () {

        // Dashboard (Accessible by all logged in admins)
        Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Orders
        Route::middleware('admin.can:manage_orders')->group(function () {
            Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
            Route::patch('orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');
        });

        // Products & Categories
        Route::middleware('admin.can:manage_products')->group(function () {
            // Products
            Route::get('products', [Admin\ProductController::class, 'index'])->name('products.index');
            Route::get('products/create', [Admin\ProductController::class, 'create'])->name('products.create');
            Route::post('products', [Admin\ProductController::class, 'store'])->name('products.store');
            Route::get('products/{product}/edit', [Admin\ProductController::class, 'edit'])->name('products.edit');
            Route::match(['POST','PUT'], 'products/{product}', [Admin\ProductController::class, 'update'])->name('products.update');
            Route::delete('products/{product}', [Admin\ProductController::class, 'destroy'])->name('products.destroy');
            Route::patch('products/{product}/toggle-availability', [Admin\ProductController::class, 'toggleAvailability'])->name('products.toggle-availability');
            Route::patch('products/{product}/toggle-active', [Admin\ProductController::class, 'toggleActive'])->name('products.toggle-active');

            // Categories
            Route::get('categories', [Admin\CategoryController::class, 'index'])->name('categories.index');
            Route::post('categories', [Admin\CategoryController::class, 'store'])->name('categories.store');
            Route::match(['POST','PUT'], 'categories/{category}', [Admin\CategoryController::class, 'update'])->name('categories.update');
            Route::delete('categories/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');
            Route::patch('categories/{category}/toggle-active', [Admin\CategoryController::class, 'toggleActive'])->name('categories.toggle-active');
        });

        // Settings & Districts
        Route::middleware('admin.can:manage_settings')->group(function () {
            Route::get('districts', [Admin\DistrictController::class, 'index'])->name('districts.index');
            Route::post('districts', [Admin\DistrictController::class, 'store'])->name('districts.store');
            Route::delete('districts/{district}', [Admin\DistrictController::class, 'destroy'])->name('districts.destroy');
            Route::patch('districts/{district}/toggle-active', [Admin\DistrictController::class, 'toggleActive'])->name('districts.toggle-active');

            Route::get('settings', [Admin\SettingController::class, 'index'])->name('settings.index');
            Route::post('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        });

        // Users
        Route::middleware('admin.can:manage_users')->group(function () {
            Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
            Route::patch('users/{user}/password', [Admin\UserController::class, 'updatePassword'])->name('users.password');
            Route::patch('users/{user}/toggle-active', [Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');
            Route::delete('users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
        });

        // Content (Sliders & Privacy)
        Route::middleware('admin.can:manage_content')->group(function () {
            Route::get('sliders', [Admin\SliderController::class, 'index'])->name('sliders.index');
            Route::post('sliders', [Admin\SliderController::class, 'store'])->name('sliders.store');
            Route::match(['POST','PUT'], 'sliders/{slider}', [Admin\SliderController::class, 'update'])->name('sliders.update');
            Route::delete('sliders/{slider}', [Admin\SliderController::class, 'destroy'])->name('sliders.destroy');
            Route::patch('sliders/{slider}/toggle-active', [Admin\SliderController::class, 'toggleActive'])->name('sliders.toggle-active');
            Route::post('sliders/reorder', [Admin\SliderController::class, 'reorder'])->name('sliders.reorder');

            Route::get('privacy-policy', [Admin\PrivacyPolicyController::class, 'index'])->name('privacy-policy.index');
            Route::post('privacy-policy', [Admin\PrivacyPolicyController::class, 'update'])->name('privacy-policy.update');
        });

        // Financial (Coupons)
        Route::middleware('admin.can:manage_financial')->group(function () {
            Route::get('coupons', [Admin\CouponController::class, 'index'])->name('coupons.index');
            Route::post('coupons', [Admin\CouponController::class, 'store'])->name('coupons.store');
            Route::patch('coupons/{coupon}', [Admin\CouponController::class, 'update'])->name('coupons.update');
            Route::delete('coupons/{coupon}', [Admin\CouponController::class, 'destroy'])->name('coupons.destroy');
            Route::patch('coupons/{coupon}/toggle-active', [Admin\CouponController::class, 'toggleActive'])->name('coupons.toggle-active');
        });

        // Admin Roles & Supervisor management
        Route::middleware('admin.can:manage_admins_roles')->group(function () {
            Route::get('roles', [Admin\RoleController::class, 'index'])->name('roles.index');
            Route::post('roles', [Admin\RoleController::class, 'store'])->name('roles.store');
            Route::put('roles/{role}', [Admin\RoleController::class, 'update'])->name('roles.update');
            Route::delete('roles/{role}', [Admin\RoleController::class, 'destroy'])->name('roles.destroy');

            Route::get('admins', [Admin\AdminUserController::class, 'index'])->name('admins.index');
            Route::post('admins', [Admin\AdminUserController::class, 'store'])->name('admins.store');
            Route::put('admins/{admin}', [Admin\AdminUserController::class, 'update'])->name('admins.update');
            Route::delete('admins/{admin}', [Admin\AdminUserController::class, 'destroy'])->name('admins.destroy');
        });

        // Logs & Docs (Accessible by Super Admin natively or manage_settings)
        Route::middleware('admin.can:manage_settings')->group(function () {
            Route::get('activity-log', [Admin\ActivityLogController::class, 'index'])->name('activity-log.index');
            Route::get('error-log', [Admin\ErrorLogController::class, 'index'])->name('error-log.index');
            Route::delete('error-log/clear', [Admin\ErrorLogController::class, 'clear'])->name('error-log.clear');
            Route::get('api-docs', [Admin\ApiDocController::class, 'index'])->name('api-docs.index');
        });
    });
});

// Redirect root to panel
Route::redirect('/', '/panel/dashboard');
