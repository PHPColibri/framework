<?php
namespace Colibri\tests\Config;

use Colibri\Config\Config;
use Colibri\Config\LocalConfig;
use PHPUnit\Framework\TestCase;

/**
 * Test LocalConfigTest class.
 */
class LocalConfigTest extends TestCase
{
    /**
     * @throws \InvalidArgumentException
     */
    public function setUp()
    {
        Config::setBaseDir(__DIR__ . '/sample');
    }

    /**
     * @covers \Colibri\Config\LocalConfig::getBaseDir
     *
     * @throws \InvalidArgumentException
     */
    public function testGetBaseDir()
    {
        static::assertEquals(realpath(__DIR__ . '/sample') . '/local', LocalConfig::getBaseDir());
    }
}
