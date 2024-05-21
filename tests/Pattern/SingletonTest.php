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
    public function testCantNew()
    {
        $this->expectException(\Error::class);

        /* @noinspection Annotator */
        new SomeSingleton();
    }

    public function testCantClone()
    {
        $this->expectException(\Error::class);
        $instance = SomeSingleton::getInstance();
        /* @noinspection PhpExpressionResultUnusedInspection */
        clone $instance;
    }

    public function testCantWakeup()
    {
        $this->expectException(\BadMethodCallException::class);
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
        $instance2 = SomeSingleton::getInstance();
        self::assertTrue($instance === $instance2);
        self::assertTrue(spl_object_id($instance) === spl_object_id($instance2));
    }
}
