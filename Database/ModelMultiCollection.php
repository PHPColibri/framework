<?php
namespace Colibri\Database;

use Colibri\Database;
use Colibri\Util\Arr;

/**
 * ObjectMultiCollection.
 */
class ModelMultiCollection extends ModelCollection
{
    /** @var string */
    protected $fkTableName = 'fkTableName_not_set';
    /** @var array */
    private $fkTableFields = [];
    /** @var array */
    private $intermediateFields = [];

    /**
     * ObjectMultiCollection constructor.
     *
     * @param mixed $parentID
     *
     * @throws \Colibri\Database\DbException
     */
    public function __construct($parentID = null)
    {
        parent::__construct($parentID);

        $metadata                 = static::db()->getColumnsMetadata($this->fkTableName);
        $this->fkTableFields      = &$metadata['fields'];
        $this->intermediateFields = array_diff($this->fkTableFields, $this->FKName);
    }

    /**
     * @return string
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function addToDbQuery(): string
    {
        return Query::insert()->into($this->fkTableName)->set([
            $this->FKName[0] => $this->FKValue[0],
            $this->FKName[1] => $this->FKValue[1],
        ])->build(static::db());
    }

    /**
     * @return string
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function delFromDbQuery(): string
    {
        return Query::delete()->from($this->fkTableName)->where([
            $this->FKName[0] => $this->FKValue[0],
            $this->FKName[1] => $this->FKValue[1],
        ])->build(static::db());
    }

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
        $strQuery = $this->FKValue[0] !== null
            ? Query::select(['*'], $this->intermediateFields)
                ->from(static::$tableName)
                ->join($this->fkTableName, $this->FKName[1], 'id', Query\JoinType::INNER)
                ->where([
                    'j1.' . $this->FKName[0] => $this->FKValue[0],
                ])
                ->build(static::db())
            : Query::select(['*'], $this->intermediateFields)
                ->from(static::$tableName)
                ->build(static::db())
        ;

        $strQuery = $this->rebuildQueryForCustomLoad($strQuery);
        if ($strQuery === false) {
            throw new \RuntimeException('can\'t rebuild query \'selFromDbAllQuery\' for custom load in ' . __METHOD__ . ' [line: ' . __LINE__ . ']. possible: getFieldsAndTypes() failed (check for sql errors) or incorrect wherePlan() format');
        }

        return $strQuery;
    }

    /**
     * @return string
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function delFromDbAllQuery(): string
    {
        return Query::delete()
            ->from($this->fkTableName)
            ->where([$this->FKName[0] => $this->FKValue[0]])
            ->build(static::db());
    }

    // with Items
    ///////////////////////////////////////////////////////////////////////////

    /**
     * @param array $row
     *
     * @return \Colibri\Database\Model
     */
    protected function instantiateItem(array $row)
    {
        if ( ! count($this->intermediateFields)) {
            return parent::instantiateItem($row);
        }

        $itemAttributes         = Arr::only($row, $this->itemFields);
        $intermediateAttributes = Arr::only($row, $this->intermediateFields);
        /** @var \Colibri\Database\Model $item */
        $item = new $this->itemClass($itemAttributes);

        return $item->setIntermediate($intermediateAttributes);
    }

    // with DataBase
    ///////////////////////////////////////////////////////////////////////////

    /**
     * @param \Colibri\Database\Model $object
     *
     * @return bool
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function addToDb(Database\Model &$object)
    {
        $this->FKValue[1] = $object->id;

        return $this->doQuery($this->addToDbQuery());
    }

    /**
     * @param int $id
     *
     * @return bool
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function delFromDb($id)
    {
        $this->FKValue[1] = $id;

        return $this->doQuery($this->delFromDbQuery());
    }

    /**
     * @return bool
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function delFromDbAll()
    {
        return $this->doQuery($this->delFromDbAllQuery());
    }
}
