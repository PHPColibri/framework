<?php
namespace Colibri\Database\Concrete\MySQL\Query;

use \Colibri\Database\AbstractDb\Driver\Query;

class Result extends Query\Result
{
    /** @var \mysqli_result */
    protected $result;

    public function __construct(\mysqli_result $result)
    {
        $this->result = $result;
    }

    /**
     * Returns count of retrieved rows in query result.
     * Количество строк в результате запроса на выборку.
     *
     * @return int
     */
    public function getNumRows()
    {
        return $this->result->num_rows;
    }

    /**
     * @param int $row
     * @param int $field
     *
     * @return object
     */
    public function getResult($row = 0, $field = 0)
    {
        $this->result->data_seek($row);
        $this->result->field_seek($field);

        return $this->result->fetch_field();
    }

    /**
     * Достаёт очередную стоку из результата запроса в виде массива указанниго типа.
     * Fetch row from query result as an associative array, a numeric array, or both.
     *
     * @param int $param Fetch type. Модификатор тива возвращаемого значения.
     *                   Возможные параметры: MYSQLI_NUM | MYSQLI_ASSOC | MYSQLI_BOTH
     *
     * @return array
     */
    public function fetchArray($param = MYSQLI_ASSOC)
    {
        return $this->result->fetch_array($param);
    }

    /**
     * Достаёт очередную стоку из результата запроса в виде нумерованного массива.
     * Fetch row from query result as an enumerated array.
     *
     * @return array
     */
    public function fetchRow()
    {
        return $this->result->fetch_row();
    }

    /**
     * Достаёт очередную стоку из результата запроса в виде асоциативного массива (ключи - названия колонок).
     * Fetch row from query result as an associative array.
     *
     * @return array
     */
    public function fetchAssoc()
    {
        return $this->result->fetch_assoc();
    }

    /**
     * Достаёт все строки из результата запроса в массив указанного вида(асоциативный,нумеровынный,оба).
     * Fetch all rows from query result as specified(assoc,num,both) array.
     *
     * @param int $param Fetch type. Модификатор тива возвращаемого значения.
     *                   Возможные параметры: MYSQLI_NUM | MYSQLI_ASSOC | MYSQLI_BOTH
     *
     * @return array
     */
    public function &fetchAllRows($param = MYSQLI_ASSOC)
    {
        $return = [];
        while ($row = $this->fetchArray($param)) {
            $return[] = $row;
        }

        return $return;
    }

    /**
     * Достаёт последнюю строку из результата запроса в виде нумерованного массива.
     * Fetch last rows from query result as an enumerated array.
     *
     * @return array
     */
    public function fetchLastRow()
    {
        $this->result->data_seek($this->getNumRows() - 1);

        return $this->result->fetch_row();
    }
}
