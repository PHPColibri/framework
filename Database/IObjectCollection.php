<?php
namespace Colibri\Database;

use Colibri\Base\IDynamicCollection;

interface	IObjectCollection extends IDynamicCollection
{
	function	addItem(Object &$obj);
	function	delItem($itemID);
	function	clearItems();

	function	selFromDbAll();

	function	load($parentID=null);
	function	reload();
	function	add(Object $obj);
	function	remove($itemID);
	function	clear();
	function	indexOf($itemID);
	function	contains($itemID);
}
