<?php
namespace Colibri\Http;

use Colibri\Pattern\Helper;

class Redirect extends Helper
{
    /**
     * @param $url
     */
    public static function to($url)
    {
        header('Location: ' . $url);
        exit;
    }
}