<?php
namespace Colibri\Database\Concrete\MySQL;

use Colibri\Database\AbstractDb\Driver;
use Colibri\Database\DbException;
use Colibri\Database\Exception\SqlException;

class Connection extends Driver\Connection
{
    /** @var \mysqli */
    private $link;
    /** @var bool */
    private $persistent = false;

    /**
     * Connection constructor.
     *
     * @param string $host       mysql server name/ip[:port]
     * @param string $login      mysql user login
     * @param string $pass       mysql user password
     * @param string $database   mysql database name
     * @param bool   $persistent make persistent connection
     *
     * @throws \Colibri\Database\DbException
     */
    public function __construct($host, $login, $pass, $database, $persistent = false)
    {
        parent::__construct($host, $login, $pass, $database);

        $this->database   = $database;
        $this->persistent = $persistent;

        $this->open();
    }

    /**
     * Проверка открыт ли коннект к базе.
     * Checks that connection is opened (alive).
     *
     * @return bool
     */
    public function opened()
    {
        return $this->link->ping();
    }

    /**
     * Closes the connection.
     *
     * @return bool
     *
     * @throws \Colibri\Database\DbException
     */
    public function close()
    {
        if ( ! $this->link->close()) {
            throw new DbException('can\'t close database connection: ' . $this->link->error, $this->link->errno);
        }

        return true;
    }

    /**
     * If instance somehow was stored in session for example, we need to reopen connection.
     *
     * @throws \Colibri\Database\DbException
     */
    public function __wakeup()
    {
        $this->open();
    }

    /**
     * @throws \Colibri\Database\DbException
     */
    protected function connect()
    {
        try {
            $this->link = new \mysqli($this->persistent ? 'p:' : '' . $this->host, $this->login, $this->pass);
        } catch (\Exception $exception) {
            throw new DbException('can\'t connect to database: ' . $exception->getMessage(), $exception->getCode(), $exception);
        }
        if ( ! $this->link) {
            throw new DbException('can\'t connect to database: ' . $this->link->connect_error, $this->link->connect_errno);
        }

        if ($this->link->select_db($this->database) === false) {
            throw new DbException('can\'t connect to database: ' . $this->link->error, $this->link->errno);
        }

        /* @PhpUnhandledExceptionInspection */
        $this->query("SET CHARACTER SET 'utf8'"/*, $encoding*/);
    }

    /**
     * Выполняет переданный запрос.
     * Executes given query.
     *
     * @param string $query
     *
     * @return bool|Driver\Query\ResultInterface
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    protected function sendQuery(string $query)
    {
        if (self::$monitorQueries) {
            self::$queriesCount++;
        }
        $result = $this->link->query($query);
        if ($result === false) {
            throw new SqlException(
                'SQL-error [' . $this->link->errno . ']: ' . $this->link->error . "\nSQL-query: $query",
                $this->link->errno
            );
        }

        return $result === true ? $result : new Query\Result($result);
    }

    // ------------------------------------------------------------------------------------

    /**
     * Идентификатор последней добавленной записи.
     * Returns the auto generated ID of last insert query.
     *
     * @return mixed
     */
    public function lastInsertId()
    {
        return $this->link->insert_id;
    }

    /**
     * Возвращает количество строк, затронутых запросом на изменение (insert, update, replace, delete, ...)
     * Returns count of rows that query affected.
     *
     * @return int|string returns string if count > PHP_INT_MAX
     */
    public function getAffectedRows()
    {
        return $this->link->affected_rows;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function escape(string $value): string
    {
        return $this->link->escape_string($value);
    }
}
