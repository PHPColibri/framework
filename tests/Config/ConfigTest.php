<?php
namespace Colibri\tests\Config;

use Colibri\Config\Config;
use Colibri\tests\TestCase;

/**
 * Test Config class.
 */
class ConfigTest extends TestCase
{
    /**
     * @covers \Colibri\Config\Config::setBaseDir
     * @expectedException \InvalidArgumentException
     */
    public function testSetInvalidBaseDir()
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        Config::setBaseDir('/a/b/c');
    }

    /**
     * @covers \Colibri\Config\Config::setBaseDir
     * @depends testSetInvalidBaseDir
     *
     * @throws \Exception
     */
    public function testSetBaseDir()
    {
        Config::setBaseDir(__DIR__ . '/sample');
        static::assertAttributeEquals(realpath(__DIR__ . '/sample'), 'baseDir', 'Colibri\Config\Config');
    }

    /**
     * @covers  \Colibri\Config\Config::getBaseDir
     * @depends testSetBaseDir
     *
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testGetBaseDir()
    {
        static::assertEquals(realpath(__DIR__ . '/sample'), Config::getBaseDir());
    }

    /**
     * @covers  \Colibri\Config\Config::exists
     * @depends testSetBaseDir
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \InvalidArgumentException
     */
    public function testExists()
    {
        static::assertTrue(Config::exists('sample'));
        static::assertFalse(Config::exists('no-file'));
    }

    /**
     * @covers  \Colibri\Config\Config::getOrEmpty
     * @depends testSetBaseDir
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     */
    public function testGetOrEmpty()
    {
        static::assertThat(
            Config::getOrEmpty('no-file'),
            static::logicalAnd(
                static::isType('array'),
                static::isEmpty()
            )
        );
        static::assertThat(
            Config::getOrEmpty('sample'),
            static::logicalAnd(
                static::isType('array'),
                static::logicalNot(static::isEmpty())
            )
        );
    }

    /**
     * @covers  \Colibri\Config\Config::get
     * @covers  \Colibri\Config\LocalConfig::load
     * @depends testSetBaseDir
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $config = Config::get('sample');
        static::assertArrayHasKey('test', $config);
        static::assertArrayHasKey('anotherSetting', $config);
        static::assertEquals('localValue', $config['anotherSetting']);
    }

    /**
     * @covers  \Colibri\Config\Config::__callStatic
     * @depends testSetBaseDir
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function test__callStatic()
    {
        /* @noinspection PhpUndefinedMethodInspection */
        static::assertEquals(true, Config::sample('test'));
        /* @noinspection PhpUndefinedMethodInspection */
        static::assertEquals('inArrValue', Config::sample('arraySetting.arrSettingKey'));
    }
}
