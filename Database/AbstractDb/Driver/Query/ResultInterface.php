<?php
namespace Colibri\Database\AbstractDb\Driver\Query;

interface ResultInterface
{
    /**
     * Returns count of retrieved rows in query result.
     * Количество строк в результате запроса на выборку.
     *
     * @return int
     */
    public function getNumRows();

    /**
     * Достаёт все строки из результата запроса в массив указанного вида(асоциативный,нумеровынный,оба).
     * Fetch all rows from query result as specified(assoc,num,both) array.
     *
     * @param int $method
     *
     * @return array
     */
    public function fetchAll($method = MYSQLI_ASSOC);

    /**
     * Достаёт очередную стоку из результата запроса в виде массива указанниго типа.
     * Fetch row from query result as an associative array, a numeric array, or both.
     *
     * @param int $param Модификатор тива возвращаемого значения
     *                   Возможные параметры: MYSQLI_NUM | MYSQLI_ASSOC | MYSQLI_BOTH
     *
     * @return array|null
     */
    public function fetch($param = MYSQLI_ASSOC);

    /**
     * Возвращает итерируемый `Generator` для дальнейшего простого использования в `foreach`.
     * Returns iterable `Generator` for further easy use in `foreach`.
     *
     * @param int $fetchMethod Fetch type. Модификатор тива возвращаемого значения.
     *                         Возможные параметры: MYSQLI_NUM | MYSQLI_ASSOC | MYSQLI_BOTH
     *
     * @return \Generator
     */
    public function cursor($fetchMethod = MYSQLI_ASSOC): \Generator;
}
