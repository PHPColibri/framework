<?php
namespace Colibri\Database;

use Colibri\Cache\Memcache;

abstract class AbstractDb implements IDb
{
    protected $host;
    protected $login;
    protected $pass;
    protected $database;

    /**
     * @var bool
     */
    static	public	$useMemcacheForMetadata = false;

    /**
     * @var array
     */
    private static $columnsMetadata = [];

    /**
     * @param string $tableName
     *
     * @return array
     */
    public function &getColumnsMetadata($tableName)
    {
        if (!isset(self::$columnsMetadata[$tableName]))
            self::$columnsMetadata[$tableName] = (static::$useMemcacheForMetadata
                ? Memcache::remember($this->database . '.' . $tableName . '.meta', function () use ($tableName) {
                    return $this->retrieveColumnsMetadata($tableName);
                })
                : $this->retrieveColumnsMetadata($tableName)
            );

        return self::$columnsMetadata[$tableName];
    }

    /**
     * @param $tableName
     *
     * @return array
     */
    abstract protected function &retrieveColumnsMetadata($tableName);

    /**
     *
     * @param array $arrQueries
     * @param bool $rollbackOnFail
     * @return bool
     */
    public	function	queries(array $arrQueries,$rollbackOnFail=false)
    {
        foreach ($arrQueries as &$query)
            if (!$this->query($query.';'))
                return  $rollbackOnFail?$this->transactionRollback()&&false:false;
        return true;
    }
}
