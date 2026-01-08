<?php

namespace App\Services;

use App\Exceptions\AccountNotFoundException;
use App\Exceptions\AccountNotVerifiedException;
use App\Repositories\EloquentUserRepository;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\ResendVerificationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Notifications\LoginFailedNotification;

class UserService
{
    protected $userRepository;

    public function __construct(EloquentUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    // 1--
    public function registerNewUser(array $data)
    {

        $existingUser = $this->userRepository->findByEmail($data['email']);

        if ($existingUser) {
            // حالة أ: المستخدم موجود ومفعل بالفعل
            if ($existingUser->is_verified) {

                throw new \Exception('Email already exists and is verified.');
            }
            // حالة ب: المستخدم موجود ولكنه غير مُفعَّل (الحل المطلوب)
            else {

                $user = $existingUser;

                $this->userRepository->generateAndSendOtp($user);


                throw new ResendVerificationException('User exists but is not verified. Verification code re-sent.');
            }
        }

        $citizenRole = Role::where('name', 'citizen')->first();

        if (!$citizenRole) {
            throw new \Exception('System error: Citizen role configuration missing.');
        }

        $userData = array_merge($data, [
            'role_id' => $citizenRole->id,

        ]);

        $user = $this->userRepository->create($userData);

        $this->userRepository->generateAndSendOtp($user);

        return $user;
    }

    // 2--
    public function authenticate(string $email, string $password): array
    {
        
        $throttleKey = Str::lower($email) . '|' . request()->ip();

        
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) { 
            $seconds = RateLimiter::availableIn($throttleKey);
            throw new \Exception("You have exceeded the maximum number of failed login attempts. Your account has been temporarily locked. Please try again in $seconds seconds.");
        }


        $user = $this->userRepository->findByEmail($email);


        if (!$user || !Hash::check($password, $user->password)) {
            
            RateLimiter::hit($throttleKey, 600);

            
            event(new \Illuminate\Auth\Events\Failed('sanctum', $user, [
                'email' => $email,
            ]));

            throw ValidationException::withMessages(['email' => ['بيانات الاعتماد غير صحيحة.']]);
        }
       
        RateLimiter::clear($throttleKey);

        if (!$user->is_verified) {
            throw new AccountNotVerifiedException('Your account is not verified.');
        }

        $token = $this->userRepository->createAuthToken($user);


        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function verifyUserAndGenerateToken(int $userId, string $otp): array
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {

            throw ValidationException::withMessages(['user_id' => ['User not found.']]);
        }

        if ($this->userRepository->verifyOtp($user, $otp)) {

            $token = $this->userRepository->createAuthToken($user);

            if (!$user->is_verified) {
                $this->userRepository->updateVerificationStatus($user, true);
            }

            return [
                'user' => $user,
                'token' => $token,
            ];
        }

        throw ValidationException::withMessages(['otp' => ['Invalid or expired verification code.']]);
    }
}
