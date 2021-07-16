<?php

namespace App\Exceptions;

use Exception;

class InvalidTokenException extends Exception
{
    protected $message = 'Invalid token';

    public function __construct()
    {
        parent::__construct($this->message, 0);
    }

    public function __toString()
    {
        return $this->message;
    }
}
