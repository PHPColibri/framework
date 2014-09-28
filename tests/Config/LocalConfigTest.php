<?php
namespace Colibri\Tests\Config;

use Colibri\Config\Config;
use Colibri\Config\LocalConfig;

class LocalConfigTest extends \PHPUnit_Framework_TestCase
{
	public function __construct()
	{
		Config::setBaseDir(__DIR__ . '/sample');
	}

    /**
     * @covers Colibri\Config\LocalConfig::getBaseDir
     */
    public function testGetBaseDir()
    {
	    $this->assertEquals(realpath(__DIR__ . '/sample') . '/local', LocalConfig::getBaseDir());
    }
}
