<?php
namespace Colibri\Database;

use Colibri\Database;

/**
 * ObjectSingleCollection.
 */
class ModelSingleCollection extends ModelCollection
{
    /**
     * @return string
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     */
    protected function selFromDbAllQuery(): string
    {
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
            throw new \RuntimeException('can\'t rebuild query \'selFromDbAllQuery\' for custom load. possible: getFieldsAndTypes() failed (check for sql errors) or incorrect wherePlan() format');
        }

        return $strQuery;
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
