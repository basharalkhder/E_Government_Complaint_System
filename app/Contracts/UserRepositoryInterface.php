<?php

namespace App\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    
    public function create(array $data): User;

    
    public function findByEmail(string $email): ?User;
    
    public function createAuthToken(User $user, string $name = 'api_token'): string;

    /**
     * جلب مستخدم بواسطة المعرف (ID)
     * @throws ModelNotFoundException
     */
    public function find(int $id): ?User;

    /**
     * التحقق من رمز OTP، وتفعيل المستخدم عند النجاح.
     */
    public function verifyOtp(User $user, int $otp): bool;

    /**
     * توليد رمز OTP، تخزينه في الكاش، وإرساله عبر البريد الإلكتروني.
     */
    public function generateAndSendOtp(User $user): void; 
    
    // ...
}