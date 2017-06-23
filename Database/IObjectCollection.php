<?php
namespace Colibri\Database;

use Colibri\Base\IDynamicCollection;
use Colibri\Database;

interface    IObjectCollection extends IDynamicCollection
{
    function addItem(Database\Object &$obj);

    function delItem($itemID);

    function clearItems();

    function selFromDbAll();

    function load($parentID = null);

    function reload();

    function add(Database\Object $obj);

    function remove($itemID);

    function clear();

    function indexOf($itemID);

    function contains($itemID);
}
