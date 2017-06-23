<?php
namespace Colibri\Pattern;

/**
 * Represent a helper pattern:
 *   not public __construct & __clone
 *
 * @author Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 */
abstract class Helper
{
    private function __construct()
    {
    }

    private function __clone()
    {
    }
}