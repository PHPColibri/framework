<?php
namespace Colibri\Util;

use Colibri\Pattern\Helper;

/**
 * Enum-like class. Specify possible values with const-ants.
 */
abstract class Enum extends Helper
{
    /**
     * @return array
     */
    public static function getValidValues()
    {
        return static::getConstList();
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function isValid($value)
    {
        return in_array($value, static::getValidValues(), true);
    }

    /**
     * @return array
     */
    protected static function getConstList()
    {
        return (new \ReflectionClass(static::class))->getConstants();
    }
}
