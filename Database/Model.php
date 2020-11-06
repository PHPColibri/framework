<?php
namespace Colibri\Database;

use Carbon\Carbon;
use Colibri\Database\Exception\NotFoundException;

/**
 * Абстрактный класс объекта базы данных.
 *
 * @property int|string $id
 */
abstract class Model
{
    const    NEW_OBJECT = -1;

    /** @var string */
    protected static $connectionName = 'default';
    /** @var string */
    protected static $tableName = 'tableName_not_set';
    /** @var array */
    protected static $PKFieldName = ['id'];

    /** @var array */
    protected $intermediate;

    /** @var array */
    protected $where = null;

    /** @var array */
    protected static $relations = [];
    /** @var array */
    protected $related = [];

    /**
     * @param int|array $idOrRow
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function __construct($idOrRow = null)
    {
        if ($idOrRow !== null) {
            if (is_array($idOrRow)) {
                $this->fillProperties($idOrRow);
            } else {
                $this->load($idOrRow);
            }
        } else {
            $this->{static::$PKFieldName[0]} = self::NEW_OBJECT;
        }
    }

    /**
     * @param mixed $intermediate
     *
     * @return $this|Model
     */
    public function setIntermediate($intermediate)
    {
        $this->intermediate = $intermediate;

        return $this;
    }

    /**
     * @param string $field
     *
     * @return array|mixed
     */
    public function getIntermediate($field = null)
    {
        return $field === null
            ? $this->intermediate
            : $this->intermediate[$field];
    }

    /**
     * @param mixed|array $idOrWhere
     *
     * @return static
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public static function find($idOrWhere)
    {
        return (new static())->load($idOrWhere);
    }

    /**
     * @param mixed|array $idOrWhere
     *
     * @return static
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\NotFoundException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public static function get($idOrWhere)
    {
        $dbObject = static::find($idOrWhere);
        if ( ! $dbObject) {
            throw new NotFoundException('Model not found');
        }

        return $dbObject;
    }

    /**
     * @return array
     *
     * @throws \Colibri\Database\DbException
     */
    public function getFieldsNames(): array
    {
        $fields         = static::db()->metadata()->getColumnsMetadata(static::getTableName())['fields'];
        $classVars      = array_keys(get_class_vars(static::class));
        $selectedFields = array_intersect($classVars, $fields);

        return $selectedFields;
    }

    /**
     * @return array
     */
    public function getPKFieldName()
    {
        return static::$PKFieldName;
    }

    /**
     * @return string
     */
    final public static function getTableName()
    {
        return static::$tableName;
    }

    /**
     * @return AbstractDb\DriverInterface
     *
     * @throws DbException
     */
    final public static function db()
    {
        return Db::connection(static::$connectionName);
    }

    /**
     * @return array
     *
     * @throws \Colibri\Database\DbException
     */
    protected function getFieldsValues(): array
    {
        $values = [];
        foreach ($this as $propName => $propValue) {
            if ($this->hasColumn($propName) && ! self::isInPrimaryKey($propName)) {
                $values[$propName] = $propValue;
            }
        }

        return $values;
    }

    /**
     * @return array
     */
    public function getPKValue(): array
    {
        $pkWhere = [];
        foreach (static::$PKFieldName as $PKName) {
            $pkWhere[$PKName] = $this->$PKName;
        }

        return $pkWhere;
    }

    /**
     * @param array $id
     *
     * @return \Colibri\Database\Model|$this|static
     *
     * @throws \InvalidArgumentException
     */
    public function setPKValue(array $id)
    {
        if (count($id) !== count(static::$PKFieldName)) {
            throw new \InvalidArgumentException('Can`t set PK: passed values count not identical with primary key fields count');
        }

        if (count($id) === 1) {
            $this->{static::$PKFieldName[0]} = array_values($id)[0];

            return $this;
        }

        foreach ($id as $column => $value) {
            if ( ! self::isInPrimaryKey($column)) {
                throw new \InvalidArgumentException('Can`t set PK: Field `' . $column . '` is not a part of primary key.');
            }

            $this->$column = $value;
        }

        return $this;
    }

    /**
     * @param array $row
     * @param bool  $cast
     *
     * @throws \Colibri\Database\DbException
     */
    protected function fillProperties(array $row, $cast = true)
    {
        foreach ($row as $propName => $propValue) {
            if ($propValue === null || ! $cast) {
                $this->$propName = $propValue;
                continue;
            }

            $type            = static::db()->metadata()->getFieldType(static::getTableName(), $propName);
            $this->$propName = $this->cast($type, $propValue);
        }
    }

    /**
     * @param $type
     * @param $value
     *
     * @return bool|\Carbon\Carbon|int|string
     */
    protected function cast($type, $value)
    {
        switch ($type) {
            case 'int':
            case 'integer':
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'bigint':
                return (int)$value;
            case 'bit':
                return (bool)$value;
            case 'timestamp':
                return new Carbon($value);
            default:
                return $value;
        }
    }

    /**
     * @return \Colibri\Database\Query
     *
     * @throws \Colibri\Database\DbException
     * @throws \InvalidArgumentException
     */
    protected function loadQuery(): Query
    {
        return Query::select($this->getFieldsNames())
            ->from(static::$tableName)
            ->where($this->where ?? $this->getPKValue())
        ;
    }

    /**
     * @param array|null $attributes
     *
     * @return \Colibri\Database\Query
     *
     * @throws \Colibri\Database\DbException
     */
    protected function createQuery(array $attributes = null): Query
    {
        return Query::insert()->into(static::$tableName)
            ->set($attributes ?? $this->getFieldsValues())
        ;
    }

    /**
     * @param array|null $attributes
     *
     * @return \Colibri\Database\Query
     *
     * @throws \Colibri\Database\DbException
     * @throws \InvalidArgumentException
     */
    protected function saveQuery(array $attributes = null): Query
    {
        return Query::update(static::$tableName)
            ->set($attributes ?? $this->getFieldsValues())
            ->where($this->getPKValue())
        ;
    }

    /**
     * @return \Colibri\Database\Query
     *
     * @throws \InvalidArgumentException
     */
    protected function deleteQuery(): Query
    {
        return Query::delete()
            ->from(static::$tableName)
            ->where($this->getPKValue())
        ;
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    protected static function isInPrimaryKey(string $field): bool
    {
        return in_array($field, static::$PKFieldName);
    }

    /**
     * @param $propName
     *
     * @return bool
     *
     * @throws \Colibri\Database\DbException
     */
    protected function hasColumn($propName): bool
    {
        return in_array($propName, static::db()->metadata()->getColumnsMetadata(static::getTableName())['fields']);
    }

    /**
     * @param string $property
     *
     * @return ModelCollection|ModelMultiCollection|ModelSingleCollection|Model
     *
     * @throws \DomainException
     */
    public function __get($property)
    {
        if (isset(static::$relations[$property])) {
            return $this->getRelated($property);
        }

        throw new \DomainException('свойство $' . $property . ' в классе ' . static::class . ' не определено или не является public.');
    }

    /**
     * @param string $name
     *
     * @return Model|ModelCollection|ModelSingleCollection|ModelMultiCollection
     */
    private function getRelated($name)
    {
        $relation = &static::$relations[$name];
        $class    = $relation[0];
        $FKName   = isset($relation[1]) ? $relation[1] : static::$PKFieldName[0];

        $related = &$this->related[$name];

        return $related === null
            ? $related = new $class($this->$FKName)
            : $related;
    }

    /**
     * @param string $propertyName
     * @param mixed  $propertyValue
     *
     * @return mixed
     *
     * @throws \DomainException
     */
    public function __set($propertyName, $propertyValue)
    {
        if (self::isInPrimaryKey($propertyName)) {
            return $this->$propertyName = $propertyValue;
        }

        throw new \DomainException('свойство $' . $propertyName . ' в классе ' . static::class . ' не определено или не является public.');
    }

    /**
     * @param array $where
     *
     * @return $this
     */
    public function where(array $where)
    {
        $this->where = $where;

        return $this;
    }

    /**
     * @param array $where
     *
     * @return bool
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected static function recordExists(array $where)
    {
        $loaded = (new static())->load($where);

        return $loaded !== null;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \UnexpectedValueException
     */
    public function create(array $attributes = null)
    {
        $this->doQuery($this->createQuery($attributes));
        $this->{static::$PKFieldName[0]} = static::db()->lastInsertId();
        if ($attributes) {
            $this->fillProperties($attributes);
        }

        return $this;
    }

    /**
     * @param mixed|array $idOrWhere
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function delete($idOrWhere = null)
    {
        if ($idOrWhere !== null) {
            if (is_array($idOrWhere)) {
                $this->where = $idOrWhere;
            } else {
                $this->where                     = null;
                $this->{static::$PKFieldName[0]} = $idOrWhere;
            }
        }

        $this->doQuery($this->deleteQuery());
    }

    /**
     * @param array $attributes
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function save(array $attributes = null)
    {
        if ($attributes !== null) {
            $this->fillProperties($attributes, false);
        }

        $this->doQuery($this->saveQuery($attributes));
    }

    /**
     * @param array $values
     *
     * @return static
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \UnexpectedValueException
     */
    public static function saveNew(array $values)
    {
        return (new static())->create($values);
    }

    /**
     * @return $this|null
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function reload()
    {
        return $this->load($this->getPKValue());
    }

    /**
     * @param mixed|array $idOrWhere
     *
     * @return $this|null
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function load($idOrWhere = null)
    {
        if ($idOrWhere !== null) {
            if (is_array($idOrWhere)) {
                $this->where = $idOrWhere;
            } else {
                $this->where                     = null;
                $this->{static::$PKFieldName[0]} = $idOrWhere;
            }
        }

        return $this->loadByQuery($this->loadQuery());
    }

    /**
     * @param mixed $id PK value - int, string or array if multifield PK
     *
     * @return static
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\NotFoundException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public static function getById($id)
    {
        return static::get($id);
    }

    /**
     * @param \Colibri\Database\Query $query
     *
     * @return $this|null
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \UnexpectedValueException
     */
    protected function loadByQuery(Query $query)
    {
        $result = $this->doQuery($query);

        if ($result->getNumRows() == 0) {
            return null;
        }

        $this->fillProperties($result->fetch());

        return $this;
    }

    /**
     * @return array
     *
     * @throws \Colibri\Database\DbException
     */
    public function toArray()
    {
        $arrRet = [];
        foreach ($this as $propName => $propValue) {
            if ($this->hasColumn($propName)) {
                $arrRet[$propName] = $propValue;
            }
        }

        return $arrRet;
    }

    /**
     * @return string
     *
     * @throws \Colibri\Database\DbException
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

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
        $result = static::db()->query($query);

        $this->where = null;

        return $result;
    }
}
