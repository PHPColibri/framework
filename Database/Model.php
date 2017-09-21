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
     * @param int|array $id_or_row
     *
     * @throws DbException
     * @throws \Exception
     */
    public function __construct($id_or_row = null)
    {
        $metadata           = static::db()->getColumnsMetadata(static::$tableName);
        $this->fields       = &$metadata['fields'];
        $this->fieldTypes   = &$metadata['fieldTypes'];
        $this->fieldLengths = &$metadata['fieldLengths'];

        if ($id_or_row !== null) {
            if (is_array($id_or_row)) {
                $this->fillProperties($id_or_row);
            } else {
                $this->load($id_or_row);
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
     * @param mixed|array $id_or_where
     *
     * @return static
     *
     * @throws DbException
     * @throws \Exception
     */
    public static function find($id_or_where)
    {
        return (new static())->load($id_or_where);
    }

    /**
     * @param mixed|array $id_or_where
     *
     * @return static
     *
     * @throws DbException
     * @throws NotFoundException
     * @throws \Exception
     */
    public static function get($id_or_where)
    {
        $dbObject = static::find($id_or_where);
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
    protected function getPKValues(): array
    {
        $pkWhere = [];
        foreach (static::$PKFieldName as $PKName) {
            $pkWhere[$PKName] = $this->$PKName;
        }

        return $pkWhere;
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
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws \Colibri\Database\DbException
     * @throws \UnexpectedValueException
     */
    protected function loadQuery(): string
    {
        $query = Query::select($this->getFieldsNames())
            ->from(static::$tableName)
            ->where($this->where ?? $this->getPKValues());

        return $query->build(static::db());
    }

    /**
     * @param array|null $attributes
     *
     * @return string
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function createQuery(array $attributes = null): string
    {
        $query = Query::insert()->into(static::$tableName)
            ->set($attributes ?? $this->getFieldsValues());

        return $query->build(static::db());
    }

    /**
     * @param array|null $attributes
     *
     * @return string
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function saveQuery(array $attributes = null): string
    {
        $query = Query::update(static::$tableName)
            ->set($attributes ?? $this->getFieldsValues())
            ->where($this->getPKValues())
        ;

        return $query->build(static::db());
    }

    /**
     * @return string
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function deleteQuery(): string
    {
        $query = Query::delete()
            ->from(static::$tableName)
            ->where($this->getPKValues());

        return $query->build(static::db());
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    private static function isInPrimaryKey(string $field): bool
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
     * @param mixed|array $id_or_where
     *
     * @throws DbException
     * @throws \Exception
     */
    public function delete($id_or_where = null)
    {
        if ($id_or_where !== null) {
            if (is_array($id_or_where)) {
                $this->where = $id_or_where;
            } else {
                $this->where                     = null;
                $this->{static::$PKFieldName[0]} = $id_or_where;
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
        return $this->load($this->getPKValues());
    }

    /**
     * @param mixed|array $id_or_where
     *
     * @return $this|null
     *
     * @throws DbException
     * @throws \Exception
     */
    public function load($id_or_where = null)
    {
        if ($id_or_where !== null) {
            if (is_array($id_or_where)) {
                $this->where = $id_or_where;
            } else {
                $this->where                     = null;
                $this->{static::$PKFieldName[0]} = $id_or_where;
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
     * @param string $sqlQuery
     *
     * @return $this|null
     *
     * @throws DbException
     * @throws \Exception
     */
    protected function loadByQuery($sqlQuery)
    {
        $this->doQuery($sqlQuery);

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
     * @param string $strQuery
     *
     * @throws DbException
     * @throws SqlException
     * @throws \Exception
     */
    protected function doQuery($strQuery)
    {
        self::db()->query($strQuery);

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
        self::db()->queries($arrQueries);

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
        self::db()->commit($queries);

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
