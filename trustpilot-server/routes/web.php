<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;

Route::get("/categories/{category}", [CategoryController::class, "list"]);
Route::get("/review/{id}", [ReviewController::class, "detail"]);
Route::get("/evaluate/{id}/{stars?}", [CompanyController::class, "evaluate"]);

Route::get("/company/edit/{id}", [CompanyController::class, "edit"]);
Route::get("/company/claim/{id}", [CompanyController::class, "claim_detail"]);
Route::get("/company/{domain}", [CompanyController::class, "detail"]);

Route::get("/admin/companies/edit/{id}", [CompanyController::class, "admin_edit"]);
Route::get("/admin/companies/add", [CompanyController::class, "add"]);
Route::get("/admin/companies", [CompanyController::class, "fetch"]);
Route::get("/admin/categories/add", [CategoryController::class, "add"]);
Route::get("/admin/categories", [CategoryController::class, "fetch"]);

Route::get("/admin/messages", function () {
    return view("admin/messages");
});

Route::get("/admin/users/add", function () {
    return view("admin/users/add");
});

Route::get("/admin/users/edit/{id}", function () {
    return view("admin/users/edit", [
        "id" => request()->id ?? 0
    ]);
});

Route::get("/admin/users", function () {
    return view("admin/users/index");
});

Route::get("/admin/settings", function () {
    return view("admin/settings");
});

Route::get("/admin/login", function () {
    return view("admin/login");
});

Route::get("/admin", function () {
    return view("admin/index");
});

Route::get("/email-verification/{email}", function () {
    return view("email-verification", [
        "email" => request()->email
    ]);
});

Route::get("/reset-password/{email}/{token}", function () {
    return view("reset-password", [
        "token" => request()->token,
        "email" => request()->email
    ]);
})
    ->name("password.reset");

Route::get("/forgot-password", function () {
    return view("forgot-password");
})->name("password.request");

Route::get("/change-password", function () {
    return view("change-password");
});

Route::get("/profile", function () {
    return view("profile");
});

Route::get("/login", function () {
    return view("login");
});

Route::get("/register", function () {
    return view("register");
});

Route::get("/", [UserController::class, "home"]);

// Route::get('/', function () {
//     return view('welcome');
// });
