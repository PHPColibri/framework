<?php
namespace Colibri\Database;

use Colibri\Cache\Memcache;

/**
 * Abstract class for Db.
 */
abstract class AbstractDb implements DbInterface
{
    /** @var string */
    protected $host;
    /** @var string */
    protected $login;
    /** @var string */
    protected $pass;
    /** @var string */
    protected $database;

    /**
     * @var bool
     */
    public static $useMemcacheForMetadata = false;

    /**
     * @var array
     */
    private static $columnsMetadata = [];

    /**
     * Кеширует и возвращает информацию о полях таблицы.
     * Caches and returns table columns info.
     *
     * @param string $tableName
     *
     * @return array
     *
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     */
    public function &getColumnsMetadata($tableName)
    {
        if ( ! isset(self::$columnsMetadata[$tableName])) {
            self::$columnsMetadata[$tableName] = (static::$useMemcacheForMetadata
                ? Memcache::remember($this->database . '.' . $tableName . '.meta', function () use ($tableName) {
                    /** @noinspection PhpUnhandledExceptionInspection */
                    return $this->retrieveColumnsMetadata($tableName);
                })
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
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    abstract protected function &retrieveColumnsMetadata($tableName);

    /**
     * Выполняет несколько запросов.
     * Executes a number of $queries.
     *
     * @param array $queries        запросы, которые нужно выполнить. queries to execute.
     * @param bool  $rollbackOnFail нужно ли откатывать транзакцию.   if you need to roll back transaction.
     *
     * @return bool
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function queries(array $queries, $rollbackOnFail = false)
    {
        foreach ($queries as &$query) {
            if ( ! $this->query($query . ';')) {
                return $rollbackOnFail ? $this->transactionRollback() && false : false;
            }
        }

        return true;
    }

    /**
     * Возвращает тип поля таблицы.
     * Returns table column type.
     *
     * @param string $table
     * @param string $column
     *
     * @return string
     *
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     */
    public function getFieldType(string $table, string $column): string
    {
        return $this->getColumnsMetadata($table)['fieldTypes'][$column];
    }
}
