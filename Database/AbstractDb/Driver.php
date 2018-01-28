<?php
namespace Colibri\Database\AbstractDb;

use Colibri\Cache\Cache;
use Colibri\Database\AbstractDb\Driver\ConnectionInterface;
use Colibri\Database\Exception\SqlException;

/**
 * Abstract class for Db.
 */
abstract class Driver implements DriverInterface
{
    /** @var string */
    protected $database;
    /** @var \Colibri\Database\Concrete\MySQL\Connection */
    protected $connection;
    /** @var mixed */
    protected $result;

    /**
     * @var bool
     */
    public static $useCacheForMetadata = false;

    /**
     * Получение переменной соединения.
     * Gets the connection.
     *
     * @return \Colibri\Database\AbstractDb\Driver\ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * @var array
     */
    private static $columnsMetadata = [];

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * Кеширует и возвращает информацию о полях таблицы.
     * Caches and returns table columns info.
     *
     * @param string $tableName
     *
     * @return array
     */
    public function &getColumnsMetadata($tableName)
    {
        if ( ! isset(self::$columnsMetadata[$tableName])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            self::$columnsMetadata[$tableName] = (static::$useCacheForMetadata
                ? $this->retrieveColumnsCachedMetadata($tableName)
                : $this->retrieveColumnsMetadata($tableName)
            );
        }

        return self::$columnsMetadata[$tableName];
    }

    /**
     * Возвращает информацию о полях таблицы.
     * Returns table columns info.
     *
     * @param string $tableName
     *
     * @return array
     */
    abstract protected function &retrieveColumnsMetadata($tableName);

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
                $this->query($query . ';');
            }
        } catch (\Exception $e) {
            $rollbackOnFail && $this->transactionRollback();
            throw $e;
        }
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * Возвращает тип поля таблицы.
     * Returns table column type.
     *
     * @param string $table
     * @param string $column
     *
     * @return string
     */
    public function getFieldType(string $table, string $column): string
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->getColumnsMetadata($table)['fieldTypes'][$column];
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param $tableName
     *
     * @return mixed
     */
    private function retrieveColumnsCachedMetadata($tableName)
    {
        $key = $this->database . '.' . $tableName . '.meta';

        /* @noinspection PhpUnhandledExceptionInspection */
        return Cache::remember($key, function () use ($tableName) {
            return $this->retrieveColumnsMetadata($tableName);
        });
    }

    /**
     * @return \Colibri\Database\AbstractDb\Driver\Query\Builder
     */
    public function getQueryBuilder(): Driver\Query\Builder
    {
        static $builder;

        $class = static::class . '\Query\Builder';

        return $builder === null
            ? $builder = new $class($this)
            : $builder;
    }
}
