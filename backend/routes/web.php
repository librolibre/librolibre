<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\LoginController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'index'])->name('login');
