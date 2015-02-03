<?php
namespace Colibri\Database;

/**
 * IDb Интерфейс класса для работы с базами данных
 *  
 * @author		alek13
 * @version		1.00
 * @package		xTeam
 * @subpackage	a13FW
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
	 * Закрытие соединения
	 */
	public function close();
	/**
	 * Получение переменной соединения
	 */
	public function getConnect();
	/**
	 * Выборка значения одного поля таблицы из результата
	 * @param int $row Строка таблицы
	 * @param int $field Столбец таблицы
 	 */
	public function getResult($row = 0, $field = 0);
	/**
	 * Запрос к базе данных
	 * @param string $query_string Строка запроса
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
	public function fetchAllRows($param = MYSQL_ASSOC);
	/**
	 * Стока результата запроса в виде массива
	 * @param int $param Модификатор тива возвращаемого значения
	 * Возможные параметры: MYSQL_NUM | MYSQL_ASSOC | MYSQL_BOTH
	 */
	public function fetchArray($param = MYSQL_ASSOC);
	/**
	 * Возвращает ассоциативный массив с названиями индексов,
	 * соответсвующими названиям колонок
	 */
	public function fetchAssoc();
	/**
	 * Количество строк в результате запроса на изменение
	 */
	public function getAffectedRows();
	
	public	function	transactionStart();
	public	function	transactionRollback();
	public	function	transactionCommit();

    public function queries(array $arrQueries,$rollbackOnFail=false);

    public function commit(array $arrQueries);

    /**
     * @param string $tableName
     * @return array
     */
    public function getColumnsMetadata($tableName);

    public static function prepareValue(&$value, $type);

    public static function getQueryTemplateArray($tpl, $arguments);
}
