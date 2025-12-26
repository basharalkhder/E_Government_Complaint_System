<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Employee\GetComplaintsResource;
use App\Models\ComplaintType;
use App\Services\EntityComplaintService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule;


class EntityComplaintController extends Controller
{

    protected $complaintService;

    public function __construct(EntityComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }


    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response_error(NULL, 401, 'Unauthenticated.');
        }

        try {
            
            $complaints = $this->complaintService->getEntityComplaints($user);

            return response_success(GetComplaintsResource::collection($complaints), 200, 'complaints retrieved successfully.');

        } catch (AuthorizationException $e) {
            
            return response_error(NULL, 403, $e->getMessage());
        } catch (\Exception $e) {

            return response_error(NULL, 500, 'An internal error occurred while retrieving complaints.');
        }
    }

    
}
