<?php

use App\Http\Controllers\VerifyEmailController;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->away("https://meinez.de/");
});

Route::get("/email/verify/{token}", [VerifyEmailController::class, "verifyEmail"])->name("auth.verify-email");