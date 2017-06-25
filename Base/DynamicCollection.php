<?php
namespace Colibri\Base;

/**
 * class DynamicCollection
 *
 * Реализует интерфейс для доступа к элементам при обращении к классу как к массиву
 * А также добавляет функции доступа к свойствам, реализованные в PropertyAccess
 */
abstract class DynamicCollection extends PropertyAccess implements IDynamicCollection
{
    /**
     * @var array
     */
    protected $_items = null;

    /**
     * @return void
     */
    abstract protected function fillItems();


    //// ArrayAccess implementation

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        if ($this->_items === null) {
            $this->fillItems();
        }

        return isset($this->_items[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if ($this->_items === null) {
            $this->fillItems();
        }

        return $this->_items[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $data
     *
     * @return mixed
     */
    public function offsetSet($offset, $data)
    {
        if ($this->_items === null) {
            $this->fillItems();
        }

        return $this->_items[$offset] = $data;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if ($this->_items === null) {
            $this->fillItems();
        }
        unset($this->_items[$offset]);
    }

    //// Iterator implementation

    /**
     * @return void
     */
    public function rewind()
    {
        if ($this->_items === null) {
            $this->fillItems();
        }
        reset($this->_items);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        if ($this->_items === null) {
            $this->fillItems();
        }

        return current($this->_items);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        if ($this->_items === null) {
            $this->fillItems();
        }

        return key($this->_items);
    }

    /**
     * @return mixed
     */
    public function next()
    {
        if ($this->_items === null) {
            $this->fillItems();
        }

        return next($this->_items);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        if ($this->_items === null) {
            $this->fillItems();
        }

        return $this->current() !== false;
    }

    //// Countable implementation

    /**
     * @return int
     */
    public function count()
    {
        if ($this->_items === null) {
            $this->fillItems();
        }

        return count($this->_items);
    }

    //// Additional methods

    /**
     * @return array
     */
    public function toArray()
    {
        if ($this->_items === null) {
            $this->fillItems();
        }

        return array_values(get_object_vars($this));
    }

    /**
     * @return array[]
     */
    public function toDblArray()
    {
        if ($this->_items === null) {
            $this->fillItems();
        }
        $retArr = [];
        $count  = count($this->_items);
        for ($i = 0; $i < $count; $i++)
            $retArr[] = get_object_vars($this->_items[$i]);

        return $retArr;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toDblArray());
    }
}
