<?php

use App\Http\Livewire\Dashboard;
use App\Http\Livewire\Projects\Index as ProjectsIndex;
use App\Http\Livewire\Tasks\Index as TasksIndex;
use App\Http\Livewire\Users\Index as UsersIndex;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Portal\AiController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    if ($user->hasRole('user') && ! $user->hasRole('admin')) {
        return redirect()->route('portal.home');
    }

    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/users', UsersIndex::class)->middleware('permission:users.manage')->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:users.manage')->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->middleware('permission:users.manage')->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:users.manage')->name('users.update');
    Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])->middleware('permission:users.manage')->name('users.deactivate');
    Route::patch('/users/{user}/activate', [UserController::class, 'activate'])->middleware('permission:users.manage')->name('users.activate');
    Route::get('/projects', ProjectsIndex::class)->middleware('permission:projects.view')->name('projects.index');
    Route::get('/tasks', TasksIndex::class)->middleware('permission:tasks.view')->name('tasks.index');
    Route::view('/inicio', 'pages.inicio')->middleware('permission:portal.home.view')->name('portal.home');
    Route::get('/ia', [AiController::class, 'index'])->middleware('permission:portal.ai.view')->name('portal.ai');
    Route::post('/ia/chats', [AiController::class, 'storeChat'])->middleware('permission:portal.ai.view')->name('portal.ai.chats.store');
    Route::get('/ia/chats/{chat}', [AiController::class, 'showChat'])->middleware('permission:portal.ai.view')->name('portal.ai.chats.show');
    Route::post('/ia/chats/{chat}/messages', [AiController::class, 'storeMessage'])->middleware('permission:portal.ai.view')->name('portal.ai.chats.messages.store');
    Route::get('/ia/exams/format', [AiController::class, 'downloadExamFormat'])->middleware('permission:portal.forms.view')->name('portal.ai.exams.format');
    Route::post('/ia/exams/manual', [AiController::class, 'storeManualExam'])->middleware('permission:portal.forms.view')->name('portal.ai.exams.manual.store');
    Route::patch('/ia/exams/{exam}/name', [AiController::class, 'updateManualExamName'])->middleware('permission:portal.forms.view')->name('portal.ai.exams.update-name');
    Route::get('/ia/exams/{exam}/manual', [AiController::class, 'showManualExamBuilder'])->middleware('permission:portal.forms.view')->name('portal.ai.exams.manual.show');
    Route::post('/ia/exams/{exam}/manual/questions', [AiController::class, 'storeManualExamQuestion'])->middleware('permission:portal.forms.view')->name('portal.ai.exams.manual.questions.store');
    Route::delete('/ia/exams/{exam}', [AiController::class, 'destroyExam'])->middleware('permission:portal.forms.view')->name('portal.ai.exams.destroy');
    Route::post('/ia/exams', [AiController::class, 'storeExam'])->middleware('permission:portal.forms.view')->name('portal.ai.exams.store');
    Route::post('/ia/exams/{exam}/practice/start', [AiController::class, 'startExamPractice'])->middleware('permission:portal.forms.view')->name('portal.ai.exams.practice.start');
    Route::get('/ia/exams/{exam}/practice/{attempt}/question/{position}', [AiController::class, 'showExamPracticeQuestion'])->middleware('permission:portal.forms.view')->whereNumber('position')->name('portal.ai.exams.practice.question');
    Route::post('/ia/exams/{exam}/practice/{attempt}/question/{position}', [AiController::class, 'submitExamPracticeQuestion'])->middleware('permission:portal.forms.view')->whereNumber('position')->name('portal.ai.exams.practice.answer');
    Route::get('/ia/exams/{exam}/practice/{attempt}/result', [AiController::class, 'showExamPracticeResult'])->middleware('permission:portal.forms.view')->name('portal.ai.exams.practice.result');
    Route::get('/examenes', [AiController::class, 'examsIndex'])->middleware('permission:portal.forms.view')->name('portal.forms');
    Route::redirect('/formularios', '/examenes');
    Route::view('/salas', 'pages.salas')->middleware('permission:portal.rooms.view')->name('portal.rooms');
    Route::view('/horarios', 'pages.horarios')->middleware('permission:portal.schedules.view')->name('portal.schedules');
    Route::view('/estadisticas', 'pages.estadisticas')->middleware('permission:portal.stats.view')->name('portal.stats');
    Route::view('/ayuda', 'pages.ayuda')->middleware('permission:portal.help.view')->name('portal.help');
    Route::view('/support', 'pages.support')->name('support');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
