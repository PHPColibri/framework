<?php
namespace Colibri\Validation;

use Exception;

/**
 * Exception for Validation rules fails.
 * Stores Validation errors for retrieve them in catch block.
 */
class ValidationException extends Exception
{
    /**
     * @var array stored validation errors
     */
    protected $errors = [];

    /**
     * Create new instance.
     *
     * @param array     $errors
     * @param Exception $previous
     */
    public function __construct(array $errors, Exception $previous = null)
    {
        parent::__construct('', 0, $previous);
        $this->errors = $errors;
    }

    /**
     * Retrieve validation errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
