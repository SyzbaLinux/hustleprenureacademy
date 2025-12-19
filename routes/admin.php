<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\EnrollmentController;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Events & Courses Management
    Route::resource('events', EventController::class);

    // Categories Management
    Route::resource('categories', CategoryController::class);

    // Instructors Management
    Route::resource('instructors', InstructorController::class);

    // User Management
    Route::resource('users', UserController::class);

    // Enrollments & Payments
    Route::resource('enrollments', EnrollmentController::class)->except(['edit']);
    Route::resource('payments', PaymentController::class)->only(['index', 'show']);
});
