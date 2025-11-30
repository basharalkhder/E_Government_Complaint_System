<?php



namespace App\Exceptions;

use Exception;

class AccountNotFoundException extends Exception
{
    protected $message = 'This account does not exist. Please create a new account.';
    protected $code = 404;
}
