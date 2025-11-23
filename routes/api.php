<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\Auth\AdminAuthController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::post('verify-otp', [AuthController::class, 'verify']);//التفعيل otp

Route::get('complaints/dependencies', [ComplaintController::class, 'getFormDependencies']);//جلب أنواع الشكاوي

Route::middleware(['auth:sanctum', 'role:citizen','verified'])->group(function () {
    
    Route::post('complaints/submit', [ComplaintController::class, 'submit']);//تقديم شكوى

    Route::post('logout', [AuthController::class, 'logout']);
});


Route::post('admin/login', [AdminAuthController::class, 'login']);//تسجيل دخول admin or employee


Route::middleware(['auth:sanctum', 'role:employee'])->group(function () {

    Route::post('employee/complaints/{complaint_id}/status', [ComplaintController::class, 'updateStatus']);//تحديث حالة الشكوى

    Route::get('employee/complaints', [ComplaintController::class, 'index']);//جلب جميع الشكاوي
});