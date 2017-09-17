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
    }

    // -------------------------------------------------------------------------------------

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

        $this->mockPreparedValues('0', '0', $twoMonthsAgoString);

        $selectQuery =
            Query::select(['*'])
                ->from('users')
                ->where([
                    'status >'    => 0,
                    'gender'      => 0,
                    'createdAt >' => $twoMonthsAgo,
                ])
                ->build($this->dbMock)
        ;

        self::assertEquals(
            "select t.* from users t where (`status` > 0 and `gender` = 0 and `createdAt` > $twoMonthsAgoString);",
            $selectQuery
        );
    }

    /**
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \Colibri\Database\DbException
     */
    public function testSelectJoin()
    {
        $selectJoinQuery = Query::select(['*'], ['*'])
            ->from('users')
            ->join('user_sites', 'user_id', 'id')
            ->build($this->dbMock)
        ;

        self::assertEquals(
            'select t.*, j1.* from users t left join user_sites j1 on j1.user_id = t.id;',
            $selectJoinQuery
        );
    }

    /**
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function testUpdate()
    {
        $this->mockPreparedValues(2, 0, '\'alek13\'', 0);

        $updateQuery =
            Query::update()
                ->into('users')
                ->values(['status' => 2, 'gender' => 0, 'email' => 'alek13'])
                ->where(['gender' => 0])
                ->build($this->dbMock)
        ;

        self::assertEquals(
            'update users set `status` = 2, `gender` = 0, `email` = \'alek13\' where (`gender` = 0);',
            $updateQuery
        );
    }

    /**
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function testDelete()
    {
        $this->mockPreparedValues(3);

        $deleteQuery =
            Query::delete()
                ->from('users')
                ->where(['id' => 3])
                ->build($this->dbMock)
        ;

        self::assertEquals(
            'delete from users where (`id` = 3);',
            $deleteQuery
        );
    }
}
