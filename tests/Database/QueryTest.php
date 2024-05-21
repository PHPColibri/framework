<?php
namespace Colibri\tests\Database;

use Colibri\Database\AbstractDb\Driver\Query\Builder;
use Colibri\Database\Query;
use Colibri\Database\Query\Aggregation as Agg;

/**
 * Class QueryTest.
 */
class QueryTest extends QueryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $connection   = $this->connectionMock;
        $this->dbMock
            ->shouldReceive('getQueryBuilder')
            ->andReturn(new class($connection) extends Builder {
            })
        ;
    }

    /**
     * @param array $escapedValues
     * @param array $types
     *
     * @return $this
     */
    private function mockEscapes(array $escapedValues, array $types)
    {
        $this->metadataMock
            ->shouldReceive('getFieldType')
            ->andReturn(...$types)
        ;

        $this->connectionMock
            ->shouldReceive('escape')
            ->andReturn(...$escapedValues)
        ;

        return $this;
    }

    // -------------------------------------------------------------------------------------

    public function testInvalidTypeConstructor()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Query('qwerty');
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \UnexpectedValueException
     */
    public function testInsert()
    {
        $this->mockEscapes(['alek13', 'alek13'], ['varchar', 'varchar', 'int']);

        $insertQuery = Query::insert()->into('users')
            ->set([
                'email'    => 'alek13',
                'password' => 'alek13',
                'status'   => true,
            ])
            ->build($this->dbMock)
        ;

        self::assertEquals(
            'insert into users set `email` = \'alek13\', `password` = \'alek13\', `status` = 1',
            $insertQuery
        );
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \UnexpectedValueException
     */
    public function testSelect()
    {
        $this
            ->assertQueryIs(
                'select t.* from users t',
                Query::select(['*'])->from('users')
            )
        ;
    }

    /**
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \UnexpectedValueException
     */
    public function testWhere()
    {
        $twoMonthsAgo       = (new \DateTime())->sub(new \DateInterval('P2M'));
        $twoMonthsAgoString = '\'' . $twoMonthsAgo->format('Y-m-d H:i:s') . '\'';

        $this
            ->mockEscapes(
                ['banned', 'banned'],
                ['int', 'int', 'timestamp', 'varchar', 'int', 'int', 'timestamp', 'varchar']
            )
            ->assertQueryIs(
                "select t.* from users t where (t.`age` > 18 and t.`gender` = 0 and t.`createdAt` > $twoMonthsAgoString and t.`status` != 'banned')",
                Query::select(['*'])
                    ->from('users')
                    ->where([
                        'age >'       => 18,
                        'gender'      => 0,
                        'createdAt >' => $twoMonthsAgo,
                        'status !='   => 'banned',
                    ])
            )
            ->assertQueryIs(
                "select t.* from users t where (t.`age` > 18 and t.`gender` = 0 and t.`createdAt` > $twoMonthsAgoString and t.`status` != 'banned')",
                Query::select(['*'])
                    ->from('users')
                    ->where([
                        'age >'  => 18,
                        'gender' => 0,
                    ])
                    ->where([
                        'createdAt >' => $twoMonthsAgo,
                        'status !='   => 'banned',
                    ])
            )
        ;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \UnexpectedValueException
     */
    public function testSelectJoin()
    {
        $this->assertQueryIs(
            'select t.*, j1.* from users t left join user_sites j1 on j1.user_id = t.id inner join sites j2 on j2.id = j1.site_id',
            Query::select(['*'], ['*'])
                ->from('users')
                ->join('user_sites', 'user_id', 'id')
                ->join('sites', 'id', 'j1.site_id', Query\JoinType::INNER)
        );
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
                'select t.* from users t order by `registered` asc limit 0, 10',
                Query::select()->from('users')->orderBy(['registered' => 'asc'])->limit(10)
            )
            ->assertQueryIs(
                'select t.* from users t order by `registered` desc limit 2110, 10',
                Query::select()->from('users')->orderBy(['registered' => 'desc'])->limit(2110, 10)
            )
        ;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \UnexpectedValueException
     */
    public function testCountGroupBy()
    {
        $this
            ->assertQueryIs(
                'select count(t.id) from users t group by `gender`',
                Query::select([Agg::count('id')])->from('users')->groupBy(['gender'])
            )
        ;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \UnexpectedValueException
     */
    public function testCountDistinct()
    {
        $this
            ->assertQueryIs(
                'select count(distinct t.session_id) from user_clicks t',
                Query::select([Agg::countDistinct('session_id')])->from('user_clicks')
            )
        ;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \UnexpectedValueException
     */
    public function testMaxMinAvg()
    {
        $this
            ->assertQueryIs(
                'select max(t.age) from users t',
                Query::select([Agg::max('age')])->from('users')
            )
            ->assertQueryIs(
                'select min(t.age) from users t',
                Query::select([Agg::min('age')])->from('users')
            )
            ->assertQueryIs(
                'select avg(t.age) from users t',
                Query::select([Agg::avg('age')])->from('users')
            )
            ->assertQueryIs(
                'select max(t.age), min(t.age), avg(t.age) from users t',
                Query::select([Agg::max('age'), Agg::min('age'), Agg::avg('age')])->from('users')
            )
        ;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \UnexpectedValueException
     */
    public function testUpdate()
    {
        $this
            ->mockEscapes(
                ['alek13'],
                ['int', 'int', 'varchar', 'int']
            )
            ->assertQueryIs(
                'update users t set t.`status` = 2, t.`gender` = 0, t.`email` = \'alek13\' where (t.`gender` = 0)',
                Query::update('users')
                    ->set(['status' => 2, 'gender' => 0, 'email' => 'alek13'])
                    ->where(['gender' => 0])
            )
        ;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \UnexpectedValueException
     */
    public function testDelete()
    {
        $this
            ->mockEscapes([], ['int'])
            ->assertQueryIs(
                'delete from t using users t where (t.`id` = 3)',
                Query::delete()
                    ->from('users')
                    ->where(['id' => 3])
            )
        ;
    }
}
