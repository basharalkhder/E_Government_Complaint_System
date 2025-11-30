<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Validation\ValidationException;
use App\Http\Requests\Citizen\RegisterRequest;
use App\Http\Requests\Citizen\LoginRequest;
use App\Http\Resources\Citizen\RegisterResource;
use App\Http\Resources\Citizen\LoginResource;
use App\Services\UserService;
use App\Exceptions\ResendVerificationException;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {

        $this->userService = $userService;
    }


    public function register(RegisterRequest $request)
    {

        $userData = $request->validated();

        try {
            $user = $this->userService->registerNewUser($userData);
            return response_success(new RegisterResource($user), 201, 'The registration was completed and the OTP code was sent to the email');
        } catch (ResendVerificationException $e) {

            return response_success(null, 200, $e->getMessage());
        } catch (\Exception $e) {
            return response_error(null, 400, $e->getMessage());
        }
    }



    public function login(LoginRequest $request)
    {
        $request->validated();

        try {
            $authData = $this->userService->authenticate(
                $request->email,
                $request->password
            );

            $user = $authData['user'];
            $token = $authData['token'];

            return response_success(new LoginResource($user, $token), 200, 'Login successful');
        } catch (\App\Exceptions\AccountNotFoundException $e) { 
            return response_error(Null, 404, $e->getMessage());
        } catch (\App\Exceptions\AccountNotVerifiedException $e) { 
            return response_error(Null, 403, $e->getMessage());
        } catch (\Illuminate\Validation\ValidationException $e) { 
            return response_error(Null, 401, 'Invalid email or password.');
        } catch (\Exception $e) {
            return response_error(Null, 500, 'An unexpected error occurred during login.');
        }
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }




    public function verify(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp' => 'required|digits:6',
        ]);

        try {

            $authData = $this->userService->verifyUserAndGenerateToken(
                $request->user_id,
                $request->otp
            );

            return response()->json([
                'message' => 'Account successfully verified.',
                'token' => $authData['token'],
                'user' => $authData['user']->only(['id', 'name', 'email'])
            ]);
        } catch (ValidationException $e) {
            throw $e;
        }
    }
}
