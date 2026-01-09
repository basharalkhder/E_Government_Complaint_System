<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use App\Http\Resources\ComplaintHistoryResource;
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

    public function index()
    {
        $complaints = $this->adminComplaintService->getAllComplaintsForAdmin();
        return response_success(ComplaintResource::collection($complaints), 200, 'All complaints');
    }

    public function exportReports(Request $request)
    {
        return $this->adminComplaintService->exportComplaints($request);
    }



    public function getStatistics()
    {
        $stats = $this->adminComplaintService->getComplaintsStatistics();

        return response_success($stats, 200, 'Complaints statistics retrieved successfully');
    }

    public function getHistory($id)
    {

        $history = $this->adminComplaintService->getComplaintHistoryByComplaintId($id);

        return response_success(ComplaintHistoryResource::collection($history), 200);
    }


    public function getSystemTraces()
    {
        $logs = $this->adminComplaintService->getAllTraces();


        $data = $logs->getCollection()->map(function ($log) {
            return [
                'id' => $log->id,
                'action_date' => $log->created_at->format('Y-m-d H:i:s'),
                'performed_by' => $log->causer->name ?? 'System',
                'event' => $log->description,
                'target_type' => class_basename($log->subject_type),
                'target_id' => $log->subject_id,
                'target_url' => $this->adminComplaintService->getSubjectUrl($log),
                'changes' => $log->properties,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
