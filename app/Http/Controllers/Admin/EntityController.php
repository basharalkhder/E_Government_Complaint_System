<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EntityManagementService;
use App\Http\Requests\Admin\StoreEntityRequest;
use App\Http\Requests\Admin\UpdateEntityRequest;

use App\Http\Resources\StoreEntityResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EntityController extends Controller
{

    protected $entityManagementService;

    public function __construct(EntityManagementService $entityManagementService)
    {
        $this->entityManagementService = $entityManagementService;
    }

    public function index()
    {
        $entities = $this->entityManagementService->get_all_entity();

        return response_success(StoreEntityResource::collection($entities), 200, 'all entites');
    }

    public function show($id){
        try{
        $entity = $this->entityManagementService->getEntityById($id);
        return response_success(new StoreEntityResource($entity),200);
        }catch(ModelNotFoundException $e) {
            return response_error(null, 404, 'Entity Not Found');
        }
    }


    public function store(StoreEntityRequest $request)
    {
        try {
            $entity = $this->entityManagementService->createEntity($request->all());

            return response_success(new StoreEntityResource($entity), 201, 'Entity created successfully.');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create entity.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(UpdateEntityRequest $request, $id)
    {

        $data = $request->validated();

        try {
            $entity = $this->entityManagementService->updateEntity($id, $data);

            return response_success(new StoreEntityResource($entity), 200, 'Entity Updated Successfully');
        } catch (ModelNotFoundException $e) {
            return response_error(null, 404, 'Entity Not Found');
        }
    }

    public function delete_Entity($id)
    {
        try {
            $this->entityManagementService->deleteEntity($id);
            return response_success(null, 200, 'Entity deleted successfully');
        } catch (ModelNotFoundException $e) {
            return response_error(null, 404, 'Entity Not Found');
        }
    }



   


}
