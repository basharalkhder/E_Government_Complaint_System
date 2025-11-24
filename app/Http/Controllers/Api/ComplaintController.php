<?php

namespace App\Http\Controllers\Api;

use App\Contracts\ComplaintRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SubmitComplaintRequest;
use App\Models\Complaint;
use App\Events\ComplaintStatusUpdated;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    protected $complaintRepository;

    
    public function __construct(ComplaintRepositoryInterface $complaintRepository)
    {
        $this->complaintRepository = $complaintRepository;
    }



    public function index(){

        $user = Auth::user();

        $Complaint = $user->complaints;

        return response()->json([
            'data' =>$Complaint,
            'status'=>200,
            'message'=>'all Complaints'
        ],200);
    }
    
    public function getFormDependencies()
    {
        
        $types = $this->complaintRepository->getComplaintTypes();

        return response()->json([
            'complaint_types' => $types
        ]);
    }


    public function submit(SubmitComplaintRequest $request)
    {
        if (!Auth::guard('sanctum')->check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->validated();

        $userId = Auth::guard('sanctum')->id();


        $complaint = $this->complaintRepository->createComplaint($userId, $request->all());

        return response()->json([
            'message' => 'Complaint submitted successfully.',
            'data' => $complaint,
            'reference_number' => $complaint->reference_number
        ], 201);
    }



    public function updateStatus(Request $request,$id)
    {
        // 1. التحقق من صحة البيانات (ضمان أن الحالة الجديدة من ضمن الثوابت)
        $request->validate([
            'status' => 'required|in:' .
                Complaint::STATUS_IN_PROCESS . ',' .
                Complaint::STATUS_COMPLETED . ',' .
                Complaint::STATUS_REJECTED,
        ]);

        $complaint = Complaint::findOrFail($id);
        $complaint->update(['status' => $request->status]);


        event(new ComplaintStatusUpdated($complaint));

        return response()->json([
            'message' => 'Complaint status updated and user notified successfully.',
            'complaint' => $complaint
        ]);
    }

   
}
