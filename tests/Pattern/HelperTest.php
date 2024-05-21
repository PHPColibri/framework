<?php
namespace Colibri\tests\Pattern;

use Colibri\tests\Pattern\sample\SomeHelper;
use Colibri\tests\TestCase;

/**
 * Tests for Class HelperTest.
 *
 * @coversDefaultClass \Colibri\Pattern\Helper
 */
class HelperTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testCantNew()
    {
        $this->expectException(\Error::class);
        /* @noinspection Annotator */
        new SomeHelper();
    }

    public function testCantNewStaticInsideTheClass()
    {
        $this->expectException(\Error::class);
        SomeHelper::tryGetInstanceStatic();
    }

    public function testCantNewSelfInsideTheClass()
    {
        $this->expectException(\Error::class);
        SomeHelper::tryGetInstanceSelf();
    }
}
