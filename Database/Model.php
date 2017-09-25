<?php
namespace Colibri\Database;

use Carbon\Carbon;
use Colibri\Database\Exception\NotFoundException;
use Colibri\Database\Exception\SqlException;

/**
 * Абстрактный класс объекта базы данных.
 *
 * @property         int|string $id
 */
abstract class Model
{
    /** @var string */
    protected static $tableName = 'tableName_not_set';
    /** @var array */
    protected static $PKFieldName = ['id'];

    /** @var array */
    protected $intermediate;

    /** @var array */
    protected $where = null;

    /** @var array */
    protected $fields = [];
    /** @var array */
    protected $fieldTypes = [];
    /** @var array */
    public $fieldLengths = [];

    /** @var string */
    protected static $connectionName = 'default';

    /**
     * @var array
     */
    protected $collections = [];
    /**
     * @var array
     */
    protected $objects = [];

    /**
     * @param int|array $idOrRow
     *
     * @throws DbException
     * @throws \Exception
     */
    public function __construct($idOrRow = null)
    {
        $metadata           = static::db()->getColumnsMetadata(static::$tableName);
        $this->fields       = &$metadata['fields'];
        $this->fieldTypes   = &$metadata['fieldTypes'];
        $this->fieldLengths = &$metadata['fieldLengths'];

        if ($idOrRow !== null) {
            if (is_array($idOrRow)) {
                $this->fillProperties($idOrRow);
            } else {
                $this->load($idOrRow);
            }
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
     * @throws DbException
     * @throws \Exception
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
     * @throws DbException
     * @throws NotFoundException
     * @throws \Exception
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
     */
    public function getFieldsNames(): array
    {
        $classVars      = array_keys(get_class_vars(get_class($this)));
        $selectedFields = array_intersect($classVars, $this->fields);

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
    final public function getTableName()
    {
        return static::$tableName;
    }

    /**
     * @return DbInterface
     *
     * @throws DbException
     */
    final public static function db()
    {
        return Db::connection(static::$connectionName);
    }

    /**************************************************************************/

    /**
     * @return array
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
     */
    protected function fillProperties(array $row, $cast = true)
    {
        foreach ($row as $propName => $propValue) {
            if ($propValue === null || ! $cast) {
                $this->$propName = $propValue;
                continue;
            }

            $type = isset($this->fieldTypes[$propName]) ? $this->fieldTypes[$propName] : null;
            switch ($type) {
                case 'int':
                case 'integer':
                case 'tinyint':
                case 'smallint':
                case 'mediumint':
                case 'bigint':
                    $this->$propName = (int)$propValue;
                    break;
                case 'bit':
                    $this->$propName = (bool)$propValue;
                    break;
                case 'timestamp':
                    $this->$propName = new Carbon($propValue);
                    break;
                default:
                    $this->$propName = $propValue;
                    break;
            }
        }
    }

    /**
     * @return \Colibri\Database\Query
     *
     * @throws \InvalidArgumentException
     */
    protected function loadQuery(): Query
    {
        return Query::select($this->getFieldsNames())
            ->from(static::$tableName)
            ->where($this->where ?? $this->getPKValue());
    }

    /**
     * @param array|null $attributes
     *
     * @return \Colibri\Database\Query
     */
    protected function createQuery(array $attributes = null): Query
    {
        return Query::insert()->into(static::$tableName)
            ->set($attributes ?? $this->getFieldsValues());
    }

    /**
     * @param array|null $attributes
     *
     * @return \Colibri\Database\Query
     *
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
            ->where($this->getPKValue());
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
     */
    protected function hasColumn($propName): bool
    {
        return in_array($propName, $this->fields);
    }

    /**
     * @param string $propertyName
     *
     * @return ModelCollection|ModelMultiCollection|ModelSingleCollection|Model
     *
     * @throws \Exception
     */
    public function __get($propertyName)
    {
        if (isset($this->collections[$propertyName])) {
            return $this->getRelated($propertyName, $this->collections);
        }
        if (isset($this->objects[$propertyName])) {
            return $this->getRelated($propertyName, $this->objects);
        }
        throw new \Exception('свойство $' . $propertyName . ' в классе ' . get_class($this) . ' не определено или не является public.');
    }

    /**
     * @param string $name
     * @param array  $relationsDefinition
     *
     * @return Model|ModelCollection|ModelSingleCollection|ModelMultiCollection
     */
    private function getRelated($name, &$relationsDefinition)
    {
        $container          = &$relationsDefinition[$name];
        $relatedObject      = &$container[1];
        $relatedObjectClass = $container[0];
        $objectFKName       = isset($container[2]) ? $container[2] : static::$PKFieldName[0];

        return $relatedObject === null
            ? $relatedObject = new $relatedObjectClass($this->$objectFKName)
            : $relatedObject;
    }

    /**
     * @param string $propertyName
     * @param mixed  $propertyValue
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function __set($propertyName, $propertyValue)
    {
        if (self::isInPrimaryKey($propertyName)) {
            return $this->$propertyName = $propertyValue;
        }

        throw new \Exception('свойство $' . $propertyName . ' в классе ' . get_class($this) . ' не определено или не является public.');
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
     * @throws \Exception
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
     * @throws DbException
     * @throws SqlException
     * @throws \Exception
     */
    public function create(array $attributes = null)
    {
        $this->doQuery($this->createQuery($attributes));
        $this->{static::$PKFieldName[0]} = self::db()->lastInsertId();
        if ($attributes) {
            $this->fillProperties($attributes);
        }

        return $this;
    }

    /**
     * @param mixed|array $idOrWhere
     *
     * @throws DbException
     * @throws \Exception
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
     * @throws DbException
     * @throws \Exception
     */
    public function save(array $attributes = null)
    {
        $this->fillProperties($attributes, false);

        $this->doQuery($this->saveQuery($attributes));
    }

    /**
     * @param array $values
     *
     * @return static
     *
     * @throws DbException
     * @throws \Exception
     */
    public static function saveNew(array $values)
    {
        return (new static())->create($values);
    }

    /**
     * @return $this|null
     *
     * @throws \Colibri\Database\DbException
     * @throws \Exception
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
     * @throws DbException
     * @throws \Exception
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
     * @throws DbException
     * @throws \Exception
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
     * @throws DbException
     * @throws \Exception
     */
    protected function loadByQuery($query)
    {
        $this->doQuery($query);

        if (self::db()->getNumRows() == 0) {
            return null;
        }

        $result = self::db()->fetchArray();
        $this->fillProperties($result);

        return $this;
    }

    /**
     * @param array $row
     */
    public function initialize(array $row)
    {
        $this->fillProperties($row);
    }

    /**
     * @return array
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
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * @param Query $query
     *
     * @throws DbException
     * @throws SqlException
     * @throws \Exception
     */
    protected function doQuery(Query $query)
    {
        $db = static::db();
        $db->query($query->build($db));

        $this->cleanUpQueryVars();
    }

    /**
     * @param array $arrQueries
     *
     * @return bool
     *
     * @throws DbException
     * @throws \Exception
     */
    protected function doQueries(array $arrQueries)
    {
        static::db()->queries($arrQueries);

        return true;
    }

    /**
     * @param array $queries
     *
     * @return bool
     *
     * @throws DbException
     * @throws \Exception
     */
    protected function doTransaction(array $queries)
    {
        static::db()->commit($queries);

        return true;
    }

    /**
     * bring out into Query class.
     */
    private function cleanUpQueryVars()
    {
        $this->where = null;
    }
}
