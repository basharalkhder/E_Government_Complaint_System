<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\User;
use App\Models\ComplaintType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Services\EntityManagementService;
use App\Http\Requests\Admin\StoreEntityRequest;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\StoreComplaintTypeRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Admin\UpdateEntityRequest;


class EntityController extends Controller
{

    protected $entityManagementService;

    public function __construct(EntityManagementService $entityManagementService)
    {
        $this->entityManagementService = $entityManagementService;
    }



    public function store(StoreEntityRequest $request)
    {
        try {
            $entity = $this->entityManagementService->createEntity($request->all());

            // 3. إرجاع استجابة النجاح
            return response()->json([
                'message' => 'Entity created successfully.',
                'data' => $entity
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create entity.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function storeEmployee(StoreEmployeeRequest $request)
    {
        try {
            $employee = $this->entityManagementService->createEmployee($request->all());

            return response()->json([
                'message' => 'Employee account created and assigned successfully.',
                'data' => ['user' => $employee]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create employee account.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    function storeComplaintType(StoreComplaintTypeRequest $request)
    {
        try {
            $complaintType = $this->entityManagementService->createComplaintType($request->all());

            return response()->json([
                'message' => 'Complaint type created and successfully linked to the entity.',
                'data' => $complaintType
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create complaint type.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
