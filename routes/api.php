<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

Route::get('/messages/sent', [MessageController::class, 'sent']);
