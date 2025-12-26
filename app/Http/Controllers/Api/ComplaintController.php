<?php

namespace App\Http\Controllers\Api;

use App\Enums\ComplaintStatus;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SubmitComplaintRequest;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Complaint;
use Illuminate\Support\Facades\Auth;
use App\Services\ComplaintService;
use App\Http\Requests\UpdateComplaintStatusRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\CreateComplaintResource;
use App\Http\Resources\UpdateStatusComplaintResource;
use App\Http\Resources\ComplaintTypeResource;
use App\Http\Resources\ComplaintUserResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

        return response_success(ComplaintUserResource::collection($complaints), 200, 'all Complaints');
    }

    public function getFormDependencies()
    {

        $types = $this->complaintService->getComplaintTypes();

        return response_success(ComplaintTypeResource::collection($types), 200, 'All Types Complaints');
    }


    public function submit(SubmitComplaintRequest $request)
    {


        $data = $request->validated();

        $userId = Auth::guard('sanctum')->id();

        $complaint = $this->complaintService->handleComplaintSubmission($userId, $data);


        return response_success(new CreateComplaintResource($complaint), 201, 'Complaint submitted successfully.');
    }



    public function updateStatus(UpdateComplaintStatusRequest $request, $id)
    {
        $request->validated();

        $user = Auth::user();

        try {

            $complaint = $this->complaintService->updateComplaintStatus(
                $id,
                $request->only(['status', 'admin_notes']),
                $user->entity_id
            );

            return response_success(new UpdateStatusComplaintResource($complaint), 200, 'Complaint updated successfully.');
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        } catch (AuthorizationException $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Complaint not found.'
            ], 404);
        } catch (\Exception $e) {

            $status = ($e->getMessage() === 'This complaint status has already been updated by another employee.') ? 409 : 500;

            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Concurrency Error'
            ],  $status);
        }
    }



    public function updateByUser(Request $request, $id)
    {
        // 1. Validation
        $data = $request->validate([
            'description' => 'sometimes|string|max:1000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,jpg,png|max:5120',
        ]);

        try {
            // 2. Business Logic Execution via Service
            $complaint = $this->complaintService->processUserUpdate(
                $id,
                Auth::id(),
                $data,
                $request->file('attachments')
            );

            return response_success(new CreateComplaintResource($complaint), 200, 'Complaint updated successfully.');
        } catch (ModelNotFoundException $e) {
            return response_error(Null, 404, 'Complaint Not Found');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
}
