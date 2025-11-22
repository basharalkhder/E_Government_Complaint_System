<?php

namespace App\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    
    public function create(array $data): User;

    // متطلب "تسجيل الدخول" (البحث عن المستخدم)
    public function findByEmail(string $email): ?User;
    
    // لإصدار التوكن بعد المصادقة الناجحة
    public function createAuthToken(User $user, string $name = 'api_token'): string;
}