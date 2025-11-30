<?php

namespace App\Services;

use App\Models\Complaint;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User; 

class EntityComplaintService 
{
    /**
     * @inheritDoc
     */
    public function getEntityComplaints(User $user)
    {
       
        if ($user->role_id !== 2) {
            throw new AuthorizationException('Unauthorized access. User does not have employee role.');
        }

        $entityId = $user->entity_id;

        if (!$entityId) {
            throw new AuthorizationException('Employee is not assigned to an entity.');
        }

        $complaints = Complaint::where('entity_id', $entityId)
            ->with(['user', 'entity'])
            ->get();

        return $complaints;
    }
}