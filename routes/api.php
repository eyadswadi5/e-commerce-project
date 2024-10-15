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
        Route::delete("/role/{role_id}/remove", "api\PermissionController@remove_role_permissions")->name("permission.role.remove");
        Route::delete("/user/{user_id}/remove", "api\PermissionController@remove_user_permissions")->name("permission.user.remove");
    });

    Route::group(["prefix" => "product"], function () {
        Route::get("/", "api\ProductController@index")->name("product.index");
        Route::get("/{product_id}", "api\ProductController@find")->name("product.find");
    });

    Route::group(["prefix" => "admin/product", "middleware" => ["auth:api", "is-permitted"]], function () {
        Route::post("/", "api\ProductController@store")->name("product.store");
        Route::put("/{product_id}", "api\ProductController@update")->name("product.update");
        Route::delete("/{product_id}", "api\ProductController@delete")->name("product.delete");
    });

    Route::group(["prefix" => "category"], function () {
        Route::get("/", "api\CategoryController@index")->name("category.index");
    });

    Route::group(["prefix"=>"admin/category", "middleware" => ["auth:api"]], function () {
        Route::post("/", "api\CategoryController@store")->name("category.store");
        Route::put("/{id}", "api\CategoryController@update")->name("category.update");
        Route::delete("/{id}", "api\CategoryController@destroy")->name("category.delete");
        Route::get("/{id}", "api\CategoryController@show")->name("category.show");
    });

    Route::group(["prefix" => "company"], function () {
        Route::get("/", "api\CompanyController@index")->name("company.index");
    });

    Route::group(["prefix"=>"admin/company", "middleware" => ["auth:api"]], function () {
        Route::post("/", "api\CompanyController@store")->name("company.store");
        Route::put("/{id}", "api\CompanyController@update")->name("company.update");
        Route::delete("/{id}", "api\CompanyController@destroy")->name("company.delete");
        Route::get("/{id}", "api\CompanyController@show")->name("company.show");
    });
});
