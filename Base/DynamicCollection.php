<?php
namespace Colibri\Base;

/**
 * class DynamicCollection
 *
 * Реализует интерфейс для доступа к элементам при обращении к клвссу как к массиву
 * А также добавляет функции доступа к свойствам, реализованные в PropertyAccess
 *
 * @author         Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package        xTeam
 * @subpackage     a13FW
 * @version        1.00.4
 */
abstract class DynamicCollection extends PropertyAccess implements IDynamicCollection
{
    protected $_items = null;

    abstract protected function fillItems();

    // ArrayAccess
    public function offsetExists($offset)
    {
        if ($this->_items === null) $this->fillItems();

        return isset($this->_items[$offset]);
    }

    public function offsetGet($offset)
    {
        if ($this->_items === null) $this->fillItems();

        return $this->_items[$offset];
    }

    public function offsetSet($offset, $data)
    {
        if ($this->_items === null) $this->fillItems();

        return $this->_items[$offset] = $data;
    }

    public function offsetUnset($offset)
    {
        if ($this->_items === null) $this->fillItems();
        unset($this->_items[$offset]);
    }

    // Iterator
    public function rewind()
    {
        if ($this->_items === null) $this->fillItems();
        reset($this->_items);
    }

    public function current()
    {
        if ($this->_items === null) $this->fillItems();

        return current($this->_items);
    }

    public function key()
    {
        if ($this->_items === null) $this->fillItems();

        return key($this->_items);
    }

    public function next()
    {
        if ($this->_items === null) $this->fillItems();

        return next($this->_items);
    }

    public function valid()
    {
        if ($this->_items === null) $this->fillItems();

        return $this->current() !== false;
    }

    // Countable
    public function count()
    {
        if ($this->_items === null) $this->fillItems();

        return count($this->_items);
    }


    public function toArray()
    {
        if ($this->_items === null) $this->fillItems();

        return array_values(get_object_vars($this));
    }

    public function toDblArray()
    {
        if ($this->_items === null) $this->fillItems();
        $retArr = [];
        $count  = count($this->_items);
        for ($i = 0; $i < $count; $i++)
            $retArr[] = get_object_vars($this->_items[$i]);

        return $retArr;
    }

    public function toJson()
    {
        return json_encode($this->toDblArray());
    }
}
