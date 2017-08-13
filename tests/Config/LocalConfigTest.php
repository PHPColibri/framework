<?php
namespace Colibri\Tests\Config;

use Colibri\Config\Config;
use Colibri\Config\LocalConfig;
use PHPUnit\Framework\TestCase;

class LocalConfigTest extends TestCase
{
    public function setUp()
    {
        Config::setBaseDir(__DIR__ . '/sample');
    }

    /**
     * @covers \Colibri\Config\LocalConfig::getBaseDir
     */
    public function testGetBaseDir()
    {
        $this->assertEquals(realpath(__DIR__ . '/sample') . '/local', LocalConfig::getBaseDir());
    }
}
