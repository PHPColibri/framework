<?php
namespace Colibri\Database;

use Carbon\Carbon;
use Colibri\Database\Exception\NotFoundException;
use Colibri\Database\Exception\SqlException;

/**
 * Абстрактный класс объекта базы данных.
 *
 * @property         int|string $id
 *
 * @method string createQuery()
 * @method string deleteQuery()
 * @method string saveQuery()
 * @method string loadQuery()
 */
abstract class Object implements IObject
{
    const    NEW_OBJECT = -1;
    const    LOAD_ERROR = -2;

    /** @var bool */
    public static $debug = true;

    /** @var string */
    protected static $tableName = 'tableName_not_set';
    /** @var array */
    protected static $PKFieldName = ['id'];

    /** @var array */
    protected $intermediate;

    /** @var array */
    protected $fieldsNameValuesArray = null;
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

        if ($id_or_row === null) {
            $this->{static::$PKFieldName[0]} = self::NEW_OBJECT;
        } else {
            if (is_array($id_or_row)) {
                $this->fillProperties($id_or_row);
            } else {
                if ( ! $this->load($id_or_row)) {
                    $this->{static::$PKFieldName[0]} = self::LOAD_ERROR;
                }
            }
        }
    }

    /**
     * @param mixed $intermediate
     *
     * @return $this|Object
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
     * @throws DbException
     * @throws \Exception
     */
    public static function find($id_or_where)
    {
        $dbObject = new static();
        $loaded   = $dbObject->load($id_or_where);

        return $loaded
            ? $dbObject
            : null;
    }

    /**
     * @param mixed|array $id_or_where
     *
     * @return static
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
     * @param string $everyFieldPrefix
     *
     * @return string
     */
    public function getFieldsNamesList($everyFieldPrefix = '')
    {
        $classVars      = array_keys(get_class_vars(get_class($this)));
        $selectedFields = array_intersect($classVars, $this->fields);

        return $everyFieldPrefix . '`' . implode('`, ' . $everyFieldPrefix . '`', $selectedFields) . '`';
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
     * @return IDb
     * @throws DbException
     */
    final public static function db()
    {
        return Db::connection(static::$connectionName);
    }
    /**************************************************************************/
    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return string
     * @throws DbException
     */
    protected function buildNameEqValue($name, $value)
    {
        $nameAndOp = explode(' ', $name, 2);
        $name      = $nameAndOp[0];
        $operator  = isset($nameAndOp[1]) ? $nameAndOp[1] : ($value === null ? 'is' : '=');
        $value     = self::db()->prepareValue($value, $this->fieldTypes[$name]);

        return "`$name` $operator $value";
    }

    /**
     * @param string $fieldPrefix
     *
     * @return string
     * @throws DbException
     */
    protected function getFieldsNameValueList($fieldPrefix = '')
    {
        $obj = $this->fieldsNameValuesArray === null ? $this : $this->fieldsNameValuesArray;

        $strList = '';
        foreach ($obj as $propName => $propValue) {
            if (in_array($propName, $this->fields) && (
                $this->fieldsNameValuesArray === null
                    ? ! in_array($propName, static::$PKFieldName)
                    : true
                )
            ) {
                $strList .= ', ' . $fieldPrefix . $this->buildNameEqValue($propName, $propValue);
            }
        }

        return substr($strList, 2);
    }

    /**
     * @param string $fieldPrefix
     *
     * @return string
     * @throws DbException
     */
    protected function getWhereCondition($fieldPrefix = '')
    {
        $strList = '';
        foreach ($this->where as $name => $value) {
            $strList .= ' AND ' . $fieldPrefix . $this->buildNameEqValue($name, $value);
        }

        return substr($strList, 5);
    }

    /**
     * @param string $fieldPrefix
     *
     * @return string
     * @throws DbException
     */
    protected function getPKCondition($fieldPrefix = '')
    {
        $strList = '';
        foreach (static::$PKFieldName as $PKName) {
            $strList .= ' AND ' . $fieldPrefix . $this->buildNameEqValue($PKName, $this->$PKName);
        }

        return substr($strList, 5);
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
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        switch ($name) {
            case 'createQuery':
                /** @noinspection SqlNoDataSourceInspection */
                $tpl = 'INSERT INTO `' . static::$tableName . '` SET ' . $this->getFieldsNameValueList();
                break;
            case 'deleteQuery':
                /** @noinspection SqlNoDataSourceInspection */
                $tpl = 'DELETE FROM `' . static::$tableName . '` WHERE ' . $this->getPKCondition();
                break;
            case 'saveQuery':
                $tpl = 'UPDATE `' . static::$tableName . '` SET ' . $this->getFieldsNameValueList() . ' WHERE ' . $this->getPKCondition();
                break;
            case 'loadQuery':
                $tpl = 'SELECT ' . $this->getFieldsNamesList() . ' FROM `' . static::$tableName . '` WHERE ' . ($this->where === null ? $this->getPKCondition() : $this->getWhereCondition());
                break;
            default:
                throw new \Exception('unknown query __called method name.');
        }

        return self::db()->getQueryTemplateArray($tpl, $arguments);
    }

    /**
     * @param string $propertyName
     *
     * @return ObjectCollection|ObjectMultiCollection|ObjectSingleCollection|Object
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
     * @return Object|ObjectCollection|ObjectSingleCollection|ObjectMultiCollection
     */
    private function getRelated($name, &$relationsDefinition)
    {
        $container          = &$relationsDefinition[$name];
        $relatedObject      = &$container[1];
        $relatedObjectClass = $container[0];
        $objectFKName       = isset($container[2]) ? $container[2] : static::$PKFieldName[0]; // TODO:

        return $relatedObject === null
            ? $relatedObject = new $relatedObjectClass($this->$objectFKName) // TODO: ссылки бывают не только на PK
            : $relatedObject;
    }

    /**
     * @param string $propertyName
     * @param mixed  $propertyValue
     *
     * @return mixed
     * @throws \Exception
     */
    public function __set($propertyName, $propertyValue)
    {
        if (in_array($propertyName, static::$PKFieldName)) {
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
     * @throws \Exception
     */
    protected static function recordExists(array $where)
    {
        $dbObject = new static();
        $loaded   = $dbObject->load($where);
        if ($loaded === false) {
            throw new \Exception('can`t load user. where clause: ' . var_export($where));
        }

        return $loaded !== null;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     * @throws DbException
     * @throws \Exception
     */
    public function create(array $attributes = null)
    {
        $this->fieldsNameValuesArray = $attributes;
        $this->doQuery($this->createQuery());
        $this->{static::$PKFieldName[0]} = self::db()->lastInsertId();
        if ($attributes) {
            $this->fillProperties($attributes);
        }

        return $this;
    }

    /**
     * @param mixed|array $id_or_where
     *
     * @return bool
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
        } else {
            ;
        }

        return $this->doQuery($this->deleteQuery());
    }

    /**
     * @param array $attributes
     *
     * @return bool
     * @throws DbException
     * @throws \Exception
     */
    public function save(array $attributes = null)
    {
        $this->fieldsNameValuesArray = $attributes;
        $this->fillProperties($attributes, false);

        return $this->doQuery($this->saveQuery());
    }

    /**
     * @param array $values
     *
     * @return static
     * @throws DbException
     * @throws \Exception
     */
    public static function saveNew(array $values)
    {
        return (new static())->create($values);
    }

    /**
     * @return bool|null
     * @throws \Colibri\Database\DbException
     * @throws \Exception
     */
    public function reload()
    {
        return $this->load();
    }

    /**
     * @param mixed|array $id_or_where
     *
     * @return bool|null
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
        } else {
            ;
        }
        if ( ! $this->doQuery($this->loadQuery())) {
            return false;
        }// sql error
        if (self::db()->getNumRows() == 0) {
            return null;
        } // no  record
        if ( ! $result = self::db()->fetchArray()) {
            return false;
        }
        $this->fillProperties($result);

        return true;
    }

    /**
     *
     * @param mixed $id PK value - int, string or array if multifield PK
     *
     * @return static
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
     * @return bool|null
     * @throws DbException
     * @throws \Exception
     */
    protected function loadByQuery($sqlQuery)
    {
        if ( ! $this->doQuery($sqlQuery)) {
            return false;
        }// sql error
        if (self::db()->getNumRows() == 0) {
            return null;
        } // no  record
        if ( ! $result = self::db()->fetchArray()) {
            return false;
        }
        $this->fillProperties($result);

        return true;
    }

    /**
     * @param array $row
     */
    public function initialize(array $row)
    {
        $this->fillProperties($row);
    }

    /**
     * @deprecated will be removed
     * @return string
     */
    public function getFieldsAsXMLstring()
    {
        $strXMLPart = '';
        foreach ($this as $propName => $propValue) {
            if (in_array($propName, $this->fields)) {
                $strXMLPart .= '<' . $propName . '>' . (is_null($propValue) ? '<null />' : $propValue) . '</' . $propName . '>';
            }
        }

        return $strXMLPart;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $arrRet = [];
        foreach ($this as $propName => $propValue) {
            if (in_array($propName, $this->fields)) {
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
     * @return bool true on success or false on failure (if no exceptions on)
     * @throws DbException
     * @throws SqlException
     * @throws \Exception
     */
    protected function doQuery($strQuery)
    {
        self::db()->query($strQuery);

        $this->cleanUpQueryVars();

        return true;
    }

    /**
     * @param array $arrQueries
     *
     * @return bool
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
     * @throws DbException
     * @throws \Exception
     */
    protected function doTransaction(array $queries)
    {
        self::db()->commit($queries);

        return true;
    }

    /**
     * bring out into Query class
     */
    private function cleanUpQueryVars()
    {
        $this->where                 = null;
        $this->fieldsNameValuesArray = null;
    }
}
