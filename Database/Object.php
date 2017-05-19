<?php
namespace Colibri\Database;

use Carbon\Carbon;
use Colibri\Base\SqlException;

/**
 * Абстрактный класс объекта базы данных.
 *
 * @author		Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package		Colibri
 * @subpackage	Database
 *
 * @property         int|string  $id
 *
 * @method string createQuery()
 * @method string deleteQuery()
 * @method string saveQuery()
 * @method string loadQuery()
 */
abstract
class Object implements IObject
{
	const	NEW_OBJECT=-1;
	const	LOAD_ERROR=-2;

	static	public	$debug=true;

	protected	static $tableName	='tableName_not_set';
	protected	static $PKFieldName=['id'];

	protected	$fieldsNameValuesArray=null;
	protected	$where=null;

	protected	$fields=[];
	protected	$fieldTypes=[];
	public      $fieldLengths = [];

    /**
     * @deprecated
     */
	public		$error_message='';
    /**
     * @deprecated
     */
	public		$error_number=0;


    protected static $connectionName = 'default';

	/**
	 * @var array
	 */
	protected	$collections=[];
	/**
	 * @var array
	 */
	protected	$objects=[];


    /**
     * @param int|array $id_or_row
     *
     * @throws DbException
     * @throws \Exception
     */
    public		function	__construct($id_or_row=null)
	{
        $metadata           = static::db()->getColumnsMetadata(static::$tableName);
        $this->fields       = &$metadata['fields'];
        $this->fieldTypes   = &$metadata['fieldTypes'];
        $this->fieldLengths = &$metadata['fieldLengths'];

		if ($id_or_row===null)
			$this->{static::$PKFieldName[0]}=self::NEW_OBJECT;
		else
			if (is_array($id_or_row))
				$this->fillProperties($id_or_row);
			else
				if (!$this->load($id_or_row))
					$this->{static::$PKFieldName[0]}=self::LOAD_ERROR;
	}

    /**
     * @param string $everyFieldPrefix
     *
     * @return string
     */
	public		function	getFieldsNamesList($everyFieldPrefix='')
	{
		$classVars=array_keys(get_class_vars(get_class($this)));
		$selectedFields=array_intersect($classVars,$this->fields);

		return $everyFieldPrefix.'`'.implode('`, '.$everyFieldPrefix.'`',$selectedFields).'`';
	}

    /**
     * @return array
     */
	public		function	getPKFieldName()
	{
		return static::$PKFieldName;
	}

    /**
     * @return string
     */
	final public function getTableName()
	{
		return static::$tableName;
	}

    /**
     * @return IDb
     * @throws DbException
     */
	final public static function db()
    {
        return Db::connection(static::$connectionName);
    }
	/**************************************************************************/
    /**
     * @param $name
     * @param $value
     *
     * @return string
     * @throws DbException
     */
	protected	function	buildNameEqValue($name,$value)
	{
		$value=self::db()->prepareValue($value,$this->fieldTypes[$name]);

		return '`'.$name.'`='.$value;
	}

    /**
     * @param string $fieldPrefix
     *
     * @return string
     * @throws DbException
     */
	protected	function	getFieldsNameValueList($fieldPrefix='')
	{
		$obj=$this->fieldsNameValuesArray===null?$this:$this->fieldsNameValuesArray;

		$strList='';
        foreach ($obj as $propName => $propValue)
            if (in_array($propName, $this->fields) && (
                $this->fieldsNameValuesArray === null
                    ? !in_array($propName, static::$PKFieldName)
                    : true
                )
            )
                $strList .= ', ' . $fieldPrefix . $this->buildNameEqValue($propName, $propValue);

		return substr($strList,2);
	}

    /**
     * @param string $fieldPrefix
     *
     * @return string
     * @throws DbException
     */
	protected	function	getWhereCondition($fieldPrefix='')
	{
		$strList='';
		foreach ($this->where as $name => $value)
			$strList.=' AND '.$fieldPrefix.$this->buildNameEqValue($name,$value);

		return substr($strList,5);
	}

    /**
     * @param string $fieldPrefix
     *
     * @return string
     * @throws DbException
     */
	protected	function	getPKCondition($fieldPrefix='')
	{
		$strList='';
		foreach (static::$PKFieldName as $PKName)
			$strList.=' AND '.$fieldPrefix.$this->buildNameEqValue($PKName,$this->$PKName);

		return substr($strList,5);
	}

    /**
     * @param array $row
     */
	protected	function	fillProperties(array $row)
	{
		foreach ($row as $propName => $propValue) {
			if ($propValue === null) {
				$this->$propName = $propValue;
				continue;
			}
			$type = isset($this->fieldTypes[$propName]) ? $this->fieldTypes[$propName] : null;
			switch ($type) {
				case 'bit':       $this->$propName = (bool)ord($propValue);break;
				case 'timestamp': $this->$propName = new Carbon($propValue);break;
				default:          $this->$propName = $propValue;break;
			}
		}
	}

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @throws \Exception
     */
    public		function	__call($name,$arguments)
	{
		switch ($name)
		{
			case 'createQuery':
                /** @noinspection SqlNoDataSourceInspection */
                $tpl ='INSERT INTO `'.static::$tableName.'` SET '  .$this->getFieldsNameValueList();break;
			case 'deleteQuery':
                /** @noinspection SqlNoDataSourceInspection */
			    $tpl='DELETE FROM `'.static::$tableName.'` WHERE '.$this->getPKCondition();break;
			case 'saveQuery':
			    $tpl='UPDATE `'     .static::$tableName.'` SET '  .$this->getFieldsNameValueList().' WHERE '.$this->getPKCondition();break;
			case 'loadQuery':
			    $tpl='SELECT '.$this->getFieldsNamesList().' FROM `'.static::$tableName.'` WHERE '.($this->where===null?$this->getPKCondition():$this->getWhereCondition());
			    break;
			default: throw new \Exception('unknown query __called method name.');
		}

		return self::db()->getQueryTemplateArray($tpl,$arguments);
	}

    /**
     * @param string $propertyName
     *
     * @return ObjectCollection|ObjectMultiCollection|ObjectSingleCollection|Object
     * @throws \Exception
     */
	public		function	__get($propertyName)
	{
		if (isset($this->collections[$propertyName]))
		{
			return $this->getRelated($propertyName, $this->collections);
		}
		if (isset($this->objects[$propertyName]))
		{
			return $this->getRelated($propertyName, $this->objects);
		}
		throw new \Exception('свойство $'.$propertyName.' в классе '.get_class($this).' не определено или не является public.');
	}

    /**
     * @param $name
     * @param $relationsDefinition
     *
     * @return Object|ObjectCollection|ObjectSingleCollection|ObjectMultiCollection
     */
    private     function    getRelated($name, &$relationsDefinition)
    {
        $container          = &$relationsDefinition[$name];
        $relatedObject      = &$container[1];
        $relatedObjectClass = $container[0];
        $objectFKName       = isset($container[2]) ? $container[2] : static::$PKFieldName[0]; // TODO:

        return $relatedObject === null
            ? $relatedObject = new $relatedObjectClass($this->$objectFKName) // TODO: ссылки бывают не только на PK
            : $relatedObject;
    }

    /**
     * @param $propertyName
     * @param $propertyValue
     *
     * @return mixed
     * @throws \Exception
     */
	public		function	__set($propertyName,$propertyValue)
	{
		if (in_array($propertyName,static::$PKFieldName))
			return $this->$propertyName=$propertyValue;

		throw new \Exception('свойство $'.$propertyName.' в классе '.get_class($this).' не определено или не является public.');
	}

    /**
     * @param array $where
     *
     * @return $this
     */
	public		function	where(array $where)
	{
		$this->where=$where;
		return $this;
	}

    /**
     * @param array $where
     *
     * @return bool
     * @throws \Exception
     */
    protected static function recordExists(array $where)
    {
        $dbObject = new static();
        $loaded   = $dbObject->load($where);
        if ($loaded === false) {
            throw new \Exception('can`t load user. where clause: ' . var_export($where));
        }

        return $loaded !== null;
    }

    /**
     * @param array $attributes
     *
     * @return bool
     * @throws DbException
     * @throws \Exception
     */
	public		function	create(array $attributes=null)
	{
		$this->fieldsNameValuesArray=$attributes;
		if (!$this->doQuery($this->createQuery()))
			return false;
		$this->{static::$PKFieldName[0]}=self::db()->lastInsertId();
		if ($attributes) {
			$this->fillProperties($attributes);
		}
		return true;
	}

    /**
     * @param null $id_or_where
     *
     * @return bool
     * @throws DbException
     * @throws \Exception
     */
	public		function	delete($id_or_where=null)
	{
		if ($id_or_where!==null)
			if (is_array($id_or_where))
				$this->where=$id_or_where;
			else
			{
				$this->where=null;
				$this->{static::$PKFieldName[0]}=$id_or_where;
			}
		else
			;
		return	$this->doQuery($this->deleteQuery());
	}

    /**
     * @param array $attributes
     *
     * @return bool
     * @throws DbException
     * @throws \Exception
     */
	public		function	save(array $attributes=null)
	{
		$this->fieldsNameValuesArray=$attributes;
		return	$this->doQuery($this->saveQuery());
	}

    /**
     * @param array $values
     *
     * @return static
     * @throws DbException
     * @throws \Exception
     */
	public static function saveNew(array $values)
	{
		$object = new static();
		$object->create($values);
		return $object;
	}

	public		function	reload()	{	return	$this->load();	}

    /**
     * @param null $id_or_where
     *
     * @return bool|null
     * @throws DbException
     * @throws \Exception
     */
    public		function	load($id_or_where=null)
	{
		if ($id_or_where!==null)
			if (is_array($id_or_where))
				$this->where=$id_or_where;
			else
			{
				$this->where=null;
				$this->{static::$PKFieldName[0]}=$id_or_where;
			}
		else
			;
		if (!$this->doQuery($this->loadQuery()))return false;// sql error
        if (self::db()->getNumRows()==0)		return null; // no  record
		if (!$result=self::db()->fetchArray())	return false;
		$this->fillProperties($result);
		return	true;
	}
	/**
	 *
	 * @param mixed $id PK value - int, string or array if multifield PK
	 * @return static
	 * @throws \Exception
	 */
static
	public		function	getById($id)
	{
		$object=new static();
		if (!$object->load($id))
			throw new \Exception('no record');
		return $object;
	}

    /**
     * @param $sqlQuery
     *
     * @return bool|null
     * @throws DbException
     * @throws \Exception
     */
	protected	function	loadByQuery($sqlQuery)
	{
		if (!$this->doQuery($sqlQuery))			return false;// sql error
		if (self::db()->getNumRows()==0)		return null; // no  record
		if (!$result=self::db()->fetchArray())	return false;
		$this->fillProperties($result);
		return	true;
	}

    /**
     * @param array $row
     */
	public		function	initialize(array $row)
    {
        $this->fillProperties($row);
    }

    /**
     * @deprecated will be removed
     * @return string
     */
	public		function	getFieldsAsXMLstring()
	{
		$strXMLPart='';
		foreach ($this as $propName => $propValue)
			if (in_array($propName,$this->fields))
				$strXMLPart.='<'.$propName.'>'.(is_null($propValue)?'<null />':$propValue).'</'.$propName.'>';
		return $strXMLPart;
	}

    /**
     * @return array
     */
	public		function	toArray()
	{
		$arrRet=[];
		foreach ($this as $propName => $propValue)
			if (in_array($propName,$this->fields))
				$arrRet[$propName]=$propValue;
		return $arrRet;
	}

    /**
     * @return string
     */
    public		function	toJson()
    {
        return json_encode($this->toArray());
    }

	/**
     * @deprecated use Exceptions
	 *
     * @param string $strQuery
     * @param string $type     sql|internal
     * @param int    $errno
     *
     * @return bool
     * @throws \Exception
     */
    protected function    setError($strQuery,$type='sql',$errno=-128)
	{
		$cls=get_class($this);
		switch ($type)
		{
			case 'sql':
				/** @noinspection PhpUndefinedMethodInspection @todo: remove this functionality in favor of Exceptions */
                $errno=self::db()->getLastErrno();
                /** @noinspection PhpUndefinedMethodInspection @todo: remove this functionality in favor of Exceptions */
				$this->error_message=
					$cls."\n".
					'SQL-error ['.$errno.']: '.self::db()->getLastError()."\n".
					'SQL-query: '.$strQuery;
				break;
			case 'internal':
				$this->error_message=
					$cls."\n".
					'internal error: '.$strQuery;
				break;
			default:
				throw new \Exception('unknown error type');
		}
		$this->error_number=$errno;
		return true;
	}

    /**
     * @deprecated use Exceptions
     *
     * @param string $strQuery
     *
     * @return bool
     * @throws \Exception
     */
	protected	function	setSqlError($strQuery)
	{
		return $this->setError($strQuery,'sql');
	}

    /**
     * @param string $strQuery
     *
     * @return bool true on success or false on failure (if no exeptions on)
     * @throws DbException
     * @throws SqlException
     * @throws \Exception
     */
	protected	function	doQuery($strQuery)
	{
		if (!self::db()->query($strQuery))
			return !$this->setSqlError($strQuery);

		$this->cleanUpQueryVars();

		return true;
	}

    /**
     * @param array $arrQueries
     *
     * @return bool
     * @throws DbException
     * @throws \Exception
     */
	protected	function	doQueries(array $arrQueries)
	{
		if (!self::db()->queries($arrQueries))
			return !$this->setSqlError(print_r($arrQueries,true));
		return true;
	}

    /**
     * @param $arrQueries
     *
     * @return bool
     * @throws DbException
     * @throws \Exception
     */
	protected	function	doTransaction($arrQueries)
	{
		if (!self::db()->commit($arrQueries))
			return !$this->setSqlError(print_r($arrQueries,true));
		return true;
	}

    /**
     * bring out into Query class
     */
    private function cleanUpQueryVars()
    {
        $this->where = null;
        $this->fieldsNameValuesArray = null;
    }
}
