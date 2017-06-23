<?php
namespace Colibri\Database;

use Colibri\Database;

interface    IObjectMultiCollection extends IObjectCollection
{
    function addToDb(Database\Object &$itemID);

    function delFromDb($itemID);

    //function	selFromDbAll();
    function delFromDbAll();
}
