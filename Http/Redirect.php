<?php
namespace Colibri\Http;

use Colibri\Pattern\Helper;

/**
 * Redirect helper.
 */
class Redirect extends Helper
{
    /**
     * Imediatly redirects to url.
     *
     * @warning @calls exit;
     * @param string $url url to redirect to
     */
    public static function to($url)
    {
        header('Location: ' . $url);
        exit;
    }
}
