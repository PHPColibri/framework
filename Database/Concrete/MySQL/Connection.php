<?php
namespace Colibri\Database\Concrete\MySQL;

use Colibri\Database\AbstractDb\Driver\Connection as AbstractConnection;
use Colibri\Database\DbException;
use Colibri\Database\Exception\SqlException;

class Connection extends AbstractConnection
{
    /** @var \mysqli */
    private $connect;
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
        return $this->connect->ping();
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
        if ( ! $this->connect->close()) {
            throw new DbException('can\'t close database connection: ' . $this->connect->error, $this->connect->errno);
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
            $this->connect = new \mysqli($this->persistent ? 'p:' : '' . $this->host, $this->login, $this->pass);
        } catch (\Exception $exception) {
            throw new DbException('can\'t connect to database: ' . $exception->getMessage(), $exception->getCode(), $exception);
        }
        if ( ! $this->connect) {
            throw new DbException('can\'t connect to database: ' . $this->connect->connect_error, $this->connect->connect_errno);
        }

        if ($this->connect->select_db($this->database) === false) {
            throw new DbException('can\'t connect to database: ' . $this->connect->error, $this->connect->errno);
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
     * @return bool|\mysqli_result
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    protected function sendQuery(string $query)
    {
        if (self::$monitorQueries) {
            self::$queriesCount++;
        }
        $result = $this->connect->query($query);
        if ($result === false) {
            throw new SqlException(
                'SQL-error [' . $this->connect->errno . ']: ' . $this->connect->error . "\nSQL-query: $query",
                $this->connect->errno
            );
        }

        return $result;
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
        return $this->connect->insert_id;
    }

    /**
     * Возвращает количество строк, затронутых запросом на изменение (insert, update, replace, delete, ...)
     * Returns count of rows that query affected.
     *
     * @return int|string returns string if count > PHP_INT_MAX
     */
    public function getAffectedRows()
    {
        return $this->connect->affected_rows;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function escape(string $value): string
    {
        return $this->connect->escape_string($value);
    }
}
