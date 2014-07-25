<?php
namespace Colibri\Database;

use Colibri\Database\MySQL;
use Colibri\Database\IDb;
use Colibri\Cache\Memcache;

/**
 * Абстрактный класс объекта базы данных.
 * прим.: версия 1.08+ возможно не совместима с предыдущими.
 *
 * @author		Александр Чибрикин aka alek13 <chibrikinalex@mail.ru>
 * @package		xTeam
 * @subpackage	a13FW
 * @version		1.12.4
 *
 * @exception	3xx
 *
 * @property-read	array	PKFieldName
 */
abstract
class Object implements IObject
{
	const	NEW_OBJECT=-1;
	const	LOAD_ERROR=-2;

	static	public	$useMemcache=false;
	static	public	$debug=true;
	
	protected	$tableName	='tableName_not_set';
	protected	$PKFieldName=array('id');

	protected	$fieldsNameValuesArray=null;
	protected	$where=null;

	protected	$fields=array();
	protected	$fieldTypes=array();
	public      $fieldLengths = array();

	public		$error_message='';
	public		$error_number=0;
	/**
	 * @var IDb 
	 */
	protected	$db;
	
	/**
	 * @var array 
	 */
	protected	$collections=array();
	/**
	 * @var array 
	 */
	protected	$objects=array();

	private function getFieldTypeLength($strField)
	{
		$ex1 = explode(")", $strField);
		$ex2 = explode("(",$ex1[0]);
		if(count($ex2)> 1)
			return $ex2[1];
		return null;
	}

	public		function	__construct(IDb &$db,$id_or_row=null,&$fieldsAndTypes=null)
	{
		$this->db=&$db;
		if ($fieldsAndTypes===null)
		{
			// TODO: bring out into Database\MySQL
			$sql='SHOW COLUMNS FROM '.$this->tableName;
			if (self::$useMemcache)
			{
				$mc_key=hash('md5',$sql);
				if (($result=Memcache::get($mc_key))===false)
				{
					if (!$this->doQuery($sql))	{ unset($this);return false;}
					$result=$this->db->fetchAllRows();
					Memcache::set($mc_key,$result);
				}
			}
			else
			{
				if (!$this->doQuery($sql))	{ unset($this);return false;}
				$result=$this->db->fetchAllRows();
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
			$this->{$this->PKFieldName[0]}=self::NEW_OBJECT;
		else
			if (is_array($id_or_row))
				$this->fillProperties($id_or_row);
			else
				if (!$this->load($id_or_row))
					$this->{$this->PKFieldName[0]}=self::LOAD_ERROR;
	}
	/*
	public	function	getFieldsNameList($specificClassName=null,$everyFieldPrefix='')
	{
		$strList='';
		$cName=$specificClassName===null?get_class($this):$specificClassName;
		$selectedFields=array_keys(get_class_vars($cName));

		foreach ($this as $propName => $propValue)
			if (in_array($propName,$selectedFields)&&in_array($propName,$this->fields))
				$strList.=', '.$everyFieldPrefix.$propName;

		return substr($strList,2);
		/*foreach ($selectedFields as $propName)
			$strList.=', '.$everyFieldPrefix.$propName;
		return substr($strList,2);*//*
	}
	*/
	public		function	getFieldsNamesList($everyFieldPrefix='')
	{
		$classVars=array_keys(get_class_vars(get_class($this)));
		$selectedFields=array_intersect($classVars,$this->fields);
		
		return $everyFieldPrefix.'`'.implode('`, '.$everyFieldPrefix.'`',$selectedFields).'`';
	}
	public		function	getPKFieldName()
	{
		return $this->PKFieldName;
	}
	public		function	getTableName()
	{
		return $this->tableName;
	}
	
	/**************************************************************************/
	protected	function	buildNameEqValue($name,$value)
	{
		$value=MySQL::prepareValue($value,$this->fieldTypes[$name]);

		return '`'.$name.'`='.$value;
	}
	protected	function	getFieldsNameValueList($fieldPrefix='')
	{
		$obj=$this->fieldsNameValuesArray===null?$this:$this->fieldsNameValuesArray;
		
		$strList='';
		foreach ($obj as $propName => $propValue)
			if (in_array($propName,$this->fields) && (
					$this->fieldsNameValuesArray===null ?
						!in_array($propName,$this->PKFieldName) :
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
		foreach ($this->PKFieldName as $PKName)
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
	public		function	__call($name,$arguments)
	{
		// TODO [alek13]: ??? parse '...Query()' methods
		switch ($name)
		{
			case 'createQuery':		$tpl='INSERT INTO `'.$this->tableName.'` SET '  .$this->getFieldsNameValueList();break;
			case 'deleteQuery':		$tpl='DELETE FROM `'.$this->tableName.'` WHERE '.$this->getPKCondition();break;
			case 'saveQuery':		$tpl='UPDATE `'     .$this->tableName.'` SET '  .$this->getFieldsNameValueList().' WHERE '.$this->getPKCondition();break;
			case 'loadQuery':		$tpl='SELECT '.$this->getFieldsNamesList().' FROM `'.$this->tableName.'` WHERE '.($this->where===null?$this->getPKCondition():$this->getWhereCondition());break;
			default: throw new \Exception('unknown query __called method name.');
		}
		return MySQL::getQueryTemplateArray($tpl,$arguments);
	}
	public		function	__get($propertyName)
	{
		if (isset($this->collections[$propertyName]))
		{
			if ($this->collections[$propertyName][1]===null)
				$this->collections[$propertyName][1]=new $this->collections[$propertyName][0]($this->db,$this->id);
			return $this->collections[$propertyName][1];
		}
		if (isset($this->objects[$propertyName]))
		{
			// TODO: ссылки не только на PK
			if ($this->objects[$propertyName][1]===null)
				$this->objects[$propertyName][1]
					= new $this->objects[$propertyName][0](
							$this->db,
							$this->{$this->objects[$propertyName][2]}
					);
			return $this->objects[$propertyName][1];
		}
		throw new \Exception('свойство $'.$propertyName.' в классе '.get_class($this).' не определено или не является public.');
	}
	public		function	__set($propertyName,$propertyValue)
	{
		// this bugfix only temporary. php bug.
		// in_array($propertyName,$this->PKFieldName) not work.
		$a=$this->PKFieldName;
		if (in_array($propertyName,$a))
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
		$this->{$this->PKFieldName[0]}=$this->db->lastInsertId();
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
				$this->{$this->PKFieldName[0]}=$id_or_where;
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
	public		function	load($id_or_where=null)
	{
		if ($id_or_where!==null)
			if (is_array($id_or_where))
				$this->where=$id_or_where;
			else
			{
				$this->where=null;
				$this->{$this->PKFieldName[0]}=$id_or_where;
			}
		else
			;
		if (!$this->doQuery($this->loadQuery()))return false;// sql error
        if ($this->db->getNumRows()==0)			return null; // no  record
		if (!$result=$this->db->fetchArray())	return false;
		$this->fillProperties($result);
		return	true;
	}
	/**
	 *
	 * @param IDb $db
	 * @param mixed $id PK value - int, string or array if multifield PK
	 * @return \static
	 * @throws \Exception 
	 */
static
	public		function	getById(IDb &$db,$id)
	{
		$object=new static($db);
		if (!$object->load($id))
			throw new \Exception('no record');
		return $object;
	}

	protected	function	loadByQuery($sqlQuery)
	{
		if (!$this->doQuery($sqlQuery))			return false;// sql error
		if ($this->db->getNumRows()==0)			return null; // no  record
		if (!$result=$this->db->fetchArray())	return false;
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
	public		function	toArray()
	{
		$arrRet=array();
		foreach ($this as $propName => $propValue)
			if (in_array($propName,$this->fields))
				$arrRet[$propName]=$propValue;
		return $arrRet;
	}
	/**
	 * 
	 * @param string $strQuery
	 * @param string $type sql|internal
	 * @param int $errno
	 * @return type 
	 */
	protected	function	setError($strQuery,$type='sql',$errno=-128)
	{
		$cls=get_class($this);
		switch ($type)
		{
			case 'sql':
				$errno=$this->db->getLastErrno();
				$this->error_message=
					$cls."\n".
					'SQL-error ['.$errno.']: '.$this->db->getLastError()."\n".
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
		if (!$this->db->query($strQuery))
			return !$this->setSqlError($strQuery);
		return true;
	}
	protected	function	doQueries(array $arrQueries)
	{
		if (!$this->db->queries($arrQueries))
			return !$this->setSqlError(print_r($arrQueries,true));
		return true;
	}
	protected	function	doTransaction($arrQueries)
	{
		if (!$this->db->commit($arrQueries))
			return !$this->setSqlError(print_r($arrQueries,true));
		return true;
	}
}
