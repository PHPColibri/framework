<?php
namespace Colibri\Database\AbstractDb\Driver\Connection;

use Colibri\Cache\Cache;
use Colibri\Database\AbstractDb;

abstract class Metadata implements MetadataInterface
{
    /** @var AbstractDb\Driver\ConnectionInterface */
    protected $connection;
    /** @var string */
    protected $cachePrefix;

    /**
     * @var array
     */
    private static $columns = [];

    /**
     * @var bool
     */
    public static $useCacheForMetadata = false;

    /**
     * Metadata constructor.
     *
     * @param AbstractDb\Driver\ConnectionInterface $connection
     * @param string                                $cachePrefix
     */
    public function __construct(AbstractDb\Driver\ConnectionInterface $connection, string $cachePrefix)
    {
        $this->connection  = $connection;
        $this->cachePrefix = $cachePrefix;
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
    final public function getFieldType(string $table, string $column): string
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->getColumnsMetadata($table)['fieldTypes'][$column];
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * Кеширует и возвращает информацию о полях таблицы.
     * Caches and returns table columns info.
     *
     * @param string $tableName
     *
     * @return array
     */
    final public function &getColumnsMetadata($tableName)
    {
        if ( ! isset(self::$columns[$tableName])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            self::$columns[$tableName] = (static::$useCacheForMetadata
                ? $this->retrieveColumnsCachedMetadata($tableName)
                : $this->retrieveColumnsMetadata($tableName)
            );
        }

        return self::$columns[$tableName];
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param $tableName
     *
     * @return mixed
     */
    private function retrieveColumnsCachedMetadata($tableName)
    {
        $key = $this->cachePrefix . '.' . $tableName . '.meta';

        /* @noinspection PhpUnhandledExceptionInspection */
        return Cache::remember($key, function () use ($tableName) {
            return $this->retrieveColumnsMetadata($tableName);
        });
    }
}
