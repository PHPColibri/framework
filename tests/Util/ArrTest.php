<?php

namespace Colibri\Tests\Util;

use Colibri\Util\Arr;
use PHPUnit_Framework_TestCase;

class ArrTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @return array
	 */
	public function overwriteDataProvider()
	{
		return [
			[
				[
					"name" => "John",
					"surname" => "Johnson",
					"age" => 35,
				],
				[
					"name" => "Pamella",
					"age" => 19,
				],
				[
					"name" => "Pamella",
					"surname" => "Johnson",
					"age" => 19
				],
			],
			[
				[
					"files" => [
						"logo.png",
						"logger.txt",
					],
					"framework" => "Colibri",
				],
				[
					"files" => "RickRoll.webm",
				],
				[
					"files" => "RickRoll.webm",
					"framework" => "Colibri"
				]
			]
		];
	}

	/**
	 * Test method Arr::overwrite.
	 *
	 * @param array $array
	 * @param array $writeValues
	 * @param array $expectedResult
	 *
	 * @dataProvider overwriteDataProvider
	 */
	public function testOverwrite(array $array, array $writeValues, array $expectedResult)
	{
		$result = Arr::overwrite($array, $writeValues);

		PHPUnit_Framework_TestCase::assertEquals($expectedResult, $result);
	}

	/**
	 * @return array
	 */
	public function getDataProvider()
	{
		return [
			[
				[
					"name" => "John",
					"work" => [
						"city" => "Moscow",
						"country" => "Russia",
						"company" => [
							"name" => "Star Env",
							"address" => "New Era str. 3",
						],
					],
				],
				"name",
				"--not found--",
				"John",
			],
			[
				[
					"name" => "John",
					"work" => [
						"city" => "Moscow",
						"country" => "Russia",
						"company" => [
							"name" => "Star Env",
							"address" => "New Era str. 3",
						],
					],
				],
				"name.0",
				"--not found--",
				"--not found--",
			],
			[
				[
					"name" => "John",
					"work" => [
						"city" => "Moscow",
						"country" => "Russia",
						"company" => [
							"name" => "Star Env",
							"address" => "New Era str. 3",
						],
					],
				],
				"work",
				"--not found--",
				[
					"city" => "Moscow",
					"country" => "Russia",
					"company" => [
						"name" => "Star Env",
						"address" => "New Era str. 3",
					],
				],
			],
			[
				[
					"name" => "John",
					"work" => [
						"city" => "Moscow",
						"country" => "Russia",
						"company" => [
							"name" => "Star Env",
							"address" => "New Era str. 3",
						],
					],
				],
				"work.company.address",
				"--not found--",
				"New Era str. 3",
			],
			[
				[
					"name" => "John",
					"work" => [
						"city" => "Moscow",
						"country" => "Russia",
						"company" => [
							"name" => "Star Env",
							"address" => "New Era str. 3",
						],
					],
				],
				"work.company.address.error",
				"--not found--",
				"--not found--",
			],
		];
	}

	/**
	 * Test method Arr::get.
	 *
	 * @param array $array
	 * @param mixed $key
	 * @param mixed $default
	 * @param mixed $expectedResult
	 *
	 * @dataProvider getDataProvider
	 */
	public function testGet(array $array, $key, $default, $expectedResult)
	{
		$result = Arr::get($array, $key, $default);

		PHPUnit_Framework_TestCase::assertEquals($expectedResult, $result);
	}

	/**
	 * @return array
	 */
	public function setDataProvider()
	{
		return [
			[
				[
					"name" => "John",
					"work" => [
						"city" => "Moscow",
						"country" => "Russia",
						"company" => [
							"name" => "Star Env",
							"address" => "New Era str. 3",
						],
					],
				],
				"work.company.name",
				"test",
				[
					"name" => "John",
					"work" => [
						"city" => "Moscow",
						"country" => "Russia",
						"company" => [
							"name" => "test",
							"address" => "New Era str. 3",
						],
					],
				],
				[
					"name" => "John",
					"work" => [
						"city" => "Moscow",
						"country" => "Russia",
						"company" => [
							"name" => "test",
							"address" => "New Era str. 3",
						],
					],
				],
			],
		];
	}

	/**
	 * @param array $array
	 * @param mixed $key
	 * @param mixed $value
	 * @param mixed $expectedResult
	 * @param mixed $expectedArray
	 *
	 * @dataProvider setDataProvider
	 */
	public function testSet(array $array, $key, $value, $expectedResult, $expectedArray)
	{
		$result = Arr::set($array, $key, $value);

		PHPUnit_Framework_TestCase::assertEquals($expectedResult, $result);
		PHPUnit_Framework_TestCase::assertEquals($expectedArray, $array);
	}

	/**
	 * @return array
	 */
	public function removeDataProvider()
	{
		return [
			[
				[
					"name" => "John",
					"work" => [
						"city" => "Moscow",
						"country" => "Russia",
						"company" => [
							"name" => "Star Env",
							"address" => "New Era str. 3",
						],
					],
				],
				"work.company.name",
				"Star Env",
				[
					"name" => "John",
					"work" => [
						"city" => "Moscow",
						"country" => "Russia",
						"company" => [
							"address" => "New Era str. 3",
						],
					],
				],
			],
		];
	}

	/**
	 * Test method Arr::remove.
	 *
	 * @param array $array
	 * @param mixed $key
	 * @param mixed $expectedResult
	 * @param mixed $expectedArray
	 *
	 * @dataProvider removeDataProvider
	 */
	public function testRemove(array $array, $key, $expectedResult, $expectedArray)
	{
		$result = Arr::remove($array, $key);

		PHPUnit_Framework_TestCase::assertEquals($expectedResult, $result);
		PHPUnit_Framework_TestCase::assertEquals($expectedArray, $array);
	}

	/**
	 * @return array
	 */
	public function onlyDataProvider()
	{
		return [
			[
				[
					"name" => "John",
					"work" => [
						"city" => "Moscow",
						"country" => "Russia",
						"company" => [
							"name" => "Star Env",
							"address" => "New Era str. 3",
						],
					],
				],
				[
					"name"
				],
				[
					"name" => "John",
				],
			],
		];
	}

	/**
	 * Test method Arr::only.
	 *
	 * @param array $array
	 * @param array $keys
	 * @param $expectedResult
	 *
	 * @dataProvider onlyDataProvider
	 */
	public function testOnly(array $array, array $keys, $expectedResult)
	{
		$result = Arr::only($array, $keys);

		PHPUnit_Framework_TestCase::assertEquals($expectedResult, $result);
	}
}
