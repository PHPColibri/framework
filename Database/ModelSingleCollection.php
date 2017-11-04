<?php
namespace Colibri\Database;

use Colibri\Database;

/**
 * ObjectSingleCollection.
 */
class ModelSingleCollection extends ModelCollection
{
    /**
     * @return \Colibri\Database\Query
     */
    protected function query(): Query
    {
        return Query::select();
    }

    /**
     * @return string
     *
     * @throws \Colibri\Database\DbException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function selFromDbAllQuery(): string
    {
        $query = $this->getQuery()->from(static::$tableName);
        if ($this->FKValue[1] !== null) {
            $query->where([$this->FKName[1] => $this->FKValue[1]]);
        }
        if ($this->FKValue[0] !== null) {
            $query->where([$this->FKName[0] => $this->FKValue[0]]);
        }

        return $query->build(static::db());
    }

    // with DataBase
    ///////////////////////////////////////////////////////////////////////////

    /**
     * @param \Colibri\Database\Model $id
     */
    protected function addToDb(Database\Model &$id)
    {
    }

    /**
     * @param mixed $id
     */
    protected function delFromDb($id)
    {
    }

    protected function delFromDbAll()
    {
    }

    ///////////////////////////////////////////////////////////////////////////
}
