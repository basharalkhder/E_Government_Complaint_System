<?php



namespace App\Exceptions;

use Exception;

class AccountNotVerifiedException extends Exception
{
    protected $message = 'Your account is not verified. Please check your email for the verification link or OTP.';
    protected $code = 403;
}
