<?php
namespace Colibri\tests\Util;

use Colibri\Util\Arr;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Colibri\Util\Arr.
 *
 * @coversDefaultClass \Colibri\Util\Arr
 */
class ArrTest extends TestCase
{
    /**
     * @return array
     */
    public function overwriteDataProvider()
    {
        return [
            [
                ['k1' => 'value1', 'k2' => 'value2'],
                ['k2' => 'v2'],
                ['k1' => 'value1', 'k2' => 'v2'],
            ],
            [
                ['k1' => 'value1', 'k2' => ['k2.1' => 'v2.1', 'k2.2' => 'v2.2']],
                ['k2' => ['k2.2' => 'some new value']],
                ['k1' => 'value1', 'k2' => ['k2.1' => 'v2.1', 'k2.2' => 'some new value']],
            ],
        ];
    }

    /**
     * @covers       \Colibri\Util\Arr::overwrite
     * @dataProvider overwriteDataProvider
     *
     * @param array $original
     * @param array $overwriteWith
     * @param array $expectedResult
     */
    public function testOverwrite(array $original, array $overwriteWith, array $expectedResult)
    {
        $result = Arr::overwrite($original, $overwriteWith);
        self::assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            ['k1.k11', 'value_11'],
            ['k1.k12.k121', 121],
        ];
    }

    /**
     * @dataProvider getDataProvider
     * @covers       \Colibri\Util\Arr::get
     *
     * @param string $key
     * @param mixed  $expectedValue
     */
    public function testGet($key, $expectedValue)
    {
        static $array = [
            'k1' => [
                'k11' => 'value_11',
                'k12' => [
                    'k121' => 121,
                ],
            ],
        ];
        $value = Arr::get($array, $key);
        self::assertEquals($expectedValue, $value);
    }

    /**
     * @return array
     */
    public function containsDataProvider()
    {
        return [
            [[111, 222, 333], 333, true],
            [[111, 222, 333], 444, false],
            [[true, false, false], true, true],
            [[false, false, false], true, false],
            [[false, true, true], false, true],
            [[true, true, true], false, false],
            [['aaa', 'bbb', 'ccc'], 'bbb', true],
            [['aaa', 'bbb', 'ccc'], 'ddd', false],
            [[.012, '', .013], .012, true],
        ];
    }

    /**
     * @dataProvider containsDataProvider
     * @covers ::contains()
     *
     * @param array $array
     * @param mixed $value
     * @param bool  $expected
     */
    public function testContains($array, $value, $expected)
    {
        self::assertEquals($expected, Arr::contains($array, $value));
    }
}
