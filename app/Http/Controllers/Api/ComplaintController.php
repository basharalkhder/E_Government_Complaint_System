<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SubmitComplaintRequest;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Complaint;
use App\Events\ComplaintStatusUpdated;
use Illuminate\Support\Facades\Auth;
use App\Services\ComplaintService;
use App\Http\Requests\UpdateComplaintStatusRequest;

class ComplaintController extends Controller
{
   protected $complaintService;

    
    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }


    public function index()
    {

        $complaints = $this->complaintService->getUserComplaints();

        return response()->json([
            'data' => $complaints,
            'status' => 200,
            'message' => 'all Complaints'
        ], 200);
    }

    public function getFormDependencies()
    {

        $types = $this->complaintService->getComplaintTypes();

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


        $complaint = $this->complaintService->createComplaint($userId, $request->all());

        return response()->json([
            'message' => 'Complaint submitted successfully.',
            'data' => $complaint,
            'reference_number' => $complaint->reference_number
        ], 201);
    }



    public function updateStatus(UpdateComplaintStatusRequest $request , $id)
    {
        $request->validated();
        
        $user = Auth::user();

        try {
            
            $complaint = $this->complaintService->updateComplaintStatus(
                $id,
                $request->only(['status', 'admin_notes']), 
                $user->entity_id 
            );

            return response()->json([
                'message' => 'Complaint updated successfully.',
                'complaint' => $complaint
            ]);

        } catch (AuthorizationException $e) {
           
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Complaint not found.'
            ], 404);

        } catch (\Exception $e) {
            
            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
