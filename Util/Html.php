<?php
namespace Colibri\Util;

use Colibri\Pattern\Helper;

/**
 * Some Html manipulations helpers.
 */
class Html extends Helper
{
    /**
     * Escapes all html chars.
     *
     * @param $value
     *
     * @return string
     */
    static public function e($value)
    {
        return htmlspecialchars($value, ENT_QUOTES);
    }
}
