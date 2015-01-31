<?php
namespace Colibri\Database;

use Colibri\Cache\Memcache;

/**
 * Абстрактный класс объекта базы данных.
 * прим.: версия 1.08+ возможно не совместима с предыдущими.
 *
 * @author		Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package		Colibri
 * @subpackage	Database
 * @version		1.12.4
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

	static	public	$useMemcache=false;
	static	public	$debug=true;

	protected	static $tableName	='tableName_not_set';
	protected	static $PKFieldName=['id'];

	protected	$fieldsNameValuesArray=null;
	protected	$where=null;

	protected	$fields=[];
	protected	$fieldTypes=[];
	public      $fieldLengths = [];

	public		$error_message='';
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

	private function getFieldTypeLength($strField)
	{
		$ex1 = explode(")", $strField);
		$ex2 = explode("(",$ex1[0]);
		if(count($ex2)> 1)
			return $ex2[1];
		return null;
	}

    /**
     * @param int|array $id_or_row
     * @param array     $fieldsAndTypes
     */
    public		function	__construct($id_or_row=null,&$fieldsAndTypes=null)
	{
		if ($fieldsAndTypes===null)
		{
			// TODO: bring out into Database\MySQL
			$sql='SHOW COLUMNS FROM '.static::$tableName;
			if (self::$useMemcache)
			{
				$mc_key=hash('md5',$sql);
				if (($result=Memcache::get($mc_key))===false)
				{
					if (!$this->doQuery($sql))	{ unset($this);return false;}
					$result=self::db()->fetchAllRows();
					Memcache::set($mc_key,$result);
				}
			}
			else
			{
				if (!$this->doQuery($sql))	{ unset($this);return false;}
				$result=self::db()->fetchAllRows();
			}
			$cnt=count($result);
			for ($i=0;$i<$cnt;$i++)
			{
				$this->fields[]=$result[$i]['Field'];
				$type=explode('(',$result[$i]['Type']);
				$this->fieldTypes[$result[$i]['Field']]=$type[0];
				$length = $this->getFieldTypeLength($result[$i]['Type']);
				$this->fieldLengths[$result[$i]['Field']] = $length;
			}
		}
		else
		{
			$this->fields		=&$fieldsAndTypes['fields'];
			$this->fieldTypes	=&$fieldsAndTypes['types'];
		}

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
	public		function	getTableName()
	{
		return static::$tableName;
	}

    /**
     * @return IDb
     */
	final public static function db()
    {
        return Db::connection(static::$connectionName);
    }
	/**************************************************************************/
	protected	function	buildNameEqValue($name,$value)
	{
		$value=self::db()->prepareValue($value,$this->fieldTypes[$name]);

		return '`'.$name.'`='.$value;
	}
	protected	function	getFieldsNameValueList($fieldPrefix='')
	{
		$obj=$this->fieldsNameValuesArray===null?$this:$this->fieldsNameValuesArray;

		$strList='';
		foreach ($obj as $propName => $propValue)
			if (in_array($propName,$this->fields) && (
					$this->fieldsNameValuesArray===null ?
						!in_array($propName,static::$PKFieldName) :
						true
			))
				$strList.=', '.$fieldPrefix.$this->buildNameEqValue($propName,$propValue);

		return substr($strList,2);
	}
	protected	function	getWhereCondition($fieldPrefix='')
	{
		$strList='';
		foreach ($this->where as $name => $value)
			$strList.=' AND '.$fieldPrefix.$this->buildNameEqValue($name,$value);

		return substr($strList,5);
	}
	protected	function	getPKCondition($fieldPrefix='')
	{
		$strList='';
		foreach (static::$PKFieldName as $PKName)
			$strList.=' AND '.$fieldPrefix.$this->buildNameEqValue($PKName,$this->$PKName);
		return substr($strList,5);
	}
	public		function	setPKValue(array $id)
	{
		// TODO [alek13]: implement protected method setPKValue(array id) or __set('PK',array id) ...  (to make posible to set an PK as array), хотя зачем ?
		throw new \Exception('method not implemented yet.');
	}

	protected	function	fillProperties(array $row)
	{
		foreach ($row as $propName => $propValue)
            if (isset($this->fieldTypes[$propName]) && strtolower($this->fieldTypes[$propName])=='bit')
                $this->$propName=(bool)ord($propValue);//$row[$propName];
            else
                $this->$propName=$propValue;//$row[$propName];
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
		// TODO [alek13]: ??? parse '...Query()' methods
		switch ($name)
		{
			case 'createQuery':		$tpl='INSERT INTO `'.static::$tableName.'` SET '  .$this->getFieldsNameValueList();break;
			case 'deleteQuery':		$tpl='DELETE FROM `'.static::$tableName.'` WHERE '.$this->getPKCondition();break;
			case 'saveQuery':		$tpl='UPDATE `'     .static::$tableName.'` SET '  .$this->getFieldsNameValueList().' WHERE '.$this->getPKCondition();break;
			case 'loadQuery':		$tpl='SELECT '.$this->getFieldsNamesList().' FROM `'.static::$tableName.'` WHERE '.($this->where===null?$this->getPKCondition():$this->getWhereCondition());break;
			default: throw new \Exception('unknown query __called method name.');
		}
        // @todo:
		return self::db()->getQueryTemplateArray($tpl,$arguments);
	}
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
	public		function	__set($propertyName,$propertyValue)
	{
		if (in_array($propertyName,static::$PKFieldName))
			return $this->$propertyName=$propertyValue;

		//if (self::$debug)
			throw new \Exception('свойство $'.$propertyName.' в классе '.get_class($this).' не определено или не является public.');
		//else
		//	$this->$propertyName=$propertyValue;
	}

	public		function	where(array $where)
	{
		$this->where=$where;
		return $this;
	}
	/**
	 * @param array $fieldsNameValuesArray
	 * @return bool
	 */
	public		function	create(array $fieldsNameValuesArray=null)
	{
		$this->fieldsNameValuesArray=$fieldsNameValuesArray;
		if (!$this->doQuery($this->createQuery()))
			return false;
		$this->{static::$PKFieldName[0]}=self::db()->lastInsertId();
		return true;
	}
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
	public		function	save($fieldsNameValuesArray=null)
	{
		$this->fieldsNameValuesArray=$fieldsNameValuesArray;
		return	$this->doQuery($this->saveQuery());
	}
	public		function	reload()	{	return	$this->load();	}

    /**
     * @param null $id_or_where
     *
     * @return bool|null
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
	 * @return \static
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

	protected	function	loadByQuery($sqlQuery)
	{
		if (!$this->doQuery($sqlQuery))			return false;// sql error
		if (self::db()->getNumRows()==0)		return null; // no  record
		if (!$result=self::db()->fetchArray())	return false;
		$this->fillProperties($result);
		return	true;
	}

	public		function	initialize($row){	$this->fillProperties($row);				}

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
	 *
     * @param string $strQuery
     * @param string $type sql|internal
     * @param int $errno
     *
     * @return type
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
	protected	function	setSqlError($strQuery)
	{
		return $this->setError($strQuery,'sql');
	}
	/**
	 * @param string $strQuery
	 * @return bool true on success or false on failure (if no exeptions on)
	 */
	protected	function	doQuery($strQuery)
	{
		if (!self::db()->query($strQuery))
			return !$this->setSqlError($strQuery);
		return true;
	}
	protected	function	doQueries(array $arrQueries)
	{
		if (!self::db()->queries($arrQueries))
			return !$this->setSqlError(print_r($arrQueries,true));
		return true;
	}
	protected	function	doTransaction($arrQueries)
	{
		if (!self::db()->commit($arrQueries))
			return !$this->setSqlError(print_r($arrQueries,true));
		return true;
	}
}
