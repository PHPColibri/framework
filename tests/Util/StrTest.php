<?php
namespace Colibri\tests\Util;

use Colibri\Util\Str;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Colibri\Util\Str
 */
class StrTest extends TestCase
{
    /**
     * @covers ::isEmail
     * @dataProvider invalidEmailProvider
     *
     * @param string $email
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testIsEmailNegative($email)
    {
        self::assertFalse(Str::isEmail($email));
    }

    /**
     * @return array
     */
    public function invalidEmailProvider()
    {
        return [
            ['invalidTest.test'],
            ['@ya.com.ru'],
            ['teststring'],
            ['88005553535@test'],
            ['test@colibri@test.ru'],
            ['test.@colibri.gmail'],
            ['.test@colibri.gmail'],
        ];
    }

    /**
     * @covers ::isEmail
     * @dataProvider validEmailProvider
     *
     * @param string $email
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testIsEmailPositive($email)
    {
        self::assertTrue(Str::isEmail($email));
    }

    /**
     * @return array
     */
    public function validEmailProvider()
    {
        return [
            ['test@ya.ru'],
            ['test-123@gmail.com'],
            ['test.test@yahoo.com.ru'],
            ['test@test.test.test.test'],
            ['88005553535@ya.ru'],
            ['xxxColibrixxx@gmail.com'],
        ];
    }

    /**
     * @covers ::random
     * @expectedException \Exception
     * @expectedExceptionMessage unknown random type
     */
    public function testRandomInvalidTypeException()
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        Str::random('invalid_type');
    }

    /**
     * @covers ::random
     * @dataProvider randomProvider
     *
     * @param string $type
     * @param int    $length
     * @param string $regexp
     *
     * @throws \Exception
     * @throws \PHPUnit\Framework\Exception
     */
    public function testRandom($type, $length, $regexp)
    {
        self::assertRegExp($regexp, Str::random($type, $length));
    }

    /**
     * @return array
     */
    public function randomProvider()
    {
        return [
            ['alnum', 2, '/[0-9a-zA-Z]{2}/'],
            ['alnum', 8, '/[0-9a-zA-Z]{8}/'],
            ['alnum', 15, '/[0-9a-zA-Z]{15}/'],
            ['numeric', 2, '/[0-9]{2}/'],
            ['numeric', 8, '/[0-9]{8}/'],
            ['numeric', 15, '/[0-9]{15}/'],
            ['nozero', 2, '/[1-9]{2}/'],
            ['nozero', 8, '/[1-9]{8}/'],
            ['nozero', 15, '/[1-9]{15}/'],
            ['unique', 2, '/[0-9a-f]{32}/'],
            ['unique', 8, '/[0-9a-f]{32}/'],
            ['unique', 15, '/[0-9a-f]{32}/'],
        ];
    }

    /**
     * @covers ::random
     * @dataProvider randomDefaultLengthProvider
     *
     * @param string $type
     * @param int    $expectedLength
     *
     * @throws \Exception
     */
    public function testRandomDefaultLength($type, $expectedLength)
    {
        self::assertEquals($expectedLength, mb_strlen(Str::random($type)));
    }

    /**
     * @return array
     */
    public function randomDefaultLengthProvider()
    {
        return [
            ['alnum', 8],
            ['numeric', 8],
            ['nozero', 8],
            ['unique', 32],
            ['guid', 36],
        ];
    }

    /**
     * @covers ::snake
     * @dataProvider snakeProvider
     *
     * @param string $stringSnakeCase
     * @param string $expectedString
     * @param string $delimiter
     */
    public function testSnake($stringSnakeCase, $expectedString, $delimiter = '_')
    {
        self::assertEquals($expectedString, Str::snake($stringSnakeCase, $delimiter));
    }

    /**
     * @return array
     */
    public function snakeProvider()
    {
        return [
            ['camelCase', 'camel_case'],
            ['snake_case', 'snake_case'],
            ['camelCAse1', 'camel_c_ase1'],
            ['PHPProfi', 'p_h_p_profi'],
            ['string', 'string'],
            ['spaced words string', 'spaced_words_string'],
            ['spaced words string', 'spaced!words!string', '!'],
            ['Spaced Words String', 'spaced_words_string',],
        ];
    }

    /**
     * @covers ::camel
     * @dataProvider camelProvider
     *
     * @param string $stringCamelCase
     * @param string $expectedString
     */
    public function testCamel($stringCamelCase, $expectedString)
    {
        self::assertEquals($expectedString, Str::camel($stringCamelCase));
    }

    /**
     * @return array
     */
    public function camelProvider()
    {
        return [
            ['camel_case', 'camelCase'],
            ['camel-case', 'camelCase'],
            ['camelCase', 'camelCase'],
            ['camel_c_ase1', 'camelCAse1'],
            ['camel-c-ase1', 'camelCAse1'],
            ['string', 'string'],
        ];
    }

    /**
     * @covers ::studly
     * @dataProvider studlyProvider
     *
     * @param string $stringCamelCase
     * @param string $expectedString
     */
    public function testStudly($stringCamelCase, $expectedString)
    {
        self::assertEquals($expectedString, Str::studly($stringCamelCase));
    }

    /**
     * @return array
     */
    public function studlyProvider()
    {
        return [
            ['camel_case', 'CamelCase'],
            ['camel-case', 'CamelCase'],
            ['camelCase', 'CamelCase'],
            ['camel_c_ase1', 'CamelCAse1'],
            ['string', 'String'],
        ];
    }

    /**
     * @covers ::isInt
     * @dataProvider isIntPositiveProvider
     *
     * @param string $string
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testIsIntPositive($string)
    {
        self::assertTrue(Str::isInt($string));
    }

    /**
     * @return array
     */
    public function isIntPositiveProvider()
    {
        return [
            ['11'],
            ['80'],
            ['8080'],
            ['-11'],
            ['0'],
        ];
    }

    /**
     * @covers ::isInt
     * @dataProvider isIntNegativeProvider
     *
     * @param string $string
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testIsIntNegative($string)
    {
        self::assertFalse(Str::isInt($string));
    }

    /**
     * @return array
     */
    public function isIntNegativeProvider()
    {
        return [
            ['1a1'],
            ['a12'],
            ['12.1'],
            ['11.'],
            ['-11.'],
            ['11-'],
            ['-0'],
            ['+0'],
        ];
    }

    /**
     * @covers ::beginsWith
     * @dataProvider beginsWithPositiveProvider
     *
     * @param string $sourceString
     * @param string $beginsWith
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testBeginsWithPositive($sourceString, $beginsWith)
    {
        self::assertTrue(Str::beginsWith($sourceString, $beginsWith));
    }

    /**
     * @return array
     */
    public function beginsWithPositiveProvider()
    {
        return [
            ['foobar', 'foo'],
            ['Colibri the best', 'Col'],
        ];
    }

    /**
     * @covers ::beginsWith
     * @dataProvider beginsWithNegativeProvider
     *
     * @param string $sourceString
     * @param string $beginsWith
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testBeginsWithNegative($sourceString, $beginsWith)
    {
        self::assertFalse(Str::beginsWith($sourceString, $beginsWith));
    }

    /**
     * @return array
     */
    public function beginsWithNegativeProvider()
    {
        return [
            ['foobar', 'bar'],
            ['Colibri the best', 'Laravel'],
        ];
    }

    /**
     * @covers ::endsWith
     * @dataProvider endsWithPositiveProvider
     *
     * @param string $sourceString
     * @param string $beginsWith
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testEndsWithPositive($sourceString, $beginsWith)
    {
        self::assertTrue(Str::endsWith($sourceString, $beginsWith));
    }

    /**
     * @return array
     */
    public function endsWithPositiveProvider()
    {
        return [
            ['foobar', 'bar'],
            ['Colibri the best', 'best'],
            ['Colibri the best', 'est'],
        ];
    }

    /**
     * @covers ::endsWith
     * @dataProvider endsWithNegativeProvider
     *
     * @param string $sourceString
     * @param string $beginsWith
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function testEndsWithNegative($sourceString, $beginsWith)
    {
        self::assertFalse(Str::endsWith($sourceString, $beginsWith));
    }

    /**
     * @return array
     */
    public function endsWithNegativeProvider()
    {
        return [
            ['foobar', 'foo'],
            ['Colibri the best', 'test'],
            ['Colibri the best', ''],
        ];
    }

    /**
     * @covers ::firstPart
     * @dataProvider firstPartProvider
     *
     * @param $sourceString
     * @param $delimiter
     * @param $expectedString
     */
    public function testFirstPart($sourceString, $delimiter, $expectedString)
    {
        self::assertEquals($expectedString, Str::firstPart($sourceString, $delimiter));
    }

    /**
     * @return array
     */
    public function firstPartProvider()
    {
        return [
            ['So much tests',     ' ', 'So'],
            ['aaa',               'a', ''],
            ['1daw2-sadf-sdfasd', '-', '1daw2'],
        ];
    }

    /**
     * @covers ::lastPart
     * @dataProvider lastPartProvider
     *
     * @param $sourceString
     * @param $delimiter
     * @param $expectedString
     */
    public function testLastPart($sourceString, $delimiter, $expectedString)
    {
        self::assertEquals($expectedString, Str::lastPart($sourceString, $delimiter));
    }

    /**
     * @return array
     */
    public function lastPartProvider()
    {
        return [
            ['So much tests',     ' ', 'tests'],
            ['aaa',               'a', ''],
            ['1daw2-sadf-sdfasd', '-', 'sdfasd'],
        ];
    }
}
