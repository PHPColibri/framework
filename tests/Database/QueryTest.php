<?php
namespace Colibri\tests\Database;

use Colibri\Database\DbInterface;
use Colibri\Database\Query;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class QueryTest.
 */
class QueryTest extends TestCase
{
    /** @var MockInterface|DbInterface $mock */
    private $dbMock;

    protected function setUp()
    {
        $this->dbMock = Mockery::mock(DbInterface::class);
    }

    /**
     * @throws \Exception
     */
    protected function tearDown()
    {
        Mockery::close();
    }

    /**
     * @param array $values
     *
     * @return $this
     */
    private function mockPreparedValues(...$values)
    {
        /* @noinspection PhpMethodParametersCountMismatchInspection */
        $this->dbMock
            ->shouldReceive('prepareValue')
            ->andReturnValues($values)
        ;
        /* @noinspection PhpMethodParametersCountMismatchInspection */
        $this->dbMock
            ->shouldReceive('getFieldType');

        return $this;
    }

    /**
     * @param string                  $expected
     * @param \Colibri\Database\Query $query
     *
     * @return $this
     *
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    private function assertQueryIs(string $expected, Query $query)
    {
        static::assertEquals(
            $expected,
            $query->build($this->dbMock)
        );

        return $this;
    }

    // -------------------------------------------------------------------------------------

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidTypeConstructor()
    {
        new Query('qwerty');
    }

    /**
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function testInsert()
    {
        $this->mockPreparedValues('\'alek13\'', '\'alek13\'', 1);

        $insertQuery = Query::insert()->into('users')
            ->values([
                'email'    => 'alek13',
                'password' => 'alek13',
                'status'   => true,
            ])
            ->build($this->dbMock)
        ;

        self::assertEquals(
            'insert into users set `email` = \'alek13\', `password` = \'alek13\', `status` = 1;',
            $insertQuery
        );
    }

    /**
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \Colibri\Database\DbException
     */
    public function testSelect()
    {
        $twoMonthsAgo       = (new \DateTime())->sub(new \DateInterval('P2M'));
        $twoMonthsAgoString = '\'' . $twoMonthsAgo->format('Y-m-d H:i:s') . '\'';

        $this
            ->mockPreparedValues('0', '0', $twoMonthsAgoString)
            ->assertQueryIs(
                "select t.* from users t where (t.`status` > 0 and t.`gender` = 0 and t.`createdAt` > $twoMonthsAgoString);",
                Query::select(['*'])
                    ->from('users')
                    ->where([
                        'status >'    => 0,
                        'gender'      => 0,
                        'createdAt >' => $twoMonthsAgo,
                    ])
            )
        ;
    }

    /**
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \Colibri\Database\DbException
     */
    public function testSelectJoin()
    {
        $this->assertQueryIs(
            'select t.*, j1.* from users t left join user_sites j1 on j1.user_id = t.id inner join sites j2 on j2.id = j1.site_id;',
            Query::select(['*'], ['*'])
                ->from('users')
                ->join('user_sites', 'user_id', 'id')
                ->join('sites', 'id', 'j1.site_id', Query\JoinType::INNER)
        );
    }

    /**
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function testSelectOrderLimit()
    {
        $this
            ->assertQueryIs(
                'select t.* from users t order by `registered` asc limit 0, 10;',
                Query::select()->from('users')->orderBy(['registered' => 'asc'])->limit(10)
            )
            ->assertQueryIs(
                'select t.* from users t order by `registered` desc limit 2110, 10;',
                Query::select()->from('users')->orderBy(['registered' => 'desc'])->limit(2110, 10)
            )
        ;
    }

    /**
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function testUpdate()
    {
        $this
            ->mockPreparedValues(2, 0, '\'alek13\'', 0)
            ->assertQueryIs(
                'update users t set t.`status` = 2, t.`gender` = 0, t.`email` = \'alek13\' where (t.`gender` = 0);',
                Query::update()
                    ->into('users')
                    ->values(['status' => 2, 'gender' => 0, 'email' => 'alek13'])
                    ->where(['gender' => 0])
            )
        ;
    }

    /**
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function testDelete()
    {
        $this
            ->mockPreparedValues(3)
            ->assertQueryIs(
                'delete from users t where (t.`id` = 3);',
                Query::delete()
                    ->from('users')
                    ->where(['id' => 3])
            )
        ;
    }
}
