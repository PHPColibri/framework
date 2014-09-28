<?php
namespace Colibri\Tests\Config;

use Colibri\Config\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
//	    $rc = new \ReflectionClass('Colibri\Config\Config');
//	    $rc->setStaticPropertyValue('baseDir', null);
//	    $rc->setStaticPropertyValue('allLoadedConfigs', array());
	}

	/**
	 * @covers Colibri\Config\Config::setBaseDir
	 * @throws \Exception
	 */
	public function testSetBaseDir()
	{
		Config::setBaseDir(__DIR__ . '/sample');
		$this->assertAttributeEquals(realpath(__DIR__ . '/sample'), 'baseDir', 'Colibri\Config\Config');
	}

	/**
	 * @covers Colibri\Config\Config::getBaseDir
	 * @depends testSetBaseDir
	 */
	public function testGetBaseDir()
	{
		$this->assertEquals(realpath(__DIR__ . '/sample'), Config::getBaseDir());
	}

	/**
	 * @covers Colibri\Config\Config::exists
	 * @depends testSetBaseDir
	 */
	public function testExists()
	{
		$this->assertTrue(Config::exists('sample'));
		$this->assertFalse(Config::exists('no-file'));
	}

	/**
	 * @covers Colibri\Config\Config::getOrEmpty
	 * @depends testSetBaseDir
	 */
	public function testGetOrEmpty()
	{
		$this->assertThat(
			Config::getOrEmpty('no-file'),
			$this->logicalAnd(
				$this->isType('array'),
				$this->isEmpty()
			)
		);
		$this->assertThat(
			Config::getOrEmpty('sample'),
			$this->logicalAnd(
				$this->isType('array'),
				$this->logicalNot($this->isEmpty())
			)
		);
	}

	/**
	 * @covers Colibri\Config\Config::get
	 * @depends testSetBaseDir
	 * @throws \Exception
	 */
	public function testGet()
	{
		$config = Config::get('sample');
		$this->assertArrayHasKey('test', $config);
		$this->assertArrayHasKey('anotherSetting', $config);
		$this->assertEquals('localValue', $config['anotherSetting']);
	}

	/**
	 * @covers Colibri\Config\Config::__callStatic
	 * @depends testSetBaseDir
	 */
	public function test__callStatic()
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$this->assertEquals(true, Config::sample('test'));
		/** @noinspection PhpUndefinedMethodInspection */
		$this->assertEquals('inArrValue', Config::sample('arraySetting.arrSettingKey'));
	}
}
