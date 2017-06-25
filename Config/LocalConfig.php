<?php
namespace Colibri\Config;

/**
 * Ability to overwrite config values with local environment ones.
 */
class LocalConfig extends Config
{
    /**
     * If no file in `local` dir, so just empty overwrites array.
     *
     * @param string $name
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected static function load($name)
    {
        return static::exists($name)
            ? parent::load($name)
            : [];
    }

    /**
     * Just override base dir (with `.../local`).
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function getBaseDir()
    {
        return parent::getBaseDir() . '/local';
    }
}
