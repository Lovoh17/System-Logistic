<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('landing');
})->name('landing');

// Logout general
Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');