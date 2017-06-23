<?php
namespace Colibri\Config;

/**
 * Description of LocalConfig
 *
 * @author Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 */
class LocalConfig extends Config
{
    protected static function load($name)
    {
        return static::exists($name)
            ? parent::load($name)
            : [];
    }

    public static function getBaseDir()
    {
        return parent::getBaseDir() . '/local';
    }
}
