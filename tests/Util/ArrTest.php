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
     * @param mixed              $key
     * @param array|\ArrayAccess $array
     * @param string             $message
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public static function assertArrayHasKey($key, $array, $message = '')
    {
        $nestedKeys = explode('.', $key);
        $a          = &$array;
        foreach ($nestedKeys as $k) {
            parent::assertArrayHasKey($k, $a, $message);
            $a = &$a[$k];
        }
    }

    // ----------------------------------------------------------------------------

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
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testOverwrite(array $original, array $overwriteWith, array $expectedResult)
    {
        $result = Arr::overwrite($original, $overwriteWith);
        self::assertEquals($expectedResult, $result);
    }

    // ----------------------------------

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
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
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

    // ----------------------------------

    /**
     * @return array
     */
    public function setDataProvider()
    {
        return [
            ['key1', 'new value1'],
            ['key2', 'value2'],
            ['nested.n1', 777],
            ['nested.n2', 'new nested value 2'],
            ['k1.k11', 'value_1.1'],
            ['k1.k12.k121', 121],
        ];
    }

    /**
     * @dataProvider setDataProvider
     * @covers       \Colibri\Util\Arr::set
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function testSet($key, $value)
    {
        static $array = [
            'key1'   => 'value1',
            'nested' => [
                'n1' => 123,
                'n2' => 'nested value 2',
            ],
        ];

        $result = Arr::set($array, $key, $value);

        self::assertArrayHasKey($key, $result);
        self::assertEquals($value, Arr::get($result, $key));
        self::assertEquals($value, Arr::get($array, $key));
    }

    // ----------------------------------

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
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testContains($array, $value, $expected)
    {
        self::assertEquals($expected, Arr::contains($array, $value));
    }

    // ----------------------------------

    /**
     * @return array
     */
    public function lastDataProvider(): array
    {
        return [
            [[111, 222, 333], 1, [333]],
            [[111, 222, 333], 2, [222, 333]],
            [[true, true, false], 1, [false]],
            [[false, false, true], 1, [true]],
            [['aaa', 'bbb', 'ccc'], 2, ['bbb', 'ccc']],
            [['aaa', 'bbb', 'ccc'], 1, ['ccc']],
            [[.012, '', .013], 1, [.013]],
            [[.012, '', .013], 2, ['', .013]],
            [[.012, '', .013], 3, [.012, '', .013]],
        ];
    }

    /**
     * @dataProvider lastDataProvider
     * @covers ::last()
     *
     * @param array $array
     * @param int   $count
     * @param array $expected
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testLast(array $array, int $count, array $expected)
    {
        self::assertEquals($expected, Arr::last($array, $count));
    }
}
