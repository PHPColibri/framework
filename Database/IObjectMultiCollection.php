<?php
namespace Colibri\Database;

use Colibri\Database\IObjectCollection;

interface	IObjectMultiCollection extends IObjectCollection
{
	function	addToDb(Object &$itemID);
	function	delFromDb($itemID);
	//function	selFromDbAll();
	function	delFromDbAll();
}
