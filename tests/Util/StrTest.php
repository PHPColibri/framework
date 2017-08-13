<?php
namespace Colibri\Tests\Util;

use Colibri\Util\Str;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_TestCase;

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
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public function testIsEmailNegative($email)
    {
        $this->assertFalse(Str::isEmail($email));
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
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public function testIsEmailPositive($email)
    {
        $this->assertTrue(Str::isEmail($email));
    }

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
     * @throws \PHPUnit_Framework_Exception
     */
    public function testRandom($type, $length, $regexp)
    {
        $this->assertRegExp($regexp, Str::random($type, $length));
    }

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
        $this->assertEquals($expectedLength, mb_strlen(Str::random($type)));
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
     */
    public function testSnake($stringSnakeCase, $expectedString)
    {
        $this->assertEquals($expectedString, Str::snake($stringSnakeCase));
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
            ['string', 'string'],
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
        $this->assertEquals($expectedString, Str::camel($stringCamelCase));
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
        $this->assertEquals($expectedString, Str::studly($stringCamelCase));
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
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public function testIsIntPositive($string)
    {
        $this->assertTrue(Str::isInt($string));
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
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public function testIsIntNegative($string)
    {
        $this->assertFalse(Str::isInt($string));
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
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public function testBeginsWithPositive($sourceString, $beginsWith)
    {
        $this->assertTrue(Str::beginsWith($sourceString, $beginsWith));
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
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public function testBeginsWithNegative($sourceString, $beginsWith)
    {
        $this->assertFalse(Str::beginsWith($sourceString, $beginsWith));
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
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public function testEndsWithPositive($sourceString, $beginsWith)
    {
        $this->assertTrue(Str::endsWith($sourceString, $beginsWith));
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
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public function testEndsWithNegative($sourceString, $beginsWith)
    {
        $this->assertFalse(Str::endsWith($sourceString, $beginsWith));
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
}
