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
 * @property string                          $selFromDbAllQuery
 * @property mixed                           $parentID
 * @property \Colibri\Database\Model[]|array $_items
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

    /** @var array */
    protected $where = null;
    /** @var array */
    protected $order_by = null;
    /** @var array */
    protected $limit = null;

    /** @var int */
    public $recordsPerPage = 20;
    /** @var int */
    public $recordsCount = null;
    /** @var int */
    public $pagesCount = null;

    /**
     * @param mixed $parentID
     */
    public function __construct($parentID = null)
    {
        $this->parentID = $parentID;
    }

    /**
     * DynamicCollectionInterface ::fillItems() implementation.
     *
     * @param array $rows
     *
     * @return bool
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
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
     * @param string $propertyName
     * @param mixed  $propertyValue
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public function __set($propertyName, $propertyValue)
    {
        switch ($propertyName) {
            case 'parentID':
                return $this->FKValue[0] = $propertyValue;
            default:
                return parent::__set($propertyName, $propertyValue);
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
        $cnt = count($this->_items);
        if ($position < 1 || $position >= $cnt) {
            throw new \OutOfBoundsException('position to shift from must be in range 1..Length-1');
        }
        for ($i = $position; $i < $cnt; $i++) {
            $this->_items[$i - 1] = $this->_items[$i];
        }
    }

    /**
     * @param \Colibri\Database\Model $object
     */
    protected function addItem(Database\Model &$object)
    {
        $this->_items[] = $object;
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
        $item = $this->_items[$pos];
        if ($pos != count($this->_items) - 1) {
            $this->shiftLeftFromPos($pos + 1);
        }
        array_pop($this->_items);

        return $item;
    }

    /**
     * @return void
     */
    protected function clearItems()
    {
        $this->_items = [];
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
     *
     * @return mixed
     */
    abstract protected function delFromDb($id);

    //abstract	protected	function	selFromDbAll();

    /**
     * @return mixed
     */
    abstract protected function delFromDbAll();

    /**
     * @return array
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     */
    protected function selFromDbAll()
    {
        $this->doQuery($this->selFromDbAllQuery);

        return $this->db()->fetchAllRows();
    }

    ///////////////////////////////////////////////////////////////////////////

    /**
     * @param string $query
     *
     * @return bool
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     */
    protected function doQuery($query)
    {
        $this->db()->query($query);

        return true;
    }

    /**
     * @return array
     *
     * @throws \Colibri\Database\DbException
     */
    protected function getFieldsAndTypes()
    {
        if (empty($this->itemFields)) {
            $metadata             = $this->db()->getColumnsMetadata(static::$tableName);
            $this->itemFields     = &$metadata['fields'];
            $this->itemFieldTypes = &$metadata['fieldTypes'];
        }

        return ['fields' => &$this->itemFields, 'types' => &$this->itemFieldTypes];
    }

    /**
     * @param array  $clauses
     * @param string $type    one of 'and'|'or'
     *
     * @return string
     *
     * @throws \Colibri\Database\DbException
     */
    protected function buildWhere(array &$clauses, $type)
    {
        $whereParts = [];
        foreach ($clauses as $clause) {
            $name  = $clause[0];
            $value = $clause[1];
            if (is_array($value) && ($name == 'and' || $name == 'or')) {
                $whereParts[] = $this->buildWhere($value, $name);
            } else {
                if ( ! is_array($name = explode(' ', $name, 2))) {
                    $name = [$name];
                }
                if ( ! isset($name[1])) {
                    $name[1] = $value === null ? 'is' : '=';
                }

                $whereParts[] = '`' . $name[0] . '` ' . $name[1] . ' ' . $this->db()->prepareValue($value, $this->itemFieldTypes[$name[0]]);
            }
        }

        return '(' . implode(' ' . $type . ' ', $whereParts) . ')';
    }

    /**
     * @param string $query
     *
     * @return bool|mixed|string
     *
     * @throws \Colibri\Database\DbException
     */
    protected function rebuildQueryForCustomLoad($query)
    {
        if ($this->getFieldsAndTypes() === false) {
            return false;
        }

        if ($this->where !== null) {
            $where = $this->where;
            if (count($where) !== 1) {
                return false;
            }
            if (isset($where['and'])) {
                $type    = 'and';
                $clauses = $where['and'];
            } else {
                if (isset($where['or'])) {
                    $type    = 'or';
                    $clauses = $where['or'];
                } else {
                    return false;
                }
            }

            $query .= ' AND ' . $this->buildWhere($clauses, $type);

            $this->where = null;
        }

        if ($this->order_by !== null) {
            $query .= ' ORDER BY ';
            $strOrder = '';
            foreach ($this->order_by as $name => $value) {
                $strOrder .= ', `' . $name . '` ' . $value;
            }
            $query .= substr($strOrder, 2);
        }

        if ($this->limit !== null) {
            $query = str_ireplace('SELECT ', 'SELECT SQL_CALC_FOUND_ROWS ', $query);
            $query .= ' LIMIT ' . implode(',', $this->limit);
            //$this->limit=null; // its sets to <null> in load()
        }

        return $query;
    }

    ///////////////////////////////////////////////////////////////////////////

    ///////////////////////////////////////////////////////////////////////////
    // for where() function additional functions.
    /*private	function	whereClauses(array $where,$type='and')
    {
        $whereClauses=$this->buildClauses($where,$type);
        if (is_array($this->where))
            $this->where=array_merge($this->where,$whereClauses);
        else
            $this->where=$whereClauses;

        return $this;
    }*/

    /**
     * @param array  $where
     * @param string $type  one of 'and'|'or'
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    private function buildClauses(array $where, $type = 'and')
    {
        if ( ! in_array($type, ['and', 'or'])) {
            throw new \InvalidArgumentException('where-type must be `and` or `or`');
        }
        $whereClauses = [];
        foreach ($where as $name => $value) {
            $whereClauses[] = [$name, $value];
        }

        return [$type => $whereClauses];
    }

    // public user functions
    ///////////////////////////////////////////////////////////////////////////
    /// additional function for custom load() /////////////////////////////////

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
        $where = $this->buildClauses($where, $type);
        if ($this->where === null) {
            $this->where = $where;

            return $this;
        }

        if (isset($this->where[$type])) {
            $this->where[$type] = array_merge($this->where[$type], $where[$type]);
        } else {
            $this->where = $type == 'or'
                ? ['and' => array_merge($this->where['and'], [['or', $where['or']]])]
                : ['and' => array_merge($where['and'], [['or', $this->where['or']]])];
        }

        return $this; //->whereClauses($where);
    }

    /*
     *
     * @param array $where
     * @return ModelCollection|$this|Model[]
     *//*
    final public function or_where(array $where)
    {
        return $this->whereClauses($where,'OR');
    }*/

    /**
     * @param array $plan
     *
     * @return ModelCollection|$this|Model[]
     */
    final public function wherePlan(array $plan)
    {
        $this->where = $plan;

        return $this;
    }

    /**
     * @param array $order_by array('field1'=>'orientation','field2'=>'orientation'), 'fieldN' - name of field,
     *                        'orientation' - ascending or descending abbreviation ('asc' or 'desc')
     *
     * @return ModelCollection|$this|Model[]
     */
    final public function orderBy(array $order_by)
    {
        $this->order_by = $order_by;

        return $this;
    }

    /**
     * @param int $offset_or_count
     * @param int $count
     *
     * @return ModelCollection|$this|Model[]
     */
    final public function limit($offset_or_count, $count = null)
    {
        if ($count === null) {
            $this->limit['offset'] = 0;
            $this->limit['count']  = (int)$offset_or_count;
        } else {
            $this->limit['offset'] = (int)$offset_or_count;
            $this->limit['count']  = (int)$count;
        }

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
        if ($recordsPerPage !== null) {
            $this->recordsPerPage = (int)$recordsPerPage;
        }
        $this->limit['offset'] = ((int)$pageNumber) * $this->recordsPerPage;
        $this->limit['count']  = $this->recordsPerPage;

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
     */
    public function add(Database\Model &$object)
    {
        if ($this->_items === null) {
            if ( ! $this->fillItems()) {
                return false;
            }
        }
        if ( ! $this->addToDb($object)) {
            return false;
        }
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
     * @throws \OutOfBoundsException
     */
    public function remove($itemID)
    {
        if ($this->_items === null) {
            if ( ! $this->fillItems()) {
                return false;
            }
        }
        if ( ! $this->delFromDb($itemID)) {
            return false;
        }
        if (($item = $this->delItem($itemID)) === false) {
            return false;
        }

        return $item;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        if ( ! $this->delFromDbAll()) {
            return false;
        }
        $this->clearItems();

        return true;
    }

    /**
     * @param mixed $parentID
     *
     * @return bool
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function load($parentID = null)
    {
        if ($parentID !== null) {
            $this->parentID = $parentID;
        }
        if ( ! is_array($rows = $this->selFromDbAll())) {
            return false;
        }

        if ($this->limit !== null) {
            // TODO [alek13]: bring out into Database\MySQL
            $this->doQuery('SELECT FOUND_ROWS()');
            $row                = $this->db()->fetchRow();
            $this->recordsCount = $row[0];
            $this->pagesCount   = ceil($this->recordsCount / $this->recordsPerPage);
            $this->limit        = null;
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
        $cnt = count($this);
        for ($i = 0; $i < $cnt; $i++) {
            if ($this->_items[$i]->id == $itemID) {
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
        if ( ! $count = count($this->_items)) {
            return false;
        }
        /** @var \Colibri\Database\Model $itemClass */
        $itemClass = $this->itemClass;
        /** @noinspection PhpUndefinedVariableInspection */
        $PKfn = $itemClass::$PKFieldName[0];
        for ($i = 0; $i < $count; $i++) {
            if (isset($this->_items[$i]->$PKfn) && $this->_items[$i]->$PKfn == $id) {
                return $this->_items[$i];
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
     * @return \Colibri\Database\DbInterface
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
     * @throws DbException
     */
    public function get()
    {
        if ( ! $this->load()) {
            throw new DbException('failed to load collection');
        }

        return $this;
    }
}
