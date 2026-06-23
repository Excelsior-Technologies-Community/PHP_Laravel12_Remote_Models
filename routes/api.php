<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CelebrityController;

Route::get(
    '/remote/models',
    [CelebrityController::class, 'remoteSync']
);

Route::get(
    '/celebrities',
    [CelebrityController::class, 'index']
);

Route::get(
    '/celebrities/stats',
    [CelebrityController::class, 'stats']
);

Route::get(
    '/sync-history',
    [CelebrityController::class, 'syncHistory']
);