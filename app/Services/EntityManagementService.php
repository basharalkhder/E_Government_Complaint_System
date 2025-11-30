<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\User;
use App\Models\ComplaintType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EntityManagementService
{
    /**
     * Creates a new entity.
     */
    public function createEntity(array $data)
    {
        // 1. إنشاء الجهة
        try {
            return Entity::create([
                'name_ar' => $data['name_ar'],
                'name_en' => $data['name_en'] ?? null,
                'code' => $data['code'],
                'email' => $data['email'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (QueryException $e) {

            throw new \Exception('Failed to create entity due to a database error.', 0, $e);
        }
    }

    // public function getEntityById(int $id)
    // {

    //     return Entity::findOrFail($id);
    // }

    // public function updateEntity($id, array $data)
    // {

      
    //     try {
    //          $data = $this->getEntityById($id);

    //         $data->update([
    //             'name_ar' => $data['name_ar'] ?? $data->name_ar,
    //             'name_en' => $data['name_en'] ?? $data->name_en,
    //             'code' => $data['code'] ?? $data->code,
    //             'email' => $data['email'] ?? $data->email,
    //             'is_active' => $data['is_active'] ?? $data->is_active,
    //             'notes' => $data['notes'] ?? $data->notes,
    //         ]);

    //         return $data;
    //     }catch (ModelNotFoundException $e) {
    //         return response_error(NULL, 404, 'Entity id not found');
    //     } 
    //     catch (QueryException $e) {
    //         throw new \Exception('Failed to update entity due to a database error.', 0, $e);
    //     }
    // }

    // public function deleteEntity(Entity $entity): bool
    // {
        
    //     try {
    //         return $entity->delete();
    //     } catch (\Exception $e) {
    //         throw new \Exception('Failed to delete entity. It might have related records.', 0, $e);
    //     }
    // }

    /**
     * Creates a new employee user and links them to an entity.
     */
    public function createEmployee(array $data)
    {
        try {
            $employee = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role_id' => 2,
                'entity_id' => $data['entity_id'],
                'is_verified' => 1,
            ]);

            return $employee->load('entity');
        } catch (QueryException $e) {
            throw new \Exception('Failed to create employee account due to a database error.', 0, $e);
        }
    }

    /**
     * Creates a new complaint type and links it to an entity.
     */
    public function createComplaintType(array $data)
    {

        try {
            $complaintType = ComplaintType::create([
                'name_ar' => $data['name_ar'],
                'code' => $data['code'],
                'related_department' => $data['related_department'] ?? null,
                'entity_id' => $data['entity_id'],
            ]);

            return $complaintType->load('entity');
        } catch (QueryException $e) {
            throw new \Exception('Failed to create complaint type due to a database error.', 0, $e);
        }
    }
}
