<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{PageController, AuthController};

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', function() { if(!auth()->check()) { return redirect()->route('pages.home');} else { return redirect()->route('pages.dashboard');};})->name('root');
Route::get('/home', [PageController::class, 'home'])->name('pages.home');
Route::get('/dashboard', [PageController::class, 'dashboard'])->name('pages.dashboard')->middleware('auth');


Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::get('/signup', [AuthController::class, 'showSignup'])->name('auth.signup');
Route::post('/signup', [AuthController::class, 'signup'])->name('auth.signup');