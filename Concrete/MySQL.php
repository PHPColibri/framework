<?php
namespace Colibri\Database\Concrete;

use Colibri\Base\SqlException;
use Colibri\Database\DbException;
use Colibri\Database\IDb;
use Colibri\Database\AbstractDb;

/**
 * DbMySQL Класс для работы с MySQL
 *
 * @author		Александр Чибрикин aka alek13 <alek13.me@gmail.com> (some 1.x versions and v. 2.x+)
 * @version		2.1.0
 * @package		xTeam
 * @subpackage	a13FW
 *
 * @property-read bool $noConnectToMemcacheServer
 */
class MySQL extends AbstractDb
{
    /** @var \mysqli */
	private $connect;
	private $lastError = ['error'=>null,'errno'=>null];
	private $host;
	private $login;
	private $pass;
	private $database;
    /** @var  \mysqli_result */
	private $result;
	private $persistent;

	static	public	$throwExceptions=true;

	static	public	$monitorQueries=false;
	static	public	$strQueries='';
	static	public	$queriesCount = 0;
	static	public	$cachedQueriesCount = 0;

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
     */
    function __construct($host, $login, $pass, $database, $persistent = false)
	{
		$this->host = $host;
		$this->login = $login;
		$this->pass = $pass;
		$this->lastError = NULL;
		$this->resource = NULL;
		$this->database = $database;
		$this->persistent = $persistent;

		if (!$this->open())
			throw new DbException('can\'t connect to datadase: SQL-error['.$this->getLastErrno().']: '.$this->getLastError());
	}
	/**
	 * Открывает соединение с базой данных
	 *
	 */
	public function open(/*$encoding = 'utf8'*/)
	{
		if (self::$monitorQueries)
		{
			self::$strQueries.="Before @mysqli_connect\n";
			global $time;
			$curTime=microtime(true)-$time;
			self::$strQueries.=sprintf('%f',$curTime)."\n";
		}

        $this->connect = new \mysqli($this->persistent ? 'p:' : '' . $this->host,$this->login,$this->pass);

		if ( ! $this->connect)
			return !$this->setLastError();

		if($this->connect->select_db($this->database)===false)
			return !$this->setLastError();

		$this->pass=null;

		$this->query("SET CHARACTER SET 'utf8'"/*, $encoding*/);
		return true;
	}
	public	function	close()				{	return $this->connect->close() || !$this->setLastError();	}
	public	function	__wakeup()			{	$this->open();						}

    /**
     * @return \mysqli
     */
    public	function	getConnect()		{	return $this->connect;				}

	public	function	getLastErrno()		{	return $this->lastError['errno'];	}
	public	function	getLastError()		{	return $this->lastError['error'];	}
	private	function	setLastError()
	{
		$this->lastError['errno'] = $this->connect->errno;
		$this->lastError['error'] = $this->connect->error;
		return true;
	}

	public	function	getNumRows()			{	return $this->result->num_rows;			}
	public	function	getAffectedRows()		{	return $this->connect->affected_rows;		}
	public	function	getResult($row=0,$field=0)
    {
        $this->result->data_seek($row);
        $this->result->field_seek($field);
        return $this->result->fetch_field();
    }
//	public	function	getResult($row=0,$field=0){	return mysql_result($this->result,$row,$field);	}
	public	function	lastInsertId()			{	return $this->connect->insert_id;			}

	public	function	fetchArray($param=MYSQLI_ASSOC)	{	return $this->result->fetch_array($param);	}
	public	function	fetchRow()						{	return $this->result->fetch_row();		}
	public	function	fetchAssoc()					{	return $this->result->fetch_assoc();		}
	/**
	 * @param int $param fetch type
	 * @return array
	 */
	public	function	fetchAllRows($param=MYSQLI_ASSOC)
	{
		$return=[];
		while ($row=$this->fetchArray($param))
			$return[]=$row;

		return $return;
	}

	public	function	fetchLastRow()
	{
        $this->result->data_seek($this->getNumRows()-1);
        return $this->result->fetch_row();
	}
	/**
	 *
	 * @global int $time
	 * @param string $query_string
	 * @return bool
	 */
	public	function	query($query_string)
	{
		if (self::$monitorQueries) {
			$queryStartTime = microtime(true);
			self::$strQueries .= $query_string."\n";
		}

		$this->result = $this->dbQuery($query_string);

		if (self::$monitorQueries) {
			global $time;
			$queryEndTime=microtime(true);
			$curScriptTime=$queryEndTime-$time;
            /** @var int $queryStartTime */
            $queryExecTime=$queryEndTime-$queryStartTime;
			self::$strQueries.='  Script time: '.round($curScriptTime,8)."\n";
			self::$strQueries.='  Query  time: '.round($queryExecTime,8)."\n";
		}

		if ($this->result === false)
			return false;

		return true;
	}
static
	public	function	getQueryTemplateArray($tpl,$argArr)
	{
		$argNum=count($argArr);
		for ($i=$argNum;$i>0;$i--)
			$tpl=str_replace('%'.$i,$argArr[$i-1],$tpl);

		return $tpl;
	}
static
	public	function	getQueryTemplate($tpl)
	{
		$argList=func_get_args();
		$argNum =func_num_args();

		$strQuery=$tpl;
		for ($i=$argNum-1;$i>0;$i--)
			$strQuery=str_replace('%'.$i,$argList[$i],$strQuery);

		return $strQuery;
	}
	public	function	queryTemplate($tpl)
	{
		$argList=func_get_args();
		$strQuery=call_user_func_array(['self','getQueryTemplate'],$argList);

		return $this->query($strQuery);
	}

    /**
     * @param string $query_string
     *
     * @return bool|\mysqli_result
     *
     * @throws SqlException
     */
    private	function	dbQuery($query_string)
	{
		if (self::$monitorQueries)
			self::$queriesCount++;
		$result=$this->connect->query($query_string);
		if ($result===false)
		{
			$this->setLastError();
			if (self::$throwExceptions)
				throw new SqlException(
					'SQL-error ['.$this->getLastErrno().']: '.$this->getLastError()."\nSQL-query: $query_string",
					9999, // TODO: waste this error code
					$this->getLastErrno()
				);
			return false;
		}
		return $result;
	}

	public	function	transactionStart()		{	return $this->query('START TRANSACTION;');	}
	public	function	transactionRollback()	{	return $this->query('ROLLBACK;');			}
	public	function	transactionCommit()		{	return $this->query('COMMIT;');				}

	/**
	 *
	 * @param string $tableName
	 * @return array  поля: [TABLE_SCHEMA] ,TABLE_NAME, COLUMN_NAME    refs to   [REFERENCED_TABLE_SCHEMA ], REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
	 */
	public	function	getTableFKs($tableName)
	{
		// TODO: доделать
		// поля: [TABLE_SCHEMA] ,TABLE_NAME, COLUMN_NAME    refs to   [REFERENCED_TABLE_SCHEMA ], REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
		$this->query('SELECT * FROM `KEY_COLUMN_USAGE` WHERE `TABLE_NAME` = \''.$tableName.'\' AND `REFERENCED_COLUMN_NAME` IS NOT NULL');
		return $this->fetchAllRows();
	}

	public	function	commit(array $arrQueries)
	{
		if (!$this->transactionStart())			return false;
		if (!$this->queries($arrQueries,true))	return false;
		if (!$this->transactionCommit())		return false;
		return true;
	}
static
	public	function	prepareValue(&$value,$type)
	{
		if ($value===null)
			return $value='NULL';

		switch (strtolower($type))
		{
			case 'timestamp':
				$value = is_int($value) ?
					'\'' . date('Y-m-d H:i:s', $value) . '\'' :
					($value instanceof \DateTime ?
						'\'' . $value->format('Y-m-d H:i:s') . '\'' :
						'\'' . addslashes($value) . '\''
					);
				break;

			case 'bit':		$value=(int)intval($value);break;

			case 'dec':
			case 'decimal':
			case 'tinyint':
			case 'smallint':
			case 'bigint':
			case 'int':		$value=(int)intval($value);break;
			case 'double':
			case 'float':	$value=(float)floatval($value);break;

			default:		$value='\''.addslashes($value).'\'';
		}

		return $value;
	}

    /**
     * @param string $tableName
     *
     * @return array
     */
    protected function &retrieveColumnsMetadata($tableName)
    {
        $sql = 'SHOW COLUMNS FROM ' . $tableName;
        if (!$this->query($sql)) {
            throw new DbException($this->getLastError());
        }
        $result = $this->fetchAllRows();

        $fields = [];
        $fieldTypes = [];
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
            'fieldLengths' => &$fieldLengths
        ];

        return $returnArray;
    }

    /**
     * @param $strField
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
