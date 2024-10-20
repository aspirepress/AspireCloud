<?php

// NOTE FOR ASPIRECLOUD: API routes should go in api.php, not here

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
