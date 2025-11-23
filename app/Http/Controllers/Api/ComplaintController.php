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

    // حقن التبعية لطبقة DAO
    public function __construct(ComplaintRepositoryInterface $complaintRepository)
    {
        $this->complaintRepository = $complaintRepository;
    }

    /**
     * متطلب: جلب بيانات النموذج (يستخدم Caching)
     */
    public function getFormDependencies()
    {
        // DAO: جلب البيانات من طبقة الوصول للبيانات (ستأتي من الكاش)
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

    public function index(){

        $complaint =Complaint::get();

        return response()->json([
            'data'=>$complaint,
            'status' =>200,
            'message'=>'List of complaints'
        ],200);
    }
}
