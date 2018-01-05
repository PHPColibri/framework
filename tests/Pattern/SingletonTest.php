<?php
namespace Colibri\tests\Pattern;

use Colibri\tests\Pattern\sample\SomeSingleton;
use PHPUnit\Framework\TestCase;

/**
 * Test for Singleton class.
 *
 * @coversDefaultClass \Colibri\Pattern\Singleton
 */
class SingletonTest extends TestCase
{
    /**
     * @expectedException \Error
     */
    public function testCantNew()
    {
        /** @noinspection Annotator */
        new SomeSingleton();
    }

    /**
     * @expectedException \Error
     */
    public function testCantClone()
    {
        $instance = SomeSingleton::getInstance();
        /* @noinspection PhpExpressionResultUnusedInspection */
        clone $instance;
    }

    /**
     * @expectedException \PHPUnit\Framework\Error\Warning
     */
    public function testCantWakeup()
    {
        $instance   = SomeSingleton::getInstance();
        $serialized = serialize($instance);
        unserialize($serialized);
    }

    /**
     * @throws \PHPUnit\Framework\Exception
     * @covers ::__construct
     * @covers ::getInstance
     */
    public function testGetInstance()
    {
        $instance = SomeSingleton::getInstance();
        self::assertInstanceOf(SomeSingleton::class, $instance);
        self::assertAttributeInstanceOf(SomeSingleton::class, 'instance', SomeSingleton::class);
        self::assertTrue($instance === self::getStaticAttribute(SomeSingleton::class, 'instance'));
        $instance2 = SomeSingleton::getInstance();
        self::assertTrue($instance === $instance2);
    }
}
