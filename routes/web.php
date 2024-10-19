<?php

use App\Http\Controllers\CatchAllController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
