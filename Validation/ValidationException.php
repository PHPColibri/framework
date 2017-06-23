<?php
namespace Colibri\Validation;

use Exception;

class ValidationException extends Exception
{
    protected $errors = [];

    /**
     * @param array     $errors
     * @param Exception $previous
     */
    public function __construct(array $errors, Exception $previous = null)
    {
        parent::__construct("", 0, $previous);
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
