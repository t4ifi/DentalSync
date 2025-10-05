<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/citas');
});

Route::get('/app', function () {
    return view('app');
});

Route::get('/citas', function () {
    return view('app');
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
