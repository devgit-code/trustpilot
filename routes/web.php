<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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


Route::group([
    "middleware" => ["guard"]
], function () {
    Route::get("/email-verification/{email}", function () {
        return view("auth.email-verification", [
            "email" => request()->email
        ]);
    });

    Route::get("/reset-password/{email}/{token}", function () {
        return view("auth.reset-password", [
            "token" => request()->token,
            "email" => request()->email
        ]);
    })
        ->name("password.reset");

    Route::get("/forgot-password", function () {
        return view("auth.forgot-password");
    })->name("password.request");

    Route::get("/change-password", function () {
        return view("auth.change-password");
    });

    Route::get("/login", function () {
        return view("auth.login");
    });

    Route::get("/register", function () {
        return view("auth.register");
    });
});


Route::get("/profile", function () {
    return view("profile");
});

Route::get("/", [UserController::class, "home"]);

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/clear-cache', function () {
	Artisan::call('cache:clear');
	Artisan::call('config:clear');
	Artisan::call('config:cache');
	Artisan::call('view:clear');
	Artisan::call('route:clear');
	Artisan::call('optimize');

	return "Cache is cleared";
});