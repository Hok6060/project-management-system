<?php

use Illuminate\Support\Facades\Route;

use App\ProjectManagement\Http\Controllers\AttachmentController;
use App\ProjectManagement\Http\Controllers\CommentController; 
use App\ProjectManagement\Http\Controllers\DashboardController;
use App\ProjectManagement\Http\Controllers\ProfileController;
use App\ProjectManagement\Http\Controllers\ProjectController;
use App\ProjectManagement\Http\Controllers\TaskController;
use App\ProjectManagement\Http\Controllers\NotificationController;
use App\ProjectManagement\Http\Controllers\AdminController;

use App\LoanManagement\Http\Controllers\LoanTypeController;
use App\LoanManagement\Http\Controllers\LoanController;
use App\LoanManagement\Http\Controllers\CustomerController;
use App\LoanManagement\Http\Controllers\SettingController;
use App\LoanManagement\Http\Controllers\TransactionController;
use App\LoanManagement\Http\Controllers\LoanPayoffController;
use App\LoanManagement\Http\Controllers\LoanWaiverController;

Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Project Routes
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Task Routes
    Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Comment Routes
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::get('/comments/{comment}/edit', [CommentController::class, 'edit'])->name('comments.edit');
    Route::patch('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Attachment Route
    Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read'); 
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead'); 
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy'); 
    Route::post('/notifications/clear-read', [NotificationController::class, 'clearRead'])->name('notifications.clearRead');

    // Loan Routes
    Route::get('/loans/apply', [LoanController::class, 'create'])->name('loans.create');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes Groups
Route::middleware(['auth', \App\ProjectManagement\Http\Middleware\IsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard'); 

    // User Management Routes
    Route::get('/users', [AdminController::class, 'indexUsers'])->name('users.index');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');

    // Chart Routes
    Route::get('/chart/project-status', [AdminController::class, 'projectStatusChartData'])->name('chart.project.status');

    // Loan Type Management Routes
    Route::resource('loan-types', LoanTypeController::class);

    // Customer Management Routes
    Route::resource('customers', CustomerController::class);

    // System Settings Route
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/run-eod', [SettingController::class, 'runEod'])->name('settings.runEod');
    Route::get('/settings/eod-progress/{jobStatus}', [SettingController::class, 'eodProgress'])->name('settings.eodProgress');
    Route::get('/eod-status/{jobStatus}', [SettingController::class, 'getEodStatus'])->name('eod.status');

    // Waiver Approval Routes
    Route::get('/waivers', [LoanWaiverController::class, 'index'])->name('waivers.index');
    Route::patch('/waivers/{waiver}', [LoanWaiverController::class, 'update'])->name('waivers.update');
});

// --- Loan Management Routes (for Admins and Loan Officers) ---
Route::middleware(['auth', \App\LoanManagement\Http\Middleware\CanManageLoans::class])->prefix('loan-management')->name('loans.admin.')->group(function () {
    Route::get('/loans', [LoanController::class, 'index'])->name('index');
    Route::get('/loans/{loan}', [LoanController::class, 'show'])->name('show');
    Route::get('/loans/{loan}/edit', [LoanController::class, 'edit'])->name('edit');
    Route::patch('/loans/{loan}', [LoanController::class, 'update'])->name('update');
    Route::patch('/loans/{loan}/assign', [LoanController::class, 'assignOfficer'])->name('assign');
    Route::post('/loans/{loan}/cancel', [LoanController::class, 'cancel'])->name('cancel');
    Route::post('/loans/{loan}/pay', [TransactionController::class, 'store'])->name('payment.store');

    // Loan Payoff Routes
    Route::get('/payoffs', [LoanPayoffController::class, 'index'])->name('payoffs.index');
    Route::get('/payoffs/create', [LoanPayoffController::class, 'create'])->name('payoffs.create');
    Route::get('/payoffs/calculate', [LoanPayoffController::class, 'calculate'])->name('payoffs.calculate');
    Route::post('/payoffs/{loan}', [LoanPayoffController::class, 'store'])->name('payoffs.store');

    // Loan Waiver Routes
    Route::get('/loans/{loan}/waivers/create', [LoanWaiverController::class, 'create'])->name('waivers.create');
    Route::post('/loans/{loan}/waivers', [LoanWaiverController::class, 'store'])->name('waivers.store');
    Route::get('/loans/{loan}/waivers/calculate-max', [LoanWaiverController::class, 'calculateMaxWaiver'])->name('waivers.calculateMax');
});

require __DIR__.'/auth.php';
