<?php
namespace Colibri\Database;

use Colibri\Base\Error;
use Colibri\Database\ObjectCollection;

/**
 * ObjectSingleCollection
 *
 */
class ObjectSingleCollection extends ObjectCollection //implements IObjectSingleCollection
{

	public		function	__get($propertyName)
	{
		switch	($propertyName)
		{
			case 'parentID':			return $this->FKValue[0];
			case 'selFromDbAllQuery':	//$code='$fList='.$this->itemClass.'::getFieldsNameList();';
										//eval($code);
										//'.$fList.'
										$strQuery=
											'SELECT * FROM `'.static::$tableName.'` WHERE 1 '.
											($this->FKValue[1]!==null?
												' AND '.$this->FKName[1].'='.$this->FKValue[1]:'').
												($this->FKValue[0]!==null?
													' AND '.$this->FKName[0].
													($this->FKValue[0]==='NULL'?' IS ':'=').
													$this->FKValue[0]
													:
													'');
										$strQuery=$this->rebuildQueryForCustomLoad($strQuery);
										if ($strQuery===false)
											Error::__raiseError(401,$this->error_number,$this->error_message,$propertyName,__METHOD__,__LINE__);
										return $strQuery;
			case 'delFromDbAllQuery':	return 'DELETE FROM `'.static::$tableName.'` WHERE '.$this->FKName[0].'='.$this->FKValue[0];
			default:					return parent::__get($propertyName);
		}
	}

	// with DataBase
	///////////////////////////////////////////////////////////////////////////
	protected   function    addToDb(Object &$id)    {	return true;	}
	protected   function    delFromDb($id)          {	return true;	}
	protected   function    delFromDbAll()          {	return true;	}
	///////////////////////////////////////////////////////////////////////////
}
