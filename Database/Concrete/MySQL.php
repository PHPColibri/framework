<?php
namespace Colibri\Database\Concrete;

use Colibri\Database\AbstractDb;
use Colibri\Database\DbException;
use Colibri\Database\Exception\SqlException;

/**
 * Класс для работы с MySQL.
 */
class MySQL extends AbstractDb
{
    /** @var \mysqli */
    private $connect;
    /** @var \mysqli_result */
    private $result;
    /** @var bool */
    private $persistent = false;

    /** @var bool */
    public static $monitorQueries = false;
    /** @var string */
    public static $strQueries = '';
    /** @var int */
    public static $queriesCount = 0;

    /**
     * Конструктор
     *
     * @param string $host       mysql server name/ip[:port]
     * @param string $login      mysql user login
     * @param string $pass       mysql user password
     * @param string $database   mysql database name
     * @param bool   $persistent make persistent connection
     *
     * @throws DbException
     */
    public function __construct($host, $login, $pass, $database, $persistent = false)
    {
        $this->host       = $host;
        $this->login      = $login;
        $this->pass       = $pass;
        $this->database   = $database;
        $this->persistent = $persistent;

        $this->open();
    }

    /**
     * Открывает соединение с базой данных.
     * Opens connection to database.
     *
     * @throws \Colibri\Database\DbException
     */
    public function open(/*$encoding = 'utf8'*/)
    {
        if (self::$monitorQueries) {
            self::$strQueries .= "Before @mysqli_connect\n";
            global $time;
            $curTime          = microtime(true) - $time;
            self::$strQueries .= sprintf('%f', $curTime) . "\n";
        }

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

        /** @PhpUnhandledExceptionInspection */
        $this->query("SET CHARACTER SET 'utf8'"/*, $encoding*/);
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
     * Получение переменной соединения.
     * Gets the connection.
     *
     * @return \mysqli
     */
    public function getConnect()
    {
        return $this->connect;
    }

    /**
     * Returns count of retrieved rows in query result.
     * Количество строк в результате запроса на выборку.
     *
     * @return int
     */
    public function getNumRows()
    {
        return $this->result->num_rows;
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
     * @param int $row
     * @param int $field
     *
     * @return object
     */
    public function getResult($row = 0, $field = 0)
    {
        $this->result->data_seek($row);
        $this->result->field_seek($field);

        return $this->result->fetch_field();
    }

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
     * Достаёт очередную стоку из результата запроса в виде массива указанниго типа.
     * Fetch row from query result as an associative array, a numeric array, or both.
     *
     * @param int $param Fetch type. Модификатор тива возвращаемого значения.
     *                   Возможные параметры: MYSQLI_NUM | MYSQLI_ASSOC | MYSQLI_BOTH
     *
     * @return array
     */
    public function fetchArray($param = MYSQLI_ASSOC)
    {
        return $this->result->fetch_array($param);
    }

    /**
     * Достаёт очередную стоку из результата запроса в виде нумерованного массива.
     * Fetch row from query result as an enumerated array.
     *
     * @return array
     */
    public function fetchRow()
    {
        return $this->result->fetch_row();
    }

    /**
     * Достаёт очередную стоку из результата запроса в виде асоциативного массива (ключи - названия колонок).
     * Fetch row from query result as an associative array.
     *
     * @return array
     */
    public function fetchAssoc()
    {
        return $this->result->fetch_assoc();
    }

    /**
     * Достаёт все строки из результата запроса в массив указанного вида(асоциативный,нумеровынный,оба).
     * Fetch all rows from query result as specified(assoc,num,both) array.
     *
     * @param int $param Fetch type. Модификатор тива возвращаемого значения.
     *                   Возможные параметры: MYSQLI_NUM | MYSQLI_ASSOC | MYSQLI_BOTH
     *
     * @return array
     */
    public function &fetchAllRows($param = MYSQLI_ASSOC)
    {
        $return = [];
        while ($row = $this->fetchArray($param)) {
            $return[] = $row;
        }

        return $return;
    }

    /**
     * Достаёт последнюю строку из результата запроса в виде нумерованного массива.
     * Fetch last rows from query result as an enumerated array.
     *
     * @return array
     */
    public function fetchLastRow()
    {
        $this->result->data_seek($this->getNumRows() - 1);

        return $this->result->fetch_row();
    }

    /**
     * Выполняет запрос к базе данных.
     * Executes given query.
     *
     * @param string $query
     *
     * @throws \Colibri\Database\Exception\SqlException
     *
     * @global int   $time
     */
    public function query($query)
    {
        if (self::$monitorQueries) {
            $queryStartTime   = microtime(true);
            self::$strQueries .= $query . "\n";
        }

        $this->result = $this->dbQuery($query);

        if (self::$monitorQueries) {
            global $time;
            $queryEndTime  = microtime(true);
            $curScriptTime = $queryEndTime - $time;
            /** @var int $queryStartTime */
            $queryExecTime    = $queryEndTime - $queryStartTime;
            self::$strQueries .= '  Script time: ' . round($curScriptTime, 8) . "\n";
            self::$strQueries .= '  Query  time: ' . round($queryExecTime, 8) . "\n";
        }
    }

    /**
     * Соьирает шаблон запроса, подставляя значения из $arguments.
     * Compile query template with specified $arguments array.
     *
     * @param string $tpl
     * @param array  $arguments
     *
     * @return string
     */
    public static function getQueryTemplateArray($tpl, array $arguments)
    {
        $argNum = count($arguments);
        for ($i = $argNum; $i > 0; $i--) {
            $tpl = str_replace('%' . $i, $arguments[$i - 1], $tpl);
        }

        return $tpl;
    }

    /**
     * Собирает шаблон запроса, подставляя значения из переданных в метод агргументов.
     * Compile query template with passed into method arguments.
     *
     * @param string $template
     * @param array  $arguments
     *
     * @return string
     */
    public static function getQueryTemplate($template, ...$arguments)
    {
        $strQuery = $template;
        foreach ($arguments as $i => &$argument) {
            $strQuery = str_replace('%' . $i, $argument, $strQuery);
        }

        return $strQuery;
    }

    /**
     * Выполняет запрос, собранный из указанного шаблона, подставив значения из переданных в метод агргументов.
     * Executes query template compiles with passed into method arguments.
     *
     * @param string $template
     * @param array  $arguments
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function queryTemplate($template, ...$arguments)
    {
        $strQuery = self::getQueryTemplate($template, $arguments);

        $this->query($strQuery);
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
    private function &dbQuery($query)
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

    /**
     * Открывает транзакцию.
     * Starts database transaction.
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function transactionStart()
    {
        $this->query('START TRANSACTION;');
    }

    /**
     * Откатывает транзакцию.
     * Rolls back database transaction.
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function transactionRollback()
    {
        $this->query('ROLLBACK;');
    }

    /**
     * "Комитит" транзакцию в БД.
     * Commits database transaction.
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function transactionCommit()
    {
        $this->query('COMMIT;');
    }

    /**
     * Возвращает информацию о внешних ключах таблицы.
     * Returns table foreign keys info.
     *
     * @param string $tableName
     *
     * @return array fields:   [TABLE_SCHEMA] ,TABLE_NAME, COLUMN_NAME
     *               refs to:  [REFERENCED_TABLE_SCHEMA ], REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
     *
     * @throws SqlException
     */
    public function getTableFKs($tableName)
    {
        $this->query('SELECT * FROM `KEY_COLUMN_USAGE` WHERE `TABLE_NAME` = \'' . $tableName . '\' AND `REFERENCED_COLUMN_NAME` IS NOT NULL');

        return $this->fetchAllRows();
    }

    /**
     * Выполняет несколько запросов внутри одной транзакции.
     * Executes number of $queries within transaction.
     *
     * @param array $queries
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function commit(array $queries)
    {
        $this->transactionStart();
        $this->queries($queries, true);
        $this->transactionCommit();
    }

    /**
     * Подготавливает значение для вставки в строку запроса.
     * Prepares value for insert into query string.
     *
     * @param mixed  $value
     * @param string $type
     *
     * @return float|int|string
     */
    public function prepareValue(&$value, $type)
    {
        if ($value === null) {
            return $value = 'NULL';
        }

        if (is_array($value)) {
            foreach ($value as &$v) {
                $this->prepareValue($v, $type);
            }

            return '(' . implode(', ', $value) . ')';
        }

        switch (strtolower($type)) {
            case 'timestamp':
                $value = is_int($value)
                    ?
                    '\'' . date('Y-m-d H:i:s', $value) . '\''
                    :
                    ($value instanceof \DateTime
                        ?
                        '\'' . $value->format('Y-m-d H:i:s') . '\''
                        :
                        '\'' . $this->connect->escape_string($value) . '\''
                    );
                break;

            case 'bit':
                $value = (int)intval($value);
                break;

            case 'dec':
            case 'decimal':
            case 'tinyint':
            case 'smallint':
            case 'bigint':
            case 'int':
                $value = (int)intval($value);
                break;
            case 'double':
            case 'float':
                $value = (float)floatval($value);
                break;

            default:
                $value = '\'' . $this->connect->escape_string($value) . '\'';
        }

        return $value;
    }

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * Возвращает информацию о полях таблицы.
     * Returns table columns info.
     *
     * @param string $tableName
     *
     * @return array
     */
    protected function &retrieveColumnsMetadata($tableName)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->query('SHOW COLUMNS FROM ' . $tableName);
        $result = $this->fetchAllRows();

        $fields       = [];
        $fieldTypes   = [];
        $fieldLengths = [];

        $cnt = count($result);
        for ($i = 0; $i < $cnt; $i++) {
            $fName                = &$result[$i]['Field'];
            $fType                = &$result[$i]['Type'];
            $fields[]             = &$fName;
            $fieldTypes[$fName]   = explode('(', $fType)[0];
            $fieldLengths[$fName] = $this->extractFieldTypeLength($fType);
        }

        $returnArray = [ // compact() ???
            'fields'       => &$fields,
            'fieldTypes'   => &$fieldTypes,
            'fieldLengths' => &$fieldLengths,
        ];

        return $returnArray;
    }

    /**
     * @param string $strFieldType
     *
     * @return int|null
     */
    private function extractFieldTypeLength(&$strFieldType)
    {
        $len = explode(')', $strFieldType);
        $len = explode('(', $len[0]);

        return (int)(count($len) > 1 ? $len[1] : null);
    }
}
