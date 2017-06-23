<?php
namespace Colibri\Database;

interface    IObjectMultiCollection extends IObjectCollection
{
    function addToDb(Object &$itemID);

    function delFromDb($itemID);

    //function	selFromDbAll();
    function delFromDbAll();
}
