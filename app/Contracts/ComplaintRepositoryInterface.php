<?php

namespace App\Contracts;

use App\Models\Complaint;
use Illuminate\Support\Collection;

interface ComplaintRepositoryInterface
{
    
    public function createComplaint(int $userId, array $data): Complaint;

    
    public function getComplaintTypes(): Collection;
}