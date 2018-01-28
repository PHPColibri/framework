<?php
namespace Colibri\tests\Database\MySQL;

use Colibri\Database\Concrete;
use Colibri\Database\Query;
use Colibri\tests\Database\QueryTestCase;

class QueryTest extends QueryTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->dbMock
            ->shouldReceive('getQueryBuilder')
            ->andReturn(new Concrete\MySQL\Query\Builder($this->connectionMock));
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \UnexpectedValueException
     */
    public function testSelectOrderLimit()
    {
        $this
            ->assertQueryIs(
                'select sql_calc_found_rows t.* from users t order by `registered` asc limit 0, 10',
                Query::select()->from('users')->orderBy(['registered' => 'asc'])->limit(10)
            )
            ->assertQueryIs(
                'select sql_calc_found_rows t.* from users t order by `registered` desc limit 2110, 10',
                Query::select()->from('users')->orderBy(['registered' => 'desc'])->limit(2110, 10)
            )
        ;
    }
}
