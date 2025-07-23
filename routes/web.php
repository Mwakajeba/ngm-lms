<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpEmailController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/verify-sms', [AuthController::class, 'showVerificationForm'])->name('verify-sms');
Route::post('/verify-sms', [AuthController::class, 'verifySmsCode']);

Route::get('/forgotPassword', [AuthController::class, 'showForgotPasswordForm'])->name('forgotPassword');
Route::post('/forgotPassword', [AuthController::class, 'forgotPassword']);

Route::get('/verify-otp-password', [AuthController::class, 'showVerificationForm'])->name('verify-otp-password');
Route::post('/verify-otp-password', [AuthController::class, 'verifyPasswordCode']);

Route::get('/reset-password', [AuthController::class, 'showNewPasswordForm'])->name('new-password-form');
Route::post('/reset-password', [AuthController::class, 'storeNewPassword']);

Route::get('/resend-otp/{phone}', [AuthController::class, 'resendOtp'])->name('resend.otp');

Route::get('/request-email-otp', [OtpEmailController::class, 'showEmailForm'])->name('email-otp-form');
Route::post('/send-email-otp', [OtpEmailController::class, 'sendOtpEmail'])->name('email-otp-send');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');

////////////////////////////////////////////// PERMISSIONS MANAGEMENT /////////////////////////////////////////////
Route::middleware(['auth'])->group(function () {
    Route::get('roles', [RolePermissionController::class, 'index'])->name('roles.index');
    Route::get('roles/{role}/edit', [RolePermissionController::class, 'edit'])->name('roles.edit');
    Route::post('roles/{role}', [RolePermissionController::class, 'update'])->name('roles.update');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/users/{user}/roles', [UserRoleController::class, 'edit'])->name('users.roles.edit');
    Route::post('/users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
});
////////////////////////////////////////////// END PERMISSIONS MANAGEMENT //////////////////////////////////////////

////////////////////////////////////////////// USER MANAGEMENT /////////////////////////////////////////////////////

Route::resource('users', UserController::class)->middleware('auth');

////////////////////////////////////////////// END /////////////////////////////////////////////////////////////////

////////////////////////////////////////////// BRANCH MANAGEMENT ///////////////////////////////////////////////////

Route::resource('branches', BranchController::class)->middleware('auth');

////////////////////////////////////////////// END /////////////////////////////////////////////////////////////////


Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->middleware('auth');

