<?php
namespace Colibri\Database;

use Colibri\Database;

/**
 * ObjectSingleCollection.
 */
class ModelSingleCollection extends ModelCollection
{
    /**
     * @param string $propertyName
     *
     * @return bool|mixed|string
     *
     * @throws \Colibri\Database\DbException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case 'parentID':
                return $this->FKValue[0];
            case 'selFromDbAllQuery':
                $query = Query::select()->from(static::$tableName);
                if ($this->FKValue[1] !== null) {
                    $query->where([$this->FKName[1] => $this->FKValue[1]]);
                }
                if ($this->FKValue[0] !== null) {
                    $query->where([$this->FKName[0] => $this->FKValue[0]]);
                }

                $strQuery = $query->build(static::db());

                $strQuery = $this->rebuildQueryForCustomLoad($strQuery);
                if ($strQuery === false) {
                    throw new \RuntimeException('can\'t rebuild query \'' . $propertyName . '\' for custom load in ' . __METHOD__ . ' [line: ' . __LINE__ . ']. possible: getFieldsAndTypes() failed (check for sql errors) or incorrect wherePlan() format');
                }

                return $strQuery;
            case 'delFromDbAllQuery':
                return Query::delete()
                    ->from(static::$tableName)
                    ->where([$this->FKName[0] => $this->FKValue[0]])
                    ->build(static::db());
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
