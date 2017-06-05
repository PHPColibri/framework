<?php
namespace Colibri\Database;

use Colibri\Util\Arr;

/**
 * ObjectMultiCollection
 *
 */
class ObjectMultiCollection extends ObjectCollection //implements IObjectMultiCollection
{
	protected	$fkTableName='fkTableName_not_set';
	private 	$fkTableFields=[];
	private 	$intermediateFields=[];

	public		function	__construct($parentID = null)
	{
		parent::__construct($parentID);

		$metadata                 = static::db()->getColumnsMetadata($this->fkTableName);
		$this->fkTableFields      = &$metadata['fields'];
		$this->intermediateFields = array_diff($this->fkTableFields, $this->FKName);
	}

	public		function	__get($propertyName)
	{
		switch	($propertyName)
		{
			case 'parentID':			return $this->FKValue[0];
			case 'addToDbQuery':		return 'INSERT INTO `'.$this->fkTableName.'` SET '  .$this->FKName[0].'='.$this->FKValue[0].', '   .$this->FKName[1].'='.$this->FKValue[1];
			case 'delFromDbQuery':		return 'DELETE FROM `'.$this->fkTableName.'` WHERE '.$this->FKName[0].'='.$this->FKValue[0].' AND '.$this->FKName[1].'='.$this->FKValue[1];
			case 'selFromDbAllQuery':
				$intermediateFields = '';
				if (count($this->intermediateFields)) {
					$intermediateFields = ', f.' . implode(', f.', $this->intermediateFields);
				}

				$strQuery = $this->FKValue[0] !== null
					? "SELECT o.* $intermediateFields FROM `" . static::$tableName . '` o inner join `' . $this->fkTableName . '` f  on o.id=f.' . $this->FKName[1] . ' WHERE f.' . $this->FKName[0] . '=' . $this->FKValue[0]
					: "SELECT o.* $intermediateFields FROM `" . static::$tableName . '` o WHERE 1';
				$strQuery = $this->rebuildQueryForCustomLoad($strQuery);
				if ($strQuery === false)
					throw new \RuntimeException('can\'t rebuild query \'' . $propertyName . '\' for custom load in ' . __METHOD__ . ' [line: ' . __LINE__ . ']. possible: getFieldsAndTypes() failed (check for sql errors) or incorrect wherePlan() format');

				return $strQuery;
			case 'delFromDbAllQuery':	return 'DELETE FROM `'.$this->fkTableName.'` WHERE '.$this->FKName[0].'='.$this->FKValue[0];
			default:					return parent::__get($propertyName);
		}
	}

	// with Items
	///////////////////////////////////////////////////////////////////////////
	/**
	 * @param array $row
	 *
	 * @return \Colibri\Database\Object
	 */
	protected	function	instantiateItem(array $row)
	{
		if (!count($this->intermediateFields)) {
			return parent::instantiateItem($row);
		}

		$itemAttributes = Arr::only($row, $this->itemFields);
		$intermediateAttributes = Arr::only($row, $this->intermediateFields);
		/** @var \Colibri\Database\Object $item */
		$item = new $this->itemClass($itemAttributes);

		return $item->setIntermediate($intermediateAttributes);
	}
	// with DataBase
	///////////////////////////////////////////////////////////////////////////
	protected	function	addToDb(Object &$object)
	{
		$this->FKValue[1]=$object->id;
		return $this->doQuery($this->addToDbQuery);
	}
	protected	function	delFromDb($id)
	{
		$this->FKValue[1]=$id;
		return $this->doQuery($this->delFromDbQuery);
	}
	protected	function	selFromDbAll()
	{
		if (!($this->doQuery($this->selFromDbAllQuery)))
			return false;
		return $this->db()->fetchAllRows();
	}
	protected	function	delFromDbAll()
	{
		return $this->doQuery($this->delFromDbAllQuery);
	}
	///////////////////////////////////////////////////////////////////////////
}
