<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CategoryController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group([
    "middleware" => ["web"]
], function () {
    Route::post("/companies/fetch-by-category", [CompanyController::class, "fetch_by_category"]);
    Route::post("/companies/reviews", [ReviewController::class, "fetch_by_company"]);
    Route::post("/companies/search", [CompanyController::class, "search"]);
    Route::post("/companies/find", [CompanyController::class, "find"]);

    Route::post("/verify-email", [UserController::class, "verify_email"]);
    Route::post("/reset-password", [UserController::class, "reset_password"]);
    Route::post("/send-password-reset-link", [UserController::class, "send_password_reset_link"]);
    Route::post("/login", [UserController::class, "login"]);
    Route::post("/register", [UserController::class, "register"]);
    Route::post("/admin/login", [AdminController::class, "login"]);
});

Route::group([
    "middleware" => ["auth:sanctum", "web"]
], function () {
    Route::post("/reviews/delete", [ReviewController::class, "destroy"]);

    Route::post("/companies/update", [CompanyController::class, "update"]);
    Route::post("/companies/verify-claim", [CompanyController::class, "verify_claim"]);
    Route::post("/companies/claim", [CompanyController::class, "claim"]);
    Route::post("/companies/review", [ReviewController::class, "review"]);
    
    Route::post("/messages/fetch", [MessagesController::class, "fetch"]);
    Route::post("/messages/send", [MessagesController::class, "send"]);

    Route::post("/change-password", [UserController::class, "change_password"]);
    Route::post("/save-profile", [UserController::class, "save_profile"]);
    Route::post("/logout", [UserController::class, "logout"]);
    Route::post("/me", [UserController::class, "me"]);

    Route::post("/admin/companies/delete", [CompanyController::class, "destroy"]);
    Route::post("/admin/companies/add", [CompanyController::class, "store"]);
    Route::post("/admin/companies/update", [CompanyController::class, "admin_update"]);
    Route::post("/admin/companies/fetch", [CompanyController::class, "fetch"]);
    Route::post("/admin/categories/delete", [CategoryController::class, "destroy"]);
    Route::post("/admin/categories/add", [CategoryController::class, "store"]);

    Route::post("/admin/send-message", [AdminController::class, "send_message"]);
    Route::post("/admin/fetch-messages", [AdminController::class, "fetch_messages"]);
    Route::post("/admin/fetch-contacts", [AdminController::class, "fetch_contacts"]);
    Route::post("/admin/users/add", [AdminController::class, "add_user"]);
    Route::post("/admin/users/change-password", [AdminController::class, "change_user_password"]);
    Route::post("/admin/users/delete", [AdminController::class, "delete_user"]);
    Route::post("/admin/users/update", [AdminController::class, "update_user"]);
    Route::post("/admin/users/fetch/{id}", [AdminController::class, "fetch_single_user"]);
    Route::post("/admin/users/fetch", [AdminController::class, "fetch_users"]);
    Route::post("/admin/fetch-settings", [AdminController::class, "fetch_settings"]);
    Route::post("/admin/save-settings", [AdminController::class, "save_settings"]);
});
