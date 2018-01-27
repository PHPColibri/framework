<?php
namespace Colibri\tests\Database;

use Colibri\Database\AbstractDb\DriverInterface;
use Colibri\Database\Query;
use Colibri\tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class QueryTestCase extends TestCase
{
    /** @var MockInterface|DriverInterface */
    protected $dbMock;

    protected function setUp()
    {
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
