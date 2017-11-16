<?php
namespace Colibri\Database;

use Colibri\Cache\Cache;

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
    public static $useCacheForMetadata = false;

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
        /* @var Exception\SqlException|\Exception $e */
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
}
