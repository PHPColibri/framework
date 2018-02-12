<?php
namespace Colibri\Database;

use Colibri\Base\DynamicCollection;
use Colibri\Base\DynamicCollectionInterface;
use Colibri\Database;

/**
 * Абстрактный класс ModelCollection.
 *
 * Класс основан на DynamicCollection, по сему свои элементы подгружает
 * только тогда, когда идёт первое обращение к элементу коллекции.
 *
 * @property mixed $parentID
 */
abstract class ModelCollection extends DynamicCollection implements DynamicCollectionInterface
{
    /** @var string */
    protected static $tableName = 'tableName_not_set';
    /** @var string */
    protected $itemClass = 'itemClass_not_set';
    /** @var array */
    protected $FKName = ['_id', '_id'];
    /** @var array */
    protected $FKValue = [null, null];
    /** @var mixed */
    protected $_parentID;
    /** @var array */
    protected $itemFields = [];
    /** @var array */
    protected $itemFieldTypes = [];

    /** @var Query */
    private $query = null;

    /** @var bool */
    private $pagedQuery = false;
    /** @var int */
    public $recordsPerPage = 20;
    /** @var int */
    public $recordsCount = null;
    /** @var int */
    public $pagesCount = null;

    /**
     * @param mixed $parentID
     *
     * @throws \Colibri\Database\DbException
     */
    public function __construct($parentID = null)
    {
        $this->parentID = $parentID;
        $this->getFieldsAndTypes();
    }

    /**
     * @return Query
     */
    protected function getQuery(): Query
    {
        return $this->query ?? $this->query = $this->query();
    }

    /**
     * @return \Colibri\Database\Query
     */
    abstract protected function query(): Query;

    /**
     * DynamicCollectionInterface ::fillItems() implementation.
     *
     * @param array $rows
     *
     * @return bool
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function fillItems(array &$rows = null)
    {
        if ($rows === null) {
            return $this->load();
        }

        $this->clearItems();
        foreach ($rows as $row) {
            $item = $this->instantiateItem($row);
            $this->addItem($item);
        }

        return true;
    }

    /**
     * @return Query
     *
     * @throws \Colibri\Database\DbException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    abstract protected function selFromDbAllQuery(): Query;

    /**
     * @param string $propertyName
     *
     * @return mixed
     *
     * @throws \UnexpectedValueException
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case 'parentID':
                return $this->FKValue[0];
            default:
                throw new \UnexpectedValueException('property ' . $propertyName . ' not defined in class ' . get_class($this));
        }
    }

    /**
     * @param string $propertyName
     * @param mixed  $propertyValue
     *
     * @return mixed
     *
     * @throws \UnexpectedValueException
     */
    public function __set($propertyName, $propertyValue)
    {
        switch ($propertyName) {
            case 'parentID':
                return $this->FKValue[0] = $propertyValue;
            default:
                throw new \UnexpectedValueException('property ' . $propertyName . ' not defined in class ' . get_class($this));
        }
    }

    // with Items
    ///////////////////////////////////////////////////////////////////////////

    /**
     * @param int $position
     *
     * @throws \OutOfBoundsException
     */
    final protected function shiftLeftFromPos($position)
    {
        $cnt = parent::count();
        if ($position < 1 || $position >= $cnt) {
            throw new \OutOfBoundsException('position to shift from must be in range 1..Length-1');
        }
        for ($i = $position; $i < $cnt; $i++) {
            $this->items[$i - 1] = $this->items[$i];
        }
    }

    /**
     * @param \Colibri\Database\Model $object
     */
    protected function addItem(Model &$object)
    {
        $this->items[] = $object;
    }

    /**
     * @param int $itemID
     *
     * @return bool|\Colibri\Database\Model
     *
     * @throws \OutOfBoundsException
     */
    protected function delItem($itemID)
    {
        $pos = $this->indexOf($itemID);
        if ($pos == -1) {
            return false;
        }
        $item = $this->items[$pos];
        if ($pos != count($this->items) - 1) {
            $this->shiftLeftFromPos($pos + 1);
        }
        array_pop($this->items);

        return $item;
    }

    /**
     * @return void
     */
    protected function clearItems()
    {
        $this->items = [];
    }

    /**
     * @param array $row
     *
     * @return \Colibri\Database\Model
     */
    protected function instantiateItem(array $row)
    {
        return new $this->itemClass($row);
    }

    ///////////////////////////////////////////////////////////////////////////

    // with DataBase
    ///////////////////////////////////////////////////////////////////////////

    /**
     * @param \Colibri\Database\Model $id
     */
    abstract protected function addToDb(Database\Model &$id);

    /**
     * @param mixed $id
     */
    abstract protected function delFromDb($id);

    /**
     * @return array
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function selFromDbAll()
    {
        $selectedRows = $this->doQuery($this->selFromDbAllQuery())->fetchAll();

        $this->query = null;

        // TODO [alek13]: bring it out
        if ($this->pagedQuery) {
            $row                = static::db()->getConnection()->query('SELECT FOUND_ROWS()')->fetch();
            $this->recordsCount = reset($row);
            $this->pagesCount   = ceil($this->recordsCount / $this->recordsPerPage);
        }

        return $selectedRows;
    }

    /**
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     */
    abstract protected function delFromDbAll();

    ///////////////////////////////////////////////////////////////////////////

    /**
     * @param Query $query
     *
     * @return bool|\Colibri\Database\AbstractDb\Driver\Query\ResultInterface
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \UnexpectedValueException
     */
    protected function doQuery(Query $query)
    {
        return static::db()->query($query);
    }

    /**
     * @throws \Colibri\Database\DbException
     */
    protected function getFieldsAndTypes()
    {
        if (empty($this->itemFields)) {
            $metadata             = $this->db()->metadata()->getColumnsMetadata(static::$tableName);
            $this->itemFields     = &$metadata['fields'];
            $this->itemFieldTypes = &$metadata['fieldTypes'];
        }
    }

    /**
     * @return int
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \UnexpectedValueException
     */
    public function count()
    {
        return $this->items !== null
            ? parent::count()
            : (int)current(
                static::db()->query(
                    (clone $this->selFromDbAllQuery())->count()
                )->fetch()
            );
    }

    ///////////////////////////////////////////////////////////////////////////

    /**
     * @param array  $where array('field [op]' => value, ...)
     * @param string $type  one of 'and'|'or'
     *
     * @return $this|\Colibri\Database\ModelCollection|Model[]
     *
     * @throws \InvalidArgumentException
     */
    final public function where(array $where, $type = 'and')
    {
        $this->getQuery()->where($where, $type);

        return $this;
    }

    /**
     * @param array $plan
     *
     * @return ModelCollection|$this|Model[]
     */
    final public function wherePlan(array $plan)
    {
        $this->getQuery()->wherePlan($plan);

        return $this;
    }

    /**
     * @param array $orderBy array('field1'=>'orientation','field2'=>'orientation'), 'fieldN' - name of field,
     *                       'orientation' - ascending or descending abbreviation ('asc' or 'desc')
     *
     * @return ModelCollection|$this|Model[]
     */
    final public function orderBy(array $orderBy)
    {
        $this->getQuery()->orderBy($orderBy);

        return $this;
    }

    /**
     * @param int $offsetOrCount
     * @param int $count
     *
     * @return ModelCollection|$this|Model[]
     */
    final public function limit($offsetOrCount, $count = null)
    {
        $this->getQuery()->limit($offsetOrCount, $count);
        $this->pagedQuery = true;

        return $this;
    }

    /**
     * @param int $pageNumber     0..N
     * @param int $recordsPerPage
     *
     * @return ModelCollection|$this|Model[]
     */
    final public function page($pageNumber, $recordsPerPage = null)
    {
        $recordsPerPage = $recordsPerPage ?? $this->recordsPerPage;

        $this->getQuery()->limit(((int)$pageNumber) * $recordsPerPage, $recordsPerPage);
        $this->pagedQuery = true;

        return $this;
    }

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
    public function add(Database\Model $object)
    {
        if ($this->items === null) {
            if ( ! $this->fillItems()) {
                return false;
            }
        }
        $this->addToDb($object);
        $this->addItem($object);

        return true;
    }

    /**
     * @param mixed $itemID
     *
     * @return bool|\Colibri\Database\Model
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     * @throws \UnexpectedValueException
     */
    public function remove($itemID)
    {
        if ($this->items === null) {
            if ( ! $this->fillItems()) {
                return false;
            }
        }

        $this->delFromDb($itemID);

        return $this->delItem($itemID);
    }

    /**
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function clear()
    {
        $this->delFromDbAll();
        $this->clearItems();
    }

    /**
     * @param mixed $parentID
     *
     * @return bool
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function load($parentID = null)
    {
        if ($parentID !== null) {
            $this->parentID = $parentID;
        }
        if ( ! is_array($rows = $this->selFromDbAll())) {
            return false;
        }

        if ( ! $this->fillItems($rows)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function reload()
    {
        return $this->load();
    }

    /**
     * @param int $itemID
     *
     * @return int
     */
    public function indexOf($itemID)
    {
        $cnt = parent::count();
        for ($i = 0; $i < $cnt; $i++) {
            if ($this->items[$i]->id == $itemID) {
                return $i;
            }
        }

        return -1;
    }

    /**
     * @param int $itemID
     *
     * @return bool
     */
    public function contains($itemID)
    {
        if ($this->indexOf($itemID) == -1) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param int|string $id
     *
     * @return bool
     */
    public function &getItemByID($id)
    {
        if ( ! $count = parent::count()) {
            return false;
        }
        /** @var \Colibri\Database\Model $itemClass */
        $itemClass = $this->itemClass;
        /** @noinspection PhpUndefinedVariableInspection */
        $PKfn = $itemClass::$PKFieldName[0];
        for ($i = 0; $i < $count; $i++) {
            if (isset($this->items[$i]->$PKfn) && $this->items[$i]->$PKfn == $id) {
                return $this->items[$i];
            }
        }

        return false;
    }

    /**
     * @param string $fieldName which field push to an array
     * @param string $keyField  which field use as keys of array
     *
     * @return array
     */
    public function &toArrayOf($fieldName, $keyField = null)
    {
        $arr = [];
        foreach ($this as $object) {
            if ($keyField === null) {
                $arr[] = $object->$fieldName;
            } else {
                $arr[$object->$keyField] = $object->$fieldName;
            }
        }

        return $arr;
    }

    /**
     * @param string $fieldName
     * @param string $glue
     *
     * @return string
     */
    public function implode($fieldName, $glue = ', ')
    {
        return implode($glue, $this->toArrayOf($fieldName));
    }

    /**
     * @return \Colibri\Database\AbstractDb\DriverInterface
     *
     * @throws \Colibri\Database\DbException
     */
    protected function db()
    {
        /** @var Model $itemClass */
        $itemClass = $this->itemClass;

        return $itemClass::db();
    }

    ///////////////////////////////////////////////////////////////////////////

    /**
     * @param string $fieldName
     * @param string $keyField
     *
     * @return static|ModelCollection|Model[]|array
     *
     * @throws \Colibri\Database\DbException
     */
    public static function &all($fieldName = null, $keyField = null)
    {
        $collection = new static();
        if ($fieldName !== null) {
            return $collection->toArrayOf($fieldName, $keyField);
        }

        return $collection;
    }

    /**
     * @return $this
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function get()
    {
        if ( ! $this->load()) {
            throw new DbException('failed to load collection');
        }

        return $this;
    }

    /**
     * @param callable $handler
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \UnexpectedValueException
     */
    public function walk(callable $handler)
    {
        $cursor = static::db()->query($this->selFromDbAllQuery())->cursor();
        foreach ($cursor as $row) {
            if ($handler($this->instantiateItem($row)) === false) {
                break;
            }
        }
    }
}
