<?php
namespace Colibri\tests\Database;

use Colibri\Database\AbstractDb\Driver\Connection\MetadataInterface;
use Colibri\Database\AbstractDb\Driver\ConnectionInterface;
use Colibri\Database\AbstractDb\DriverInterface;
use Colibri\Database\Query;
use Colibri\tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class QueryTestCase extends TestCase
{
    /** @var MockInterface|DriverInterface */
    protected $dbMock;
    /** @var MockInterface|ConnectionInterface */
    protected $connectionMock;
    /** @var MockInterface|MetadataInterface */
    protected $metadataMock;

    protected function setUp()
    {
        $this->metadataMock = Mockery::mock(MetadataInterface::class);

        $this->connectionMock = Mockery::mock(ConnectionInterface::class);
        $this->connectionMock->shouldReceive('metadata')->andReturn($this->metadataMock);

        $this->dbMock = Mockery::mock(DriverInterface::class);
    }

    /**
     * @param string                  $expected
     * @param \Colibri\Database\Query $query
     *
     * @return $this
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \UnexpectedValueException
     */
    protected function assertQueryIs(string $expected, Query $query)
    {
        static::assertEquals(
            $expected,
            $query->build($this->dbMock)
        );

        return $this;
    }
}
