<?php
namespace Colibri\Database\AbstractDb\Driver;

abstract class Connection implements ConnectionInterface
{
    /** @var string */
    protected $host;
    /** @var string */
    protected $login;
    /** @var string */
    protected $pass;
    /** @var string */
    protected $database;

    /** @var \Colibri\Database\AbstractDb\Driver\Connection\Metadata */
    protected $metadata;

    /** @var bool */
    public static $monitorQueries = false;
    /** @var string */
    public static $strQueries = '';
    /** @var int */
    public static $queriesCount = 0;

    /**
     * Connection constructor.
     *
     * @param string $host
     * @param string $login
     * @param string $pass
     * @param string $database
     */
    public function __construct($host, $login, $pass, $database)
    {
        $this->host     = $host;
        $this->login    = $login;
        $this->pass     = $pass;
        $this->database = $database;
    }

    /**
     * Открывает соединение с базой данных.
     * Opens connection to database.
     *
     * @throws \Colibri\Database\DbException
     */
    public function open()
    {
        if (self::$monitorQueries) {
            self::$strQueries .= "Before @mysqli_connect\n";
            global $time;
            $curTime          = microtime(true) - $time;
            self::$strQueries .= sprintf('%f', $curTime) . "\n";
        }

        $this->connect();
    }

    /**
     * @throws \Colibri\Database\DbException
     */
    abstract protected function connect();

    /**
     * Выполняет запрос к базе данных.
     * Executes given query.
     *
     * @param string $query
     *
     * @return bool|\Colibri\Database\AbstractDb\Driver\Query\ResultInterface
     *
     * @throws \Colibri\Database\Exception\SqlException
     *
     * @global int   $time
     */
    public function query(string $query)
    {
        if (self::$monitorQueries) {
            $queryStartTime   = microtime(true);
            self::$strQueries .= $query . "\n";
        }

        $result = $this->sendQuery($query);

        if (self::$monitorQueries) {
            global $time;
            $queryEndTime  = microtime(true);
            $curScriptTime = $queryEndTime - $time;
            /** @var int $queryStartTime */
            $queryExecTime    = $queryEndTime - $queryStartTime;
            self::$strQueries .= '  Script time: ' . round($curScriptTime, 8) . "\n";
            self::$strQueries .= '  Query  time: ' . round($queryExecTime, 8) . "\n";
        }

        return $result;
    }

    /**
     * @param string $query
     *
     * @return bool|Query\ResultInterface
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    abstract protected function sendQuery(string $query);

    /**
     * @return \Colibri\Database\AbstractDb\Driver\Connection\Metadata
     */
    public function metadata(): Connection\MetadataInterface
    {
        /** @var Connection\Metadata $class */
        $class = static::class . '\Metadata';

        return $this->metadata === null
            ? $this->metadata = new $class($this, $this->database . '@' . $this->host)
            : $this->metadata;
    }
}
