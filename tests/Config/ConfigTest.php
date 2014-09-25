<?php
namespace Colibri\Tests\Config;


use Colibri\Config\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

	public function testGet()
	{
		Config::setBaseDir(__DIR__);

		$this->assertArrayHasKey('test', Config::get('sample'));
	}
}
 