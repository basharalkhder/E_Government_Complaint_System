<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use App\Http\Resources\Employee\GetComplaintsResource;
use App\Http\Resources\ComplaintResource;

use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\AdminComplaintService;

class AdminComplaintController extends Controller
{

    protected $adminComplaintService;

    public function __construct(AdminComplaintService $adminComplaintService)
    {
        $this->adminComplaintService = $adminComplaintService;
    }



    public function index(Request $request)
    {

        $complaints = $this->adminComplaintService->getFilteredComplaints($request);


        return response_success(GetComplaintsResource::collection($complaints), 200, 'all complaints');
    }

    public function exportReports(Request $request)
    {
        return $this->adminComplaintService->exportComplaints($request);
    }

    public function show($id)
    {
        $complaint = Complaint::findOrFail($id);

       // 1. التحميل المسبق للعلاقات الضرورية
        $complaint->load([
            // تحميل سجل التاريخ وترتيبه
            'histories' => fn ($query) => $query->with('user')->orderBy('created_at', 'desc'),
            'user',        // المالك الأصلي
            'entity',      // الجهة
            'attachments', // المرفقات
        ]);

        // 2. 🚨 إرجاع الاستجابة باستخدام المورد
        return  response_success(ComplaintResource::make($complaint),200,'تم استعراض تفاصيل الشكوى مع سجل التاريخ الكامل.');
    }
}
