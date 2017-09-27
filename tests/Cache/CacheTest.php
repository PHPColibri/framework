<?php
namespace Colibri\tests\Cache;

use Colibri\Cache\Cache;
use Colibri\Cache\Storage\StorageInterface;
use Colibri\tests\TestCase;
use Mockery\MockInterface;

/**
 * Cache service Tests.
 *
 * @coversDefaultClass \Colibri\Cache\Cache
 */
class CacheTest extends TestCase
{
    /**
     * @var MockInterface erStorageInterface
     */
    private $storageMock;

    protected function setUp()
    {
        $this->storageMock = \Mockery::mock(StorageInterface::class);
    }

    /**
     * @dataProvider storageMethodCalled_DataProvider
     *
     * @param string $method
     * @param array  $args
     * @param array  $receiveArgs
     * @param mixed  $return
     * @param mixed  $expected
     */
    public function testStorageMethodCalled(string $method, array $args, array $receiveArgs, $return, $expected)
    {
        $this->inject(Cache::class, ['storage' => ['memcache' => $this->storageMock]]);

        /* @noinspection PhpMethodParametersCountMismatchInspection */
        $this->storageMock
            ->shouldReceive($method)
            ->withArgs($receiveArgs)
            ->andReturn($return);

        self::assertEquals($expected, Cache::$method(...$args));
    }

    /**
     * @return array
     */
    public function storageMethodCalled_DataProvider(): array
    {
        $someClosure = function () {
        };

        return [
            ['set', ['some.key', 'some.value'], ['some.key', 'some.value', null], true, true],
            ['get', ['some.key', 'some.default.value'], ['some.key', 'some.default.value'], 'some.default.value', 'some.default.value'],
            ['delete', ['some.key'], ['some.key'], true, true],
            ['remember', ['some.key', $someClosure], ['some.key', $someClosure, null], 'some.value', 'some.value'],
        ];
    }
}
