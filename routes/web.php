<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{PageController, AuthController, ProjectController, SchemaController};

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', function() { if(!auth()->check()) { return redirect()->route('pages.home');} else { return redirect()->route('pages.dashboard');};})->name('root');
Route::get('/home', [PageController::class, 'home'])->name('pages.home');
Route::get('/dashboard', [PageController::class, 'dashboard'])->name('pages.dashboard')->middleware('auth');

Route::middleware('guest')->group(function () {
Route::get('/auth', function () {
    return redirect()->route('auth.login');
})->name('login');
Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/signup', [AuthController::class, 'showSignup'])->name('auth.signup');
Route::post('/signup', [AuthController::class, 'signup']);
});

Route::middleware('auth')->group( function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('/logout', [AuthController::class, 'logout']);

    
    Route::name('projects.')->group(function () {
        Route::get('/projects', [ProjectController::class, 'index'])->name('index');
        Route::prefix('p')->group(function () {
            Route::get('/', function () { return redirect()->route('projects.create');});
            Route::get('/create', [ProjectController::class, 'create'])->name('create');
            Route::post('/', [ProjectController::class, 'store'])->name('store');
            Route::get('/{project_slug}/edit', [ProjectController::class, 'edit'])->name('edit');
            Route::get('/{project_slug}/show', [ProjectController::class, 'show'])->name('show');
            Route::put('/{project_slug}', [ProjectController::class, 'update'])->name('update');
            Route::delete('/{project_slug}', [ProjectController::class, 'destroy'])->name('destroy');
        });
    });

    Route::name('schema.')->group( function () {
        Route::get('/{project_slug}', [SchemaController::class, 'showProject'])->name('project');
        Route::get('/{project_slug}/{database_name}', [SchemaController::class, 'showDatabase'])->name('database');
        Route::get('/{project_slug}/{database_name}/{table_name}', [SchemaController::class, 'showTable'])->name('table');
        Route::get('/{project_slug}/{database_name}/{table_name}/{column_name}', [SchemaController::class, 'showColumn'])->name('column');
    });
});