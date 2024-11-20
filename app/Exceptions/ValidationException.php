<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected $errors;

    public function __construct($message, $errors = [], $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => $this->errors,
        ], $this->code);
    }
}
