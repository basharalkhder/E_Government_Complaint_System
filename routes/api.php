<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\EntityComplaintController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\Admin\ComplaintTypeController;
use App\Http\Controllers\Admin\EntityController;
use App\Http\Controllers\Admin\ManageEmployeeController;
use App\Http\Controllers\Admin\AdminComplaintController;
use App\Http\Controllers\Admin\ManageUserController;
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

//jmeter
// Route::get('complaints/dependencies', [ComplaintController::class, 'getFormDependencies']); 


Route::middleware(['auth:sanctum', 'role:citizen', 'verified'])->group(function () {

    //done
    Route::post('complaints/submit', [ComplaintController::class, 'submit']); //تقديم شكوى

    Route::post('{complaint_id}/update-by-user', [ComplaintController::class, 'updateByUser']); //تحديث الشكوى 

    //done
   Route::get('complaints/dependencies', [ComplaintController::class, 'getFormDependencies']); //جلب أنواع الشكاوي

    //done
    Route::get('getComplaints', [ComplaintController::class, 'index']); //عرض الشكاوي الخاصة بالعميل

    Route::post('logout', [AuthController::class, 'logout']);
});



Route::middleware(['auth:sanctum', 'role:employee'])->group(function () {


    //done
    Route::post('employee/complaints/{complaint_id}/status', [ComplaintController::class, 'updateStatus']); //تحديث حالة الشكوى


    // مسار استعراض الشكاوى
    Route::get('/entity-panel/complaints', [EntityComplaintController::class, 'index']); //done



});


Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    //===============Manage Entity========================================

    //عرض جهة معينة
    Route::get('entities/{entity}', [EntityController::class, 'show']);
    //عرض كل الجهات
    Route::get('entities', [EntityController::class, 'index']);
    //اضافة جهة جديدة
    Route::post('entities', [EntityController::class, 'store']); //done
    //تعديل جهة 
    Route::post('entities/{entity}', [EntityController::class, 'update']);
    //حذف جهة
    Route::post('entities/{entity}', [EntityController::class, 'delete_Entity']);

    //=================================================================================

    //=============================Manage Employee=============================================================

    //عرض موظف معين
    Route::get('employee/{employee}', [ManageEmployeeController::class, 'show']);
    //عرض جميع الموظفين
    Route::get('employee', [ManageEmployeeController::class, 'index']);
    //اضافة حساب موظف
    Route::post('add/employee', [ManageEmployeeController::class, 'storeEmployee']); //done

    //تعديل حساب موظف
    Route::post('update/employee/{employee}', [ManageEmployeeController::class, 'updateEmployee']);

    //حذف حساب موظف
    Route::post('delete/employee/{employee}', [ManageEmployeeController::class, 'deleteEmployee']);

    //==============================================================================================

    //==================================Manage Complaint Type=================================================

    // مسار استعراض جميع الشكاوى للمشرف
    Route::get('complaint-types', [ComplaintTypeController::class, 'index']);

    //عرض نوع شكوى محددة
    Route::get('complaint-types/{complaint_type}', [ComplaintTypeController::class, 'show']);
    //اضافة نوع شكوى جديد
    Route::post('add/complaint-types', [ComplaintTypeController::class, 'storeComplaintType']);

    //تحديث نوع شكوى
    Route::post('update/complaint-types/{complaint_type}', [ComplaintTypeController::class, 'updateComplaintType']);

    //حذف نوع شكوى
    Route::post('delete/complaint-types/{complaint_type}', [ComplaintTypeController::class, 'deleteTypeComplaint']);

    //==================================================================================================

    //==============================Manage User==============================
    //عرض جميع المستخدمين 
    Route::get('users', [ManageUserController::class, 'index']);

    //حظر مستخدم
    Route::post('users/{id}/block', [ManageUserController::class, 'block']);

    //فك حظر مستخدم
    Route::post('users/{id}/unblock', [ManageUserController::class, 'unblock']);

    //============================================================================================


    Route::get('all-complaints', [AdminComplaintController::class, 'index']);

    // مسار تصدير التقارير (CSV,pdf)
    Route::get('complaints/export-data', [AdminComplaintController::class, 'exportReports']); //done

    //الإحصائيات
    Route::get('complaints/statistics', [AdminComplaintController::class, 'getStatistics']);

    // Route::get('complaint/{complaint_id}', [AdminComplaintController::class, 'show']); //عرض كل شكوى والسجل الزمني الخاص بها

    //عرض تاريخ تعديلات على شكوى معينة
    Route::get('admin/complaints/{id}/history', [AdminComplaintController::class, 'getHistory']);

    //مراقبة جميع العمليات بالنظام
    
    Route::get('admin/system-traces', [AdminComplaintController::class, 'getSystemTraces']);
});

//done
Route::post('admin/login', [AdminAuthController::class, 'login']); //تسجيل دخول admin or employee

Route::middleware(['auth:sanctum', 'role:employee,admin'])->group(function () {

    Route::post('admin/logout', [AdminAuthController::class, 'logout']); //done
});
