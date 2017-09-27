<?php
namespace Colibri\tests\Pattern;

use Colibri\tests\Pattern\sample\SomeHelper;
use Colibri\tests\TestCase;

/**
 * Tests for Class HelperTest
 *
 * @coversDefaultClass \Colibri\Pattern\Helper
 */
class HelperTest extends TestCase
{
    /**
     * @expectedException \Error
     *
     * @covers ::__construct
     */
    public function testCantNew()
    {
        new SomeHelper();
    }

    /**
     * @expectedException \Error
     */
    public function testCantNewStaticInsideTheClass()
    {
        SomeHelper::tryGetInstanceStatic();
    }

    /**
     * @expectedException \Error
     */
    public function testCantNewSelfInsideTheClass()
    {
        SomeHelper::tryGetInstanceSelf();
    }
}
