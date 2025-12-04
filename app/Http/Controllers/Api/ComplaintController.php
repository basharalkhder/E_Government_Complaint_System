<?php

namespace App\Http\Controllers\Api;


use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SubmitComplaintRequest;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Complaint;
use App\Events\ComplaintStatusUpdated;
use Illuminate\Support\Facades\Auth;
use App\Services\ComplaintService;
use App\Http\Requests\UpdateComplaintStatusRequest;
use Illuminate\Validation\ValidationException;

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

        $data = $request->validated();

        $userId = Auth::guard('sanctum')->id();

        // 1. التحقق من التكرار (المنع الزمني)
        $recentComplaint = Complaint::where('user_id', $userId)
            ->where('complaint_type_code', $data['complaint_type_code'])
            ->whereIn('status', ['New', 'In Progress', 'Requested Info']) // الحالات النشطة
            ->where('created_at', '>', Carbon::now()->subDays(30)) // خلال آخر 30 يوماً
            ->exists();

        if ($recentComplaint) {
            throw ValidationException::withMessages([
                'complaint_type_code' => 'لديك شكوى نشطة بنفس الموضوع مقدمة مؤخراً. يرجى متابعة الشكوى الحالية.'
            ]);
        }

        $complaint = $this->complaintService->createComplaint($userId, $request->all());

        return response()->json([
            'message' => 'Complaint submitted successfully.',
            'data' => $complaint,
            'reference_number' => $complaint->reference_number
        ], 201);
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

            return response()->json([
                'message' => 'Complaint updated successfully.',
                'complaint' => $complaint
            ]);
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

            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }






    public function lockComplaint(Complaint $complaint)
    {
        $currentUserId = Auth::id();

        if ($complaint->entity_id !== Auth::user()->entity_id) {
            return response()->json(['message' => 'Unauthorized. This complaint does not belong to your entity.'], 403);
        }

        try {
            $this->complaintService->acquireLock($complaint);

            return response()->json([
                'message' => 'Complaint successfully locked for editing.',
                'locker' => Auth::user()->name,
                'locked_at' => $complaint->locked_at
            ], 200);
        } catch (ValidationException $e) {
            $errorMessage = collect($e->errors())->flatten()->first();
            return response()->json(['message' => $errorMessage], 403);
        }
    }



    public function updateByUser(Request $request, $id)
    {
        $user = Auth::user();
        $complaint = Complaint::findOrFail($id);

        if ($complaint->user_id !== $user->id) {
            throw new AuthorizationException('غير مصرح لك بتعديل هذه الشكوى.');
        }


        if ($complaint->status !== 'Requested Info') {
            throw ValidationException::withMessages([
                'status' => 'لا يمكن تعديل الشكوى إلا في حالة "Requested Info".'
            ]);
        }

        $data =  $request->validate([
            'description' => 'sometimes|string|max:1000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,jpg,png|max:5120',
        ]);

        try {
            $updatedComplaint = $this->complaintService->updateComplaintByUser(
                $complaint,
                $data,
                $user->id
            );

            if ($request->hasFile('attachments')) {
                $this->complaintService->addAttachmentsinhistory($updatedComplaint, $request->file('attachments'), $user->id);
            }

            return response()->json([
                'message' => 'تم تحديث الشكوى والمرفقات بنجاح.',
                'complaint' => $updatedComplaint
            ], 200);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
