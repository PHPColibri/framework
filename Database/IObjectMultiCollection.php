<?php
namespace Colibri\Database;

use Colibri\Database\IObjectCollection;

interface	IObjectMultiCollection extends IObjectCollection
{
	function	addToDb($itemID);
	function	delFromDb($itemID);
	//function	selFromDbAll();
	function	delFromDbAll();
}
