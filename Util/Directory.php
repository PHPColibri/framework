<?php
namespace Colibri\Util;

use Colibri\Pattern\Helper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Directory extends Helper
{
    /**
     * @param string $path
     * @param int    $flags
     *
     * @return \RecursiveIteratorIterator|\RecursiveDirectoryIterator[]
     */
    public static function iterator($path, $flags = RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::CURRENT_AS_SELF)
    {
        return new RecursiveIteratorIterator(new RecursiveDirectoryIterator(\realpath($path), $flags));
    }
}
