<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ManageUserService;
use App\Http\Resources\ManageUserResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ManageUserController extends Controller
{
    protected $manageuserService;
    public function __construct(ManageUserService $manageuserService)
    {
        $this->manageuserService = $manageuserService;
    }

    public function index()
    {
        $users = $this->manageuserService->getAllUsers();

        return response_success(ManageUserResource::collection($users), 200, 'All users');
    }

    public function block($id)
    {
        try {
             $this->manageuserService->block_user($id);
            return response_success(null, 200, 'The user has been blocked');
        } catch (ModelNotFoundException $e) {
            return response_error(null, 404, 'user not found');
        }
    }

    public function unblock($id)
    {
        try {
             $this->manageuserService->unblock_user($id);
            return response_success(null, 200, 'The user has been unblocked');
        } catch (ModelNotFoundException $e) {
            return response_error(null, 404, 'user not found');
        }
    }
}
