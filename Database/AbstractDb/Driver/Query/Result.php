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
     * @param int $method Fetch type. Модификатор тива возвращаемого значения.
     *                    Возможные параметры: MYSQLI_NUM | MYSQLI_ASSOC | MYSQLI_BOTH
     *
     * @return array
     */
    public function &fetchAll($method = MYSQLI_ASSOC)
    {
        $allRows = [];
        foreach ($this->cursor($method) as $row) {
            $allRows[] = $row;
        }

        return $allRows;
    }

    /**
     * Возвращает итерируемый `Generator` для дальнейшего простого использования в `foreach`.
     * Returns iterable `Generator` for further easy use in `foreach`.
     *
     * @param int $fetchMethod Fetch type. Модификатор тива возвращаемого значения.
     *                         Возможные параметры: MYSQLI_NUM | MYSQLI_ASSOC | MYSQLI_BOTH
     *
     * @return \Generator
     */
    public function cursor($fetchMethod = MYSQLI_ASSOC): \Generator
    {
        while ($row = $this->fetch($fetchMethod)) {
            yield $row;
        }
    }
}
