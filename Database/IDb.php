<?php
namespace Colibri\Database;

use Colibri\Database\Exception\SqlException;

/**
 * IDb Интерфейс класса для работы с базами данных
 *
 * @author         alek13
 * @version        1.00
 * @package        xTeam
 * @subpackage     a13FW
 */
interface IDb
{
    /**
     * Конструктор
     *
     * @param      $host
     * @param      $login
     * @param      $pass
     * @param      $database
     * @param bool $persistent
     */
    function __construct($host, $login, $pass, $database, $persistent = false);

    /**
     * Открытие соединения
     */
    public function open();

    /**
     * Проверка открыт ли коннект к базе
     *
     * @return bool
     */
    public function opened();

    /**
     * Закрытие соединения
     */
    public function close();

    /**
     * Получение переменной соединения
     */
    public function getConnect();

    /**
     * Выборка значения одного поля таблицы из результата
     *
     * @param int $row   Строка таблицы
     * @param int $field Столбец таблицы
     */
    public function getResult($row = 0, $field = 0);

    /**
     * Запрос к базе данных
     *
     * @param string $query_string Строка запроса
     *
     * @throws SqlException
     */
    public function query($query_string);

    /**
     * Количество строк в результате запроса на выборку
     */
    public function getNumRows();

    /**
     * Идентификатор последней добавленной записи
     */
    public function lastInsertId();

    /**
     * Выгрузка результата запроса в массив
     *
     * @param int $param
     *
     * @return
     */
    public function fetchAllRows($param = MYSQLI_ASSOC);

    /**
     * Стока результата запроса в виде массива
     *
     * @param int $param Модификатор тива возвращаемого значения
     *                   Возможные параметры: MYSQLI_NUM | MYSQLI_ASSOC | MYSQLI_BOTH
     */
    public function fetchArray($param = MYSQLI_ASSOC);

    /**
     * Возвращает ассоциативный массив с названиями индексов,
     * соответсвующими названиям колонок
     */
    public function fetchAssoc();

    /**
     * Количество строк в результате запроса на изменение
     */
    public function getAffectedRows();

    public function transactionStart();

    public function transactionRollback();

    public function transactionCommit();

    public function queries(array $arrQueries, $rollbackOnFail = false);

    public function commit(array $arrQueries);

    /**
     * @param string $tableName
     *
     * @return array
     */
    public function &getColumnsMetadata($tableName);

    public function prepareValue(&$value, $type);

    public static function getQueryTemplateArray($tpl, $arguments);
}
