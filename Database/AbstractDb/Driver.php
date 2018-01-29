<?php
namespace Colibri\Database\AbstractDb;

use Colibri\Database\Exception\SqlException;
use Colibri\Database\Query;

/**
 * Abstract class for Db.
 */
abstract class Driver implements DriverInterface
{
    /** @var \Colibri\Database\AbstractDb\Driver\ConnectionInterface */
    protected $connection;

    /**
     * Конструктор
     *
     * @param \Colibri\Database\AbstractDb\Driver\ConnectionInterface $connection
     */
    public function __construct(Driver\ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Получение переменной соединения.
     * Gets the connection.
     *
     * @return \Colibri\Database\AbstractDb\Driver\ConnectionInterface
     */
    public function getConnection(): Driver\ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * Выполняет запрос к базе данных.
     * Executes given query.
     *
     * @param Query $query
     *
     * @return bool|\Colibri\Database\AbstractDb\Driver\Query\ResultInterface
     *
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \UnexpectedValueException
     */
    public function query(Query $query)
    {
        return $this->connection->query($query->build($this));
    }

    /**
     * Выполняет несколько запросов.
     * Executes a number of $queries.
     *
     * @param array $queries        запросы, которые нужно выполнить. queries to execute.
     * @param bool  $rollbackOnFail нужно ли откатывать транзакцию.   if you need to roll back transaction.
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function queries(array $queries, $rollbackOnFail = false)
    {
        /* @var SqlException|\Exception $e */
        try {
            foreach ($queries as &$query) {
                $this->connection->query($query . ';');
            }
        } catch (\Exception $e) {
            $rollbackOnFail && $this->transactionRollback();
            throw $e;
        }
    }

    /**
     * @return \Colibri\Database\AbstractDb\Driver\Query\Builder
     */
    public function getQueryBuilder(): Driver\Query\Builder
    {
        static $builder;

        $class = static::class . '\Query\Builder';

        return $builder === null
            ? $builder = new $class($this->connection)
            : $builder;
    }

    /**
     * @return \Colibri\Database\AbstractDb\Driver\Connection\Metadata
     */
    public function metadata(): Driver\Connection\MetadataInterface
    {
        return $this->connection->metadata();
    }
}
