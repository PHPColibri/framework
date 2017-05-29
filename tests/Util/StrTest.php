<?php

namespace Colibri\Tests\Util;

use Colibri\Util\Str;
use PHPUnit_Framework_TestCase;

class StrTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \Colibri\Util\Str::isEmail()
     */
    public function testIsEmail()
    {
        $validEmails = [
            'test@ya.ru',
            'test-123@gmail.com',
            'test.test@yahoo.com.ru',
            'test@test.test.test.test',
            '88005553535@ya.ru',
            'xxxColibrixxx@gmail.com',
        ];

        foreach ($validEmails as $email) {
            $this->assertTrue(Str::isEmail($email));
        }

        $invalidEmails = [
            'invalidTest.test',
            '@ya.com.ru',
            'teststring',
            '88005553535@test',
            'test@colibri@test.ru',
            'test.@colibri.gmail',
            '.test@colibri.gmail',
        ];

        foreach ($invalidEmails as $email) {
            $this->assertFalse(Str::isEmail($email));
        }
    }
}
