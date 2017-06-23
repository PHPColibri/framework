<?php
namespace Colibri\Database\Concrete;

use Colibri\Database\AbstractDb;
use Colibri\Database\DbException;
use Colibri\Database\Exception\SqlException;

/**
 * Класс для работы с MySQL
 */
class MySQL extends AbstractDb
{
    /** @var \mysqli */
    private $connect;
    /** @var  \mysqli_result */
    private $result;
    private $persistent;

    static public $monitorQueries = false;
    static public $strQueries = '';
    static public $queriesCount = 0;
    static public $cachedQueriesCount = 0;

    /**
     * Конструктор
     *
     * @param    string $host       mysql server name/ip[:port]
     * @param    string $login      mysql user login
     * @param    string $pass       mysql user password
     * @param    string $database   mysql database name
     * @param    bool   $persistent make persistent connection
     *
     * @throws DbException
     * @throws SqlException
     */
    function __construct($host, $login, $pass, $database, $persistent = false)
    {
        $this->host       = $host;
        $this->login      = $login;
        $this->pass       = $pass;
        $this->database   = $database;
        $this->persistent = $persistent;

        $this->open();
    }

    /**
     * Открывает соединение с базой данных
     *
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
        if (!$this->connect)
            throw new DbException('can\'t connect to database: ' . $this->connect->connect_error, $this->connect->connect_errno);

        if ($this->connect->select_db($this->database) === false)
            throw new DbException('can\'t connect to database: ' . $this->connect->error, $this->connect->errno);

        $this->query("SET CHARACTER SET 'utf8'"/*, $encoding*/);
    }

    /**
     * Проверка открыт ли коннект к базе
     *
     * @return bool
     */
    public function opened()
    {
        return $this->connect->ping();
    }

    public function close()
    {
        if (!($closed = $this->connect->close()))
            throw new DbException('can\'t close database connection: ' . $this->connect->error, $this->connect->errno);

        return true;
    }

    public function __wakeup()
    {
        $this->open();
    }

    /**
     * @return \mysqli
     */
    public function getConnect()
    {
        return $this->connect;
    }


    public function getNumRows()
    {
        return $this->result->num_rows;
    }

    public function getAffectedRows()
    {
        return $this->connect->affected_rows;
    }

    public function getResult($row = 0, $field = 0)
    {
        $this->result->data_seek($row);
        $this->result->field_seek($field);

        return $this->result->fetch_field();
    }

//	public	function	getResult($row=0,$field=0){	return mysql_result($this->result,$row,$field);	}
    public function lastInsertId()
    {
        return $this->connect->insert_id;
    }

    public function fetchArray($param = MYSQLI_ASSOC)
    {
        return $this->result->fetch_array($param);
    }

    public function fetchRow()
    {
        return $this->result->fetch_row();
    }

    public function fetchAssoc()
    {
        return $this->result->fetch_assoc();
    }

    /**
     * @param int $param fetch type
     *
     * @return array
     */
    public function    &fetchAllRows($param = MYSQLI_ASSOC)
    {
        $return = [];
        while ($row = $this->fetchArray($param))
            $return[] = $row;

        return $return;
    }

    public function fetchLastRow()
    {
        $this->result->data_seek($this->getNumRows() - 1);

        return $this->result->fetch_row();
    }

    /**
     *
     * @param string $query_string
     *
     * @return bool
     * @throws SqlException
     * @global int   $time
     */
    public function query($query_string)
    {
        if (self::$monitorQueries) {
            $queryStartTime   = microtime(true);
            self::$strQueries .= $query_string . "\n";
        }

        $this->result = $this->dbQuery($query_string);

        if (self::$monitorQueries) {
            global $time;
            $queryEndTime  = microtime(true);
            $curScriptTime = $queryEndTime - $time;
            /** @var int $queryStartTime */
            $queryExecTime    = $queryEndTime - $queryStartTime;
            self::$strQueries .= '  Script time: ' . round($curScriptTime, 8) . "\n";
            self::$strQueries .= '  Query  time: ' . round($queryExecTime, 8) . "\n";
        }

        return true;
    }

    static
    public function getQueryTemplateArray($tpl, $argArr)
    {
        $argNum = count($argArr);
        for ($i = $argNum; $i > 0; $i--)
            $tpl = str_replace('%' . $i, $argArr[$i - 1], $tpl);

        return $tpl;
    }

    static
    public function getQueryTemplate($tpl)
    {
        $argList = func_get_args();
        $argNum  = func_num_args();

        $strQuery = $tpl;
        for ($i = $argNum - 1; $i > 0; $i--)
            $strQuery = str_replace('%' . $i, $argList[$i], $strQuery);

        return $strQuery;
    }

    public function queryTemplate($tpl)
    {
        $argList  = func_get_args();
        $strQuery = call_user_func_array(['self', 'getQueryTemplate'], $argList);

        return $this->query($strQuery);
    }

    /**
     * @param string $query_string
     *
     * @return bool|\mysqli_result
     *
     * @throws SqlException
     */
    private function    &dbQuery($query_string)
    {
        if (self::$monitorQueries)
            self::$queriesCount++;
        $result = $this->connect->query($query_string);
        if ($result === false) {
            throw new SqlException(
                'SQL-error [' . $this->connect->errno . ']: ' . $this->connect->error . "\nSQL-query: $query_string",
                $this->connect->errno
            );
        }

        return $result;
    }

    public function transactionStart()
    {
        return $this->query('START TRANSACTION;');
    }

    public function transactionRollback()
    {
        return $this->query('ROLLBACK;');
    }

    public function transactionCommit()
    {
        return $this->query('COMMIT;');
    }

    /**
     *
     * @param string $tableName
     *
     * @return array поля: [TABLE_SCHEMA] ,TABLE_NAME, COLUMN_NAME    refs to   [REFERENCED_TABLE_SCHEMA ],
     *               REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
     * @throws SqlException
     */
    public function getTableFKs($tableName)
    {
        // TODO: доделать
        // поля: [TABLE_SCHEMA] ,TABLE_NAME, COLUMN_NAME    refs to   [REFERENCED_TABLE_SCHEMA ], REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        $this->query('SELECT * FROM `KEY_COLUMN_USAGE` WHERE `TABLE_NAME` = \'' . $tableName . '\' AND `REFERENCED_COLUMN_NAME` IS NOT NULL');

        return $this->fetchAllRows();
    }

    public function commit(array $arrQueries)
    {
        if (!$this->transactionStart()) return false;
        if (!$this->queries($arrQueries, true)) return false;
        if (!$this->transactionCommit()) return false;

        return true;
    }

    public function prepareValue(&$value, $type)
    {
        if ($value === null)
            return $value = 'NULL';

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

    /**
     * @param string $tableName
     *
     * @return array
     * @throws DbException
     * @throws SqlException
     */
    protected function &retrieveColumnsMetadata($tableName)
    {
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
     * @param $strFieldType
     *
     * @return int|null
     */
    private function &extractFieldTypeLength(&$strFieldType)
    {
        $len = explode(")", $strFieldType);
        $len = explode("(", $len[0]);
        if (count($len) > 1)
            $len = &$len[1];
        else
            $len = null;

        return $len;
    }
}
