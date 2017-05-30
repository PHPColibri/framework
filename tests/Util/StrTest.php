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
}
