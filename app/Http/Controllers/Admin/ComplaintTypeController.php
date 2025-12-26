<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ComplaintTypeService;
use App\Http\Requests\Admin\StoreComplaintTypeRequest;
use App\Http\Requests\Admin\UpdateComplaintTypeRequest;
use App\http\Resources\ComplaintTypeResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ComplaintTypeController extends Controller
{

    protected $complaintTypeService;

    public function __construct(ComplaintTypeService $complaintTypeService)
    {
        $this->complaintTypeService = $complaintTypeService;
    }

    public function index()
    {
        $complaintTypes = $this->complaintTypeService->get_all_ComplaintTypes();

        return response_success(ComplaintTypeResource::collection($complaintTypes), 200, 'All Complaints Types');
    }

    public function show($id)
    {
        try {
            $complaintType = $this->complaintTypeService->getComplaintTypeById($id);
            return response_success(new ComplaintTypeResource($complaintType), 200);
        } catch (ModelNotFoundException $e) {
            return response_error(null, 404, 'Complaint type not found');
        }
    }

    public function storeComplaintType(StoreComplaintTypeRequest $request)
    {
        $data = $request->validated();
        try {
            $complaintType = $this->complaintTypeService->createComplaintType($data);

            return response_success(new ComplaintTypeResource($complaintType), 201, 'Complaint Type created successfully');
        } catch (\Exception $e) {
            return response_error(null, 500, $e->getMessage());
        }
    }

    public function updateComplaintType(UpdateComplaintTypeRequest $request, $id)
    {
        $data = $request->validated();

        try {
            $updatedType = $this->complaintTypeService->updateComplaintType($data, $id);

            return response_success(
                new ComplaintTypeResource($updatedType),
                200,
                'Complaint Type Updated Successfully'
            );
        } catch (ModelNotFoundException $e) {
            return response_error(null, 404, 'Complaint Type not found');
        } catch (\Exception $e) {
            return response_error(null, 500, $e->getMessage());
        }
    }

    public function deleteTypeComplaint($id)
    {
        try {
            $this->complaintTypeService->deleteTypeComplaint($id);
            return response_success(null, 200, 'Complaint Type deleted successfully');
        } catch (ModelNotFoundException $e) {
            return response_error(null, 404, 'Complaint Type not found');
        }
    }
}
