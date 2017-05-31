<?php
namespace Colibri\Tests\Util;

use Colibri\Util\Str;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass \Colibri\Util\Str
 */
class StrTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::isEmail
     * @dataProvider invalidEmailProvider
     *
     * @param string $email
     */
    public function testIsEmailNegative($email)
    {
        $this->assertFalse(Str::isEmail($email));
    }

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
     * @param string  $type
     * @param integer $length
     * @param string  $regexp
     */
    public function testRandom($type, $length, $regexp)
    {
        $this->assertRegExp($regexp, Str::random($type, $length));
    }

    public function randomProvider()
    {
        return [
            ['alnum',   2,  '/[0-9a-zA-Z]{2}/'],
            ['alnum',   8,  '/[0-9a-zA-Z]{8}/'],
            ['alnum',   15, '/[0-9a-zA-Z]{15}/'],
            ['numeric', 2,  '/[0-9]{2}/'],
            ['numeric', 8,  '/[0-9]{8}/'],
            ['numeric', 15, '/[0-9]{15}/'],
            ['nozero',  2,  '/[1-9]{2}/'],
            ['nozero',  8,  '/[1-9]{8}/'],
            ['nozero',  15, '/[1-9]{15}/'],
            ['unique',  2,  '/[0-9a-f]{32}/'],
            ['unique',  8,  '/[0-9a-f]{32}/'],
            ['unique',  15, '/[0-9a-f]{32}/'],
        ];
    }

    /**
     * @covers ::random
     * @dataProvider randomDefaultLengthProvider
     *
     * @param string  $type
     * @param integer $expectedLength
     */
    public function testRandomDefaultLength($type, $expectedLength)
    {
        $this->assertEquals($expectedLength, mb_strlen(Str::random($type)));
    }

    public function randomDefaultLengthProvider()
    {
        return [
            ['alnum',   8],
            ['numeric', 8],
            ['nozero',  8],
            ['unique',  32],
            ['guid',    36],
        ];
    }

    /**
     * @covers ::snake
     * @dataProvider snakeProvider
     *
     * @param $stringSnakeCase
     * @param $expectedString
     */
    public function testSnake($stringSnakeCase, $expectedString)
    {
        $this->assertEquals($expectedString, Str::snake($stringSnakeCase));
    }

    public function snakeProvider()
    {
        return [
            ['camelCase',  'camel_case'],
            ['snake_case', 'snake_case'],
            ['camelCAse1', 'camel_c_ase1'],
            ['string',     'string'],
        ];
    }

    /**
     * @covers ::camel
     * @dataProvider camelProvider
     *
     * @param $stringCamelCase
     * @param $expectedString
     */
    public function testCamel($stringCamelCase, $expectedString)
    {
        $this->assertEquals($expectedString, Str::camel($stringCamelCase));
    }

    public function camelProvider()
    {
        return [
            ['camel_case',   'camelCase'],
            ['camelCase',    'camelCase'],
            ['camel_c_ase1', 'camelCAse1'],
            ['string',       'string'],
        ];
    }

    /**
     * @covers ::studly
     * @dataProvider studlyProvider
     *
     * @param $stringCamelCase
     * @param $expectedString
     */
    public function testStudly($stringCamelCase, $expectedString)
    {
        $this->assertEquals($expectedString, Str::studly($stringCamelCase));
    }

    public function studlyProvider()
    {
        return [
            ['camel_case',   'CamelCase'],
            ['camel-case',   'CamelCase'],
            ['camelCase',    'CamelCase'],
            ['camel_c_ase1', 'CamelCAse1'],
            ['string',       'String'],
        ];
    }
}
