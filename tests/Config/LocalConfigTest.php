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
    public function setUp(): void
    {
        Config::setBaseDir(__DIR__ . '/sample');
    }

    /**
     * @covers \Colibri\Config\LocalConfig::getBaseDir
     *
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testGetBaseDir()
    {
        static::assertEquals(realpath(__DIR__ . '/sample') . '/local', LocalConfig::getBaseDir());
    }
}
