<?php
namespace Colibri\Base;

/**
 * class DynamicCollection.
 *
 * Реализует интерфейс для доступа к элементам при обращении к классу как к массиву
 * А также добавляет функции доступа к свойствам, реализованные в PropertyAccess
 */
abstract class DynamicCollection implements DynamicCollectionInterface
{
    /**
     * @var array
     */
    protected $items = null;

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
        if ($this->items === null) {
            $this->fillItems();
        }

        return isset($this->items[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if ($this->items === null) {
            $this->fillItems();
        }

        return $this->items[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $data
     *
     * @return mixed
     */
    public function offsetSet($offset, $data)
    {
        if ($this->items === null) {
            $this->fillItems();
        }

        return $this->items[$offset] = $data;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if ($this->items === null) {
            $this->fillItems();
        }
        unset($this->items[$offset]);
    }

    //// Iterator implementation

    /**
     * @return void
     */
    public function rewind()
    {
        if ($this->items === null) {
            $this->fillItems();
        }
        reset($this->items);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        if ($this->items === null) {
            $this->fillItems();
        }

        return current($this->items);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        if ($this->items === null) {
            $this->fillItems();
        }

        return key($this->items);
    }

    /**
     * @return mixed
     */
    public function next()
    {
        if ($this->items === null) {
            $this->fillItems();
        }

        return next($this->items);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        if ($this->items === null) {
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
        if ($this->items === null) {
            $this->fillItems();
        }

        return count($this->items);
    }

    //// Additional methods

    /**
     * @return array
     */
    public function toArray()
    {
        if ($this->items === null) {
            $this->fillItems();
        }

        return array_values(get_object_vars($this));
    }

    /**
     * @return array[]
     */
    public function toDblArray()
    {
        if ($this->items === null) {
            $this->fillItems();
        }
        $retArr = [];
        $count  = count($this->items);
        for ($i = 0; $i < $count; $i++) {
            $retArr[] = get_object_vars($this->items[$i]);
        }

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
