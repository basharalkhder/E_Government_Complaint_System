<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use App\Http\Resources\Employee\GetComplaintsResource;
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
}
