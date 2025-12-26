<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Services\ManageEmplyeeService;
use App\Http\Resources\EmployeeResource;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ManageEmployeeController extends Controller
{
    protected $manageEmplyeeService;

    public function __construct(ManageEmplyeeService $manageEmplyeeService)
    {
        $this->manageEmplyeeService = $manageEmplyeeService;
    }

    public function index(){
        $employee =$this->manageEmplyeeService->get_all_employee();
        return response_success(EmployeeResource::collection($employee),200,'all employee');
    }

    public function show($id){
        try{
            $employee = $this->manageEmplyeeService->find_Employee_byId($id);
            return response_success(new EmployeeResource($employee) ,200);
        }catch(ModelNotFoundException $e){
            return response_error(null ,404 , 'employee not found');
        }
    }



    public function storeEmployee(StoreEmployeeRequest $request)
    {
        $data = $request->validated();
        try {
            $employee = $this->manageEmplyeeService->createEmployee($data);

            return response_success(new EmployeeResource($employee), 201, 'Employee account created and assigned successfully.');
        } catch (\Exception $e) {
            return response_error(null, 500, $e->getMessage());
        }
    }

    public function updateEmployee(UpdateEmployeeRequest $request, $id)
    {
        $data = $request->validated();

        try {
            $employee = $this->manageEmplyeeService->updateEmployee($data, $id);
            return response_success(new EmployeeResource($employee), 200, 'Employee Updated Successfully');
        } catch (ModelNotFoundException $e) {
            return response_error(null, 404, 'Employee not found');
        }
    }

    public function deleteEmployee($id){
         try {
             $this->manageEmplyeeService->deleteEmployee($id);
            return response_success(null, 200, 'Employee deleted Successfully');
        } catch (ModelNotFoundException $e) {
            return response_error(null, 404, 'Employee not found');
        }
    }
}
