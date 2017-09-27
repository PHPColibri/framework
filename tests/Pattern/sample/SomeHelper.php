<?php
namespace Colibri\tests\Pattern\sample;

use Colibri\Pattern\Helper;

class SomeHelper extends Helper
{
    /**
     * @return static
     */
    public static function tryGetInstanceStatic()
    {
        return new static();
    }

    /**
     * @return SomeHelper
     */
    public static function tryGetInstanceSelf()
    {
        return new self();
    }
}
