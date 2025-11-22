<?php

namespace App\Http\Controllers\Api;

use App\Contracts\ComplaintRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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


    public function submit(Request $request)
    {
        if (!Auth::guard('sanctum')->check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }


        $request->validate([
            'complaint_type_code' => 'required|exists:complaint_types,code', 
            'description' => 'required|string|max:1000',
            'department' => 'sometimes|nullable|string|max:150',
            'location_address' => 'sometimes|nullable|string|max:255',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',
            'attachments' => 'sometimes|array|max:5', 
            'attachments.*' => 'file|mimes:pdf,jpg,png|max:5000',
        ]);

        $userId = Auth::guard('sanctum')->id(); 

        
        $complaint = $this->complaintRepository->createComplaint($userId, $request->all());

        return response()->json([
            'message' => 'Complaint submitted successfully.',
            'id' => $complaint->id,
            'reference_number' => $complaint->reference_number
        ], 201);
    }
}
