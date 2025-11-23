<?php

namespace App\Http\Controllers\Api;

use App\Contracts\UserRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\Role;

class AuthController extends Controller
{
    protected $userRepository;


    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    public function register(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $citizenRole = Role::where('name', 'citizen')->first();

        if (!$citizenRole) {

            return response()->json(['message' => 'System error: Citizen role configuration missing.'], 500);
        }

        $userData = array_merge($request->all(), [
            'role_id' => $citizenRole->id,
        ]);

        $user = $this->userRepository->create($userData);

        $verificationToken = Str::random(60);

        Cache::put($verificationToken, $user->id, now()->addMinutes(15));

        $this->userRepository->generateAndSendOtp($user);



        return response()->json([
            'message' => 'User registered successfully. An OTP will be sent for verification.',
            'user' => $user->only(['id', 'name', 'email'])
        ], 201);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);


        $user = $this->userRepository->findByEmail($request->email);

        if (!$user || !Hash::check($request->password, $user->password)) {


            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $token = $this->userRepository->createAuthToken($user);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email'])
        ]);
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

        $user = $this->userRepository->find($request->user_id); 

        if (!$user) {
            throw ValidationException::withMessages(['user_id' => ['User not found.']]);
        }

        if ($this->userRepository->verifyOtp($user, $request->otp)) {
            $token = $this->userRepository->createAuthToken($user);

            return response()->json([
                'message' => 'Account successfully verified.',
                'token' => $token,
                'user' => $user->only(['id', 'name', 'email'])
            ]);
        }

        throw ValidationException::withMessages(['otp' => ['Invalid or expired verification code.']]);
    }
}
