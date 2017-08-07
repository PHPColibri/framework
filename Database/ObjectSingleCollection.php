<?php
namespace Colibri\Database;

use Colibri\Database;

/**
 * ObjectSingleCollection.
 */
class ObjectSingleCollection extends ObjectCollection
{
    /**
     * @param string $propertyName
     *
     * @return bool|mixed|string
     *
     * @throws \RuntimeException
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case 'parentID':
                return $this->FKValue[0];
            case 'selFromDbAllQuery':
                $strQuery =
                    'SELECT * FROM `' . static::$tableName . '` WHERE 1 ' .
                    ($this->FKValue[1] !== null ?
                        ' AND ' . $this->FKName[1] . '=' . $this->FKValue[1] : '') .
                    ($this->FKValue[0] !== null
                        ?
                        ' AND ' . $this->FKName[0] .
                        ($this->FKValue[0] === 'NULL' ? ' IS ' : '=') .
                        $this->FKValue[0]
                        :
                        '');
                $strQuery = $this->rebuildQueryForCustomLoad($strQuery);
                if ($strQuery === false) {
                    throw new \RuntimeException('can\'t rebuild query \'' . $propertyName . '\' for custom load in ' . __METHOD__ . ' [line: ' . __LINE__ . ']. possible: getFieldsAndTypes() failed (check for sql errors) or incorrect wherePlan() format');
                }

                return $strQuery;
            case 'delFromDbAllQuery':
                return 'DELETE FROM `' . static::$tableName . '` WHERE ' . $this->FKName[0] . '=' . $this->FKValue[0];
            default:
                return parent::__get($propertyName);
        }
    }

    // with DataBase
    ///////////////////////////////////////////////////////////////////////////

    /**
     * @param \Colibri\Database\Model $id
     *
     * @return bool
     */
    protected function addToDb(Database\Model &$id)
    {
        return true;
    }

    /**
     * @param mixed $id
     *
     * @return bool
     */
    protected function delFromDb($id)
    {
        return true;
    }

    /**
     * @return bool
     */
    protected function delFromDbAll()
    {
        return true;
    }

    ///////////////////////////////////////////////////////////////////////////
}
