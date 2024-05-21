<?php
namespace Colibri\tests\Session;

use Colibri\Session\Session;
use Colibri\Session\Storage\StorageInterface;
use Colibri\tests\TestCase;
use PHPUnit\Framework\MockObject\Invocation\ObjectInvocation;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Tests for Session class.
 *
 * @coversDefaultClass \Colibri\Session\Session
 */
class SessionTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $storageMock;

    /**
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \ReflectionException
     */
    protected function setUp(): void
    {
        // use PHPUnit mocks, because StorageInterface contains `catch`method, but Mockery can`t mock it
        $this->storageMock = $this
            ->getMockBuilder(StorageInterface::class)
            ->setMethodsExcept()
            ->getMock()
        ;

        static::inject(Session::class, ['storage' => $this->storageMock]);
    }

    protected function tearDown(): void
    {
        $this->storageMock = null;
    }

    // -------------------------------------------------------------------------------------

    /**
     * @covers ::get
     * @dataProvider getDataProvider
     *
     * @param string $variable
     * @param mixed  $value
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     */
    public function testGet($variable, $value)
    {
        $this->storageMock
            ->expects($spy = self::once())
            ->method('get')->with($variable)
            ->willReturn($value)
        ;

        self::assertEquals($value, Session::get($variable));
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        // args: [variable name to ::get, returned value]
        return [
            ['var1', true],
            ['var2', false],
            ['var3', 777],
            ['var4', .012],
            ['var5', 'qwerty'],
            ['var6', new \stdClass()],
        ];
    }
}
