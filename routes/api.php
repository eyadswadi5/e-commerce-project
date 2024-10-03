<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(["namespace" => "App\Http\Controllers", "middleware" => "api"], function ($router) {
    Route::group(["prefix" => "auth"], function () {
        Route::post("register", "api\AuthController@register")->name("auth.register");
        Route::post("login", "api\AuthController@login")->name("auth.login");
        Route::post("refresh", "api\AuthController@refresh")->middleware("auth:api")->name("auth.refresh");
        Route::get("user-profile", "api\AuthController@userProfile")->name("auth.user-profile")->middleware("auth:api");
    });
});
