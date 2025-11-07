<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
// routes/web.php
Route::get('/_health', function () {
    return response('Laravel OK', 200);
});
