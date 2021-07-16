<?php

namespace App\Exceptions;

use Exception;

class TokenNotFoundException extends Exception
{
    protected $message = 'Token not found';

    public function __construct()
    {
        parent::__construct($this->message, 0);
    }

    public function __toString()
    {
        return $this->message;
    }
}
