<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\EntityComplaintController;
use App\Http\Controllers\Admin\EntityController;
use App\Http\Controllers\Admin\AdminComplaintController;

use App\Http\Controllers\Api\Auth\AdminAuthController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//done
Route::post('register', [AuthController::class, 'register']);

//done
Route::post('login', [AuthController::class, 'login']);

//done
Route::post('verify-otp', [AuthController::class, 'verify']); //التفعيل otp



Route::middleware(['auth:sanctum', 'role:citizen', 'verified'])->group(function () {

    //done
    Route::post('complaints/submit', [ComplaintController::class, 'submit']); //تقديم شكوى

    //done
    Route::get('complaints/dependencies', [ComplaintController::class, 'getFormDependencies']); //جلب أنواع الشكاوي

    //done
    Route::get('getComplaints', [ComplaintController::class, 'index']); //عرض الشكاوي الخاصة بالعميل

    Route::post('logout', [AuthController::class, 'logout']);
});


Route::middleware(['auth:sanctum', 'role:employee'])->group(function () {

    //done
    Route::post('employee/complaints/{complaint_id}/status', [ComplaintController::class, 'updateStatus']); //تحديث حالة الشكوى

    //done
    Route::get('employee/complaints', [ComplaintController::class, 'index']); //جلب جميع الشكاوي

    // مسار استعراض الشكاوى
    Route::get('/entity-panel/complaints', [EntityComplaintController::class, 'index']);//done

});


Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    //اضافة جهة جديدة
    Route::post('entities', [EntityController::class, 'store']);//done

    //اضافة حساب موظف
    Route::post('add/employee', [EntityController::class, 'storeEmployee']);//done

    //اضافة نوع شكوى جديد
    Route::post('add/complaint-types', [EntityController::class, 'storeComplaintType']);//done

    // مسار استعراض جميع الشكاوى للمشرف
    Route::get('complaints', [AdminComplaintController::class, 'index']);//done

    // مسار تصدير التقارير (CSV,pdf)
    Route::get('complaints/export', [AdminComplaintController::class, 'exportReports']);//done

});

//done
Route::post('admin/login', [AdminAuthController::class, 'login']); //تسجيل دخول admin or employee

Route::middleware(['auth:sanctum','role:employee,admin'])->group(function () {

Route::post('admin/logout',[AdminAuthController::class,'logout']);//done
});