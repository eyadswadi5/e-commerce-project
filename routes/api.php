<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(["namespace" => "App\Http\Controllers", "middleware" => "api"], function ($router) {

    Route::post("/password/forget", "ResetPasswordController@sendResetLink")->name("password.send-reset-link");
    Route::put("/password/reset/{token}", "ResetPasswordController@resetPassword")->name("password.reset");
    
    Route::group(["prefix" => "auth"], function () {
        Route::post("register", "api\AuthController@register")->name("auth.register");
        Route::post("login", "api\AuthController@login")->name("auth.login");
        Route::post("refresh", "api\AuthController@refresh")->middleware("auth:api")->name("auth.refresh");
        Route::get("user-profile", "api\AuthController@userProfile")->middleware("auth:api")->name("auth.user-profile");
    });

    Route::group(["prefix" => "admin/permission", "middleware" => ["auth:api", "is-permitted"]], function () {
        Route::get("/", "api\PermissionController@index")->name("permission.index");
        Route::get("/role/{role_id}", "api\PermissionController@role_has_permissions")->name("permission.role.list");
        Route::get("/user/{user_id}", "api\PermissionController@user_has_permissions")->name("permission.user.list");
        Route::put("/role/{role_id}/add", "api\PermissionController@add_role_permissions")->name("permission.role.add");
        Route::put("/user/{user_id}/add", "api\PermissionController@add_user_permissions")->name("permission.user.add");
        Route::put("/role/{role_id}/remove", "api\PermissionController@remove_role_permissions")->name("permission.role.remove");
        Route::put("/user/{user_id}/remove", "api\PermissionController@remove_user_permissions")->name("permission.user.remove");
    });
});
