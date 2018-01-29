<?php
namespace Colibri\Database\AbstractDb\Driver\Query;

abstract class Result implements ResultInterface
{
    /** @var mixed */
    protected $result;

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
}
