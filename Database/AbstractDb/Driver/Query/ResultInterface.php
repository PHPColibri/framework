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
     * Выборка значения одного поля из указанной строки.
     *
     * @param int $row   Строка таблицы
     * @param int $field Столбец таблицы
     */
    public function getResult($row = 0, $field = 0);

    /**
     * Достаёт все строки из результата запроса в массив указанного вида(асоциативный,нумеровынный,оба).
     * Fetch all rows from query result as specified(assoc,num,both) array.
     *
     * @param int $param
     *
     * @return array
     */
    public function fetchAllRows($param = MYSQLI_ASSOC);

    /**
     * Достаёт очередную стоку из результата запроса в виде массива указанниго типа.
     * Fetch row from query result as an associative array, a numeric array, or both.
     *
     * @param int $param Модификатор тива возвращаемого значения
     *                   Возможные параметры: MYSQLI_NUM | MYSQLI_ASSOC | MYSQLI_BOTH
     *
     * @return array
     */
    public function fetchArray($param = MYSQLI_ASSOC);

    /**
     * Достаёт очередную стоку из результата запроса в виде нумерованного массива.
     * Fetch row from query result as an enumerated array.
     *
     * @return array
     */
    public function fetchRow();

    /**
     * Достаёт очередную стоку из результата запроса в виде асоциативного массива (ключи - названия колонок).
     * Fetch row from query result as an associative array.
     *
     * @return array
     */
    public function fetchAssoc();
}
