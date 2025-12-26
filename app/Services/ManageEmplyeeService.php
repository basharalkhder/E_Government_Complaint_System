<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\User;
use App\Models\ComplaintType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;


class ManageEmplyeeService
{

    public function find_Employee_byId($id)
    {
        return User::where('role_id', 2)
            ->with('entity')
            ->findOrFail($id);
    }

    public function get_all_employee()
    {
        return User::where('role_id', 2)->with('entity')->get();
    }


    public function createEmployee($data)
    {
        try {
            $data['role_id'] = 2;
            $data['is_verified'] = 1;

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $employee = User::create($data);

            return $employee->load('entity');
        } catch (\Exception $e) {

            throw new \Exception("Error creating employee: " . $e->getMessage());
        }
    }

    public function updateEmployee($data, $id)
    {

        $employee = $this->find_Employee_byId($id);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $employee->update($data);

        return $employee->load('entity');
    }

    public function deleteEmployee($id)
    {

        $employee = $this->find_Employee_byId($id);

        return $employee->delete();
    }
}
