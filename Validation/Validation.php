<?php
namespace Colibri\Validation;

use Colibri\Base\PropertyAccess;
use Colibri\Util\Arr;
use Colibri\Util\Str;

/**
 * Organize Validation of your data.
 *
 * @property array $errors array of validation errors.
 */
class Validation extends PropertyAccess
{
    /** @var string */
    public static $requiredMessage = 'поле \'%s\' является обязательным для заполнения.';
    /** @var string */
    public static $minLengthMessage = 'поле \'%s\' должно быть не меньше %d символов.';
    /** @var string */
    public static $maxLengthMessage = 'поле \'%s\' не должно быть больше %d символов.';
    /** @var string */
    public static $regexMessage = 'поле \'%s\' не удовлетворяет условию.';
    /** @var string */
    public static $isIntGt0Message = 'поле \'%s\' должно быть целым числом больше 0.';
    /** @var string */
    public static $isJSONMessage = 'поле \'%s\' должно быть строкой в формате JSON.';
    /** @var string */
    public static $isEmailMessage = 'поле \'%s\' должно содержать существующий почтовый ящик.';
    /** @var string */
    public static $isEqualMessage = 'поля \'%s\' должны быть одинаковыми.';

    /**
     * @var array occurred validation errors
     */
    protected $_errors = [];
    /**
     * @var array scope of data to validate
     */
    protected $scope = [];

    /**
     * Validation constructor.
     *
     * @param array $scope initial scope of data to validate
     */
    public function __construct(array $scope = null)
    {
        if ($scope !== null) {
            $this->scope = $scope;
        }
    }

    /**
     * Adds additional data to existing scope.
     *
     * @param array $scope
     *
     * @return $this
     */
    public function extendScope(array $scope)
    {
        $this->scope = array_merge($this->scope, $scope);

        return $this;
    }

    /**
     * Resets scope with new one & resets the errors.
     *
     * @param array $scope
     *
     * @return $this
     */
    public function setScope(array $scope)
    {
        $this->scope   = $scope;
        $this->_errors = [];

        return $this;
    }

    /**
     * Adds specified error $message for $key.
     *
     * @param $key
     * @param $message
     */
    public function addError($key, $message)
    {
        $this->_errors[$key] = $message;
    }

    /**
     * 'Required' validation rule. Checks if specified by $key data exists in scope.
     *
     * @param string|array $key
     * @param string       $message
     *
     * @return $this
     */
    public function required($key, $message = null)
    {
        if (is_array($key)) {
            foreach ($key as $name) {
                $this->required($name, $message);
            }
        } else {
            if ( ! (isset($this->scope[$key]) && ! empty($this->scope[$key]))) {
                $this->_errors[$key] = sprintf($message !== null ? $message : self::$requiredMessage, $key);
            }
        }

        return $this;
    }

    /**
     * 'MinLength' validation rule. Checks that specified by $key data not shorter than $minLength.
     *
     * @param string|array $key
     * @param int|array    $minLength
     * @param string       $message
     *
     * @return $this
     */
    public function minLength($key, $minLength, $message = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $name) {
                $this->minLength($name, is_array($minLength) ? $minLength[$k] : $minLength, $message);
            }
        } else {
            if (isset($this->scope[$key]) && mb_strlen($this->scope[$key]) < $minLength) {
                $this->_errors[$key] = sprintf($message !== null ? $message : self::$minLengthMessage, $key, $minLength);
            }
        }

        return $this;
    }

    /**
     * 'MaxLength' validation rule. Checks that specified by $key data not longer than $maxLength.
     *
     * @param string|array $key
     * @param int|array    $maxLength
     * @param string       $message
     *
     * @return $this
     */
    public function maxLength($key, $maxLength, $message = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $name) {
                $this->maxLength($name, is_array($maxLength) ? $maxLength[$k] : $maxLength, $message);
            }
        } else {
            if (isset($this->scope[$key]) && mb_strlen($this->scope[$key]) > $maxLength) {
                $this->_errors[$key] = sprintf($message !== null ? $message : self::$maxLengthMessage, $key, $maxLength);
            }
        }

        return $this;
    }

    /**
     * 'Regex' validation rule. Checks that specified by $key data matches to $pattern.
     *
     * @param string|array $key
     * @param string       $pattern
     * @param string       $message
     *
     * @return $this
     */
    public function regex($key, $pattern, $message = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $name) {
                $this->regex($name, is_array($pattern) ? $pattern[$k] : $pattern, $message);
            }
        } else {
            if (isset($this->scope[$key]) && ! (bool)preg_match($pattern, $this->scope[$key])) {
                $this->_errors[$key] = sprintf($message !== null ? $message : self::$regexMessage, $key);
            }
        }

        return $this;
    }

    /**
     * 'IsIntGt0' validation rule. Checks that specified by $key data is integer and greater than zero.
     *
     * @param string|array $key
     * @param string       $message
     *
     * @return $this
     */
    public function isIntGt0($key, $message = null)
    {
        if (is_array($key)) {
            foreach ($key as $name) {
                $this->isIntGt0($name, $message);
            }
        } else {
            if (isset($this->scope[$key]) && ! (Str::isInt($this->scope[$key]) && ((int)$this->scope[$key]) > 0)) {
                $this->_errors[$key] = sprintf($message !== null ? $message : self::$isIntGt0Message, $key);
            }
        }

        return $this;
    }

    /**
     * 'IsJSON' validation rule. Checks that specified by $key data stores a JSON string.
     *
     * @param string|array $key
     * @param string       $message
     *
     * @return $this
     */
    public function isJSON($key, $message = null)
    {
        if (is_array($key)) {
            foreach ($key as $name) {
                $this->isJSON($name, $message);
            }
        } else {
            if (isset($this->scope[$key]) && ! Str::isJSON($this->scope[$key])) {
                $this->_errors[$key] = sprintf($message !== null ? $message : self::$isJSONMessage, $key);
            }
        }

        return $this;
    }

    /**
     * 'IsEmail' validation rule. Checks that specified by $key data stores a string with email.
     *
     * @param string|array $key
     * @param string       $message
     *
     * @return $this
     */
    public function isEmail($key, $message = null)
    {
        if (is_array($key)) {
            foreach ($key as $name) {
                $this->isEmail($name, $message);
            }
        } else {
            if (isset($this->scope[$key]) && ! Str::isEmail($this->scope[$key])) {
                $this->_errors[$key] = sprintf($message !== null ? $message : self::$isEmailMessage, $key);
            }
        }

        return $this;
    }

    /**
     * 'IsEqual' validation rule. Checks that specified by $keys values are equal.
     *
     * @param array  $keys
     * @param string $message
     *
     * @return $this
     */
    public function isEqual(array $keys, $message = null)
    {
        $existingKey = null;
        foreach ($keys as $key) {
            if (isset($this->scope[$key])) {
                $existingKey = $key;
                break;
            }
        }
        if ($existingKey === null) {
            return $this;
        }

        foreach ($keys as $key) {
            if (isset($this->scope[$key]) && $this->scope[$key] != $this->scope[$existingKey]) {
                $keysList            = implode("', '", $keys);
                $this->_errors[$key] = sprintf($message !== null ? $message : self::$isEqualMessage, $keysList);
            }
        }

        return $this;
    }

    /**
     * 'Is' validation rule. Custom rule specified by $checkFunc().
     *
     * @param callable     $checkFunc
     * @param string|array $key
     * @param string       $message
     *
     * @return static
     */
    public function is($checkFunc, $key, $message)
    {
        if (is_array($key)) {
            foreach ($key as $name) {
                $this->is($checkFunc, $name, $message);
            }
        } else {
            if (isset($this->scope[$key]) && ! call_user_func($checkFunc, $this->scope[$key])) {
                $this->_errors[$key] = sprintf($message, $key);
            }
        }

        return $this;
    }

    /**
     * 'Is' validation rule. Custom rule specified by $checkFunc(). Checks that data NOT satisfies to rule.
     *
     * @param callable $checkFunc
     * @param string   $key
     * @param string   $message
     *
     * @return $this
     */
    public function isNot($checkFunc, $key, $message)
    {
        if (is_array($key)) {
            foreach ($key as $name) {
                $this->isNot($checkFunc, $name, $message);
            }
        } else {
            if (isset($this->scope[$key]) && call_user_func($checkFunc, $this->scope[$key])) {
                $this->_errors[$key] = sprintf($message, $key);
            }
        }

        return $this;
    }

    /**
     * Checks if scope data is valid or not.
     *
     * @return bool
     */
    public function valid()
    {
        return ! (bool)count($this->_errors);
    }

    /**
     * Calls $callback if scope is valid.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function ifIsValid(\Closure $callback)
    {
        if ($this->valid()) {
            $callback($this->scope);
        }

        return $this;
    }

    /**
     * Calls $callback if scope is NOT valid.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function ifNotValid(\Closure $callback)
    {
        if ( ! $this->valid()) {
            $callback($this->_errors);
        }

        return $this;
    }

    /**
     * Validates the data scope.
     *
     * @throws ValidationException
     *
     * @return $this
     */
    public function validate()
    {
        $this->ifNotValid(function (array $errors) {
            throw new ValidationException($errors);
        });

        return $this;
    }

    /**
     * Creates new Validation instance with $scope injected.
     *
     * @param array $scope
     *
     * @return static
     */
    public static function forScope(array $scope)
    {
        return new static($scope);
    }

    /**
     * Gets from $_POST only needed keys and use new as scope.
     * Creates new Validation instance with this scope.
     *
     * @param array $keys list of keys in $_POST array that must be validates
     *
     * @return Validation
     */
    public static function forPostOnly(array $keys)
    {
        return static::forScope(Arr::only($_POST, $keys));
    }
}
