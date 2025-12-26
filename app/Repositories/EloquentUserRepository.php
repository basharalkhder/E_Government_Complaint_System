<?php

namespace App\Repositories;

use App\Models\User;
use App\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Mail\VerificationCodeMail;

class EloquentUserRepository implements UserRepositoryInterface
{

    public function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
        ]);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function find(int $id): ?User
    {

        return User::find($id);
    }


    public function createAuthToken(User $user, string $name = 'api_token'): string
    {

        $user->tokens()->where('name', $name)->delete();
        return $user->createToken($name)->plainTextToken;
    }


    public function generateAndSendOtp(User $user): void
    {
        
        $otp = random_int(100000, 999999);
        $cacheKey = "otp:{$user->id}";

        Cache::put($cacheKey, $otp, now()->addMinutes(5));

        
        Mail::to($user->email)->send(new VerificationCodeMail($otp));
    }


    // public function sendOtpViaTelegram(User $user, int $otp)
    // {
    //     $token = env('TELEGRAM_BOT_TOKEN');
    //     $chatId = $user->telegram_chat_id;

    //     // إذا لم يكن لديه تلغرام، نرسل إيميل كخيار بديل (Fallback)
    //     if (!$chatId) {
    //         Mail::to($user->email)->send(new VerificationCodeMail($otp));
    //         return;
    //     }

    //     $message = "🔐 <b>نظام الشكاوى الحكومية</b>\n\n";
    //     $message .= "رمز التحقق الخاص بك هو: <code>$otp</code>\n";
    //     $message .= "صلاحية الرمز 5 دقائق.";

    //     Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
    //         'chat_id' => $chatId,
    //         'text' => $message,
    //         'parse_mode' => 'HTML'
    //     ]);
    // }

    public function verifyOtp(User $user, int $otp): bool
    {
        $cacheKey = "otp:{$user->id}";
        $storedOtp = Cache::get($cacheKey);

        if ($storedOtp && (int)$otp === (int)$storedOtp) {
            //  تطابق الرمز بنجاح
            $user->update(['is_verified' => true]);
            Cache::forget($cacheKey); // حذف الرمز من الكاش
            return true;
        }

        return false;
    }

    public function updateVerificationStatus(User $user, bool $status): bool
    {

        $user->is_verified = $status;

        if ($status === true) {
            $user->email_verified_at = now();
        } else {
            $user->email_verified_at = null;
        }

        return $user->save();
    }
}
