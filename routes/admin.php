<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Events & Courses Management
    Route::resource('events', EventController::class);
    Route::post('events/{event}/schedules', [EventController::class, 'storeSchedule'])->name('events.schedules.store');
    Route::put('events/{event}/schedules/{schedule}', [EventController::class, 'updateSchedule'])->name('events.schedules.update');
    Route::delete('events/{event}/schedules/{schedule}', [EventController::class, 'deleteSchedule'])->name('events.schedules.delete');

    // Categories Management
    Route::post('categories/quick', [CategoryController::class, 'storeQuick'])->name('categories.quick');
    Route::resource('categories', CategoryController::class);

    // Instructors Management
    Route::resource('instructors', InstructorController::class);

    // User Management
    Route::resource('users', UserController::class);

    // Enrollments & Payments
    Route::resource('enrollments', EnrollmentController::class)->except(['edit']);
    Route::resource('payments', PaymentController::class)->only(['index', 'show']);
    Route::post('payments/{payment}/mark-paid', [PaymentController::class, 'markPaid'])->name('payments.mark-paid');
    Route::post('payments/{payment}/create-enrollment', [PaymentController::class, 'createEnrollment'])->name('payments.create-enrollment');
});
