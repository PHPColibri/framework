<?php
namespace Colibri\Database\Concrete\MySQL\Query;

use Colibri\Database\AbstractDb\Driver\Query;

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
     * Достаёт очередную стоку из результата запроса в виде массива указанниго типа.
     * Fetch row from query result as an associative array, a numeric array, or both.
     *
     * @param int $param Fetch type. Модификатор тива возвращаемого значения.
     *                   Возможные параметры: MYSQLI_NUM | MYSQLI_ASSOC | MYSQLI_BOTH
     *
     * @return array|null
     */
    public function fetch($param = MYSQLI_ASSOC)
    {
        return $this->result->fetch_array($param);
    }
}
