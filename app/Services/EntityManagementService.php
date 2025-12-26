<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\User;
use App\Models\ComplaintType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;


class EntityManagementService
{

    public function get_all_entity()
    {
        return Cache::remember('all_entities_list', 3600, function () {
            return Entity::all();
        });
    }


    public function createEntity(array $data)
    {
        // 1. إنشاء الجهة
        try {
            $type =  Entity::create([
                'name_ar' => $data['name_ar'],
                'name_en' => $data['name_en'] ?? null,
                'code' => $data['code'],
                'email' => $data['email'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'notes' => $data['notes'] ?? null,
            ]);

            return $type;
        } catch (QueryException $e) {

            throw new \Exception('Failed to create entity due to a database error.', 0, $e);
        }
    }

    public function getEntityById(int $id)
    {

        return Cache::remember("entity_{$id}", 3600, function () use ($id) {
            return Entity::findOrFail($id);
        });
    }

    public function updateEntity($id,  $data)
    {


        $entity = $this->getEntityById($id);

        $entity->update($data);


        return $entity;
    }

    public function deleteEntity($id)
    {
        $entity = $this->getEntityById($id);

        return $entity->delete();
    }

    
   

   
}
