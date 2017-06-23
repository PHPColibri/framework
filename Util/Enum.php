<?php
namespace Colibri\Util;

use Colibri\Pattern\Helper;

/**
 * Enum-like class. Specify possible values with const-ants.
 */
abstract class Enum extends Helper
{

// @todo
//	/**
//	 * @var array cached valid values for classes
//	 */
//	protected static $validValues = [] или null;

    /**
     * @todo LLP: можно сделать "кеш", если это вообще хоть как-то ускорит (см. комменты)
     * @return array
     */
    public static function getValidValues()
    {
        return /* <isset(self::$validValues[get_called_class()])> или <static::$validValues!==null> */
            /* ? <self::$validValues[get_called_class()]> или <static::$validValues> : */
            static::getConstList();
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
        return (new \ReflectionClass(get_called_class()))->getConstants();
    }
}
