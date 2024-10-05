<?php

use App\Http\Controllers\VerifyEmailController;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->away("https://meinez.de/");
});

//email verification should be with api routes, but it's here only for testing purposes, because frontend didn't complete yet.
Route::get("/email/verify/{token}", [VerifyEmailController::class, "verifyEmail"])->name("auth.verify-email");
