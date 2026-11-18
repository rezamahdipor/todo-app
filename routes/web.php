<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome-page')->name('home');

// فقط مهمان‌ها (وارد نشده‌ها)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// فقط کاربرهای وارد شده
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // داشبورد، حساب کاربری و سازنده
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/creator', 'creator')->name('creator');
    Route::get('/account', [AccountController::class, 'show'])->name('account');
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');

    Route::get('/stats', [StatsController::class, 'index'])->name('stats');

    // کانبان
    Route::get('/board', [BoardController::class, 'index'])->name('board');
    Route::post('/board/move', [BoardController::class, 'move'])->name('board.move');

    // عادت‌ها
    Route::get('/habits', [HabitController::class, 'index'])->name('habits');
    Route::post('/habits', [HabitController::class, 'store'])->name('habits.store');
    Route::post('/habits/{habit}/toggle', [HabitController::class, 'toggle'])->name('habits.toggle');
    Route::delete('/habits/{habit}', [HabitController::class, 'destroy'])->name('habits.destroy');

    // باید قبل از resource بیاد
    Route::post('/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');

    Route::resource('tasks', TaskController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    // زیرتسک‌ها
    Route::post('/tasks/{task}/subtasks', [SubtaskController::class, 'store'])->name('subtasks.store');
    Route::put('/subtasks/{subtask}', [SubtaskController::class, 'update'])->name('subtasks.update');
    Route::delete('/subtasks/{subtask}', [SubtaskController::class, 'destroy'])->name('subtasks.destroy');

    Route::resource('categories', CategoryController::class)
        ->only(['store', 'destroy']);
});