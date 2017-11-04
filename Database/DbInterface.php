<?php
namespace Colibri\Database;

use Colibri\Database\Exception\SqlException;

/**
 * IDb Интерфейс класса для работы с базами данных.
 */
interface DbInterface
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
    public function __construct($host, $login, $pass, $database, $persistent = false);

    /**
     * Открывает соединение с базой данных.
     * Opens connection to database.
     */
    public function open();

    /**
     * Проверка открыт ли коннект к базе.
     * Checks that connection is opened (alive).
     *
     * @return bool
     */
    public function opened();

    /**
     * Закрывает соединения.
     * Closes the connection.
     *
     * @return bool TRUE on success
     */
    public function close();

    /**
     * Получение переменной соединения.
     * Gets the connection.
     *
     * @return mixed
     */
    public function getConnect();

    /**
     * Выборка значения одного поля из указанной строки.
     *
     * @param int $row   Строка таблицы
     * @param int $field Столбец таблицы
     */
    public function getResult($row = 0, $field = 0);

    /**
     * Выполняет запрос к базе данных.
     * Executes given query.
     *
     * @param string $query Строка запроса
     *
     * @throws SqlException
     */
    public function query($query);

    /**
     * Returns count of retrieved rows in query result.
     * Количество строк в результате запроса на выборку.
     *
     * @return int
     */
    public function getNumRows();

    /**
     * Идентификатор последней добавленной записи.
     * Returns the auto generated ID of last insert query.
     *
     * @return mixed
     */
    public function lastInsertId();

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

    /**
     * Возвращает количество строк, затронутых запросом на изменение (insert, update, replace, delete, ...)
     * Returns count of rows that query affected.
     *
     * @return int|string
     */
    public function getAffectedRows();

    /**
     * Открывает транзакцию.
     * Starts database transaction.
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function transactionStart();

    /**
     * Откатывает транзакцию.
     * Rolls back database transaction.
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function transactionRollback();

    /**
     * "Комитит" транзакцию в БД.
     * Commits database transaction.
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function transactionCommit();

    /**
     * Выполняет несколько запросов.
     * Executes a number of $queries.
     *
     * @param array $queries        запросы, которые нужно выполнить. queries to execute.
     * @param bool  $rollbackOnFail нужно ли откатывать транзакцию.   if you need to roll back transaction.
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function queries(array $queries, $rollbackOnFail = false);

    /**
     * Выполняет несколько запросов внутри одной транзакции.
     * Executes number of $queries within transaction.
     *
     * @param array $queries
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function commit(array $queries);

    /**
     * Кеширует и возвращает информацию о полях таблицы.
     * Caches and returns table columns info.
     *
     * @param string $tableName
     *
     * @return array
     */
    public function &getColumnsMetadata($tableName);

    /**
     * Подготавливает значение для вставки в строку запроса.
     * Prepares value for insert into query string.
     *
     * @param mixed  $value
     * @param string $type
     *
     * @return float|int|string
     */
    public function prepareValue(&$value, $type);

    /**
     * Собирает шаблон запроса, подставляя значения из $arguments.
     * Compile query template with specified $arguments array.
     *
     * @param string $tpl
     * @param array  $arguments
     *
     * @return string
     */
    public static function getQueryTemplateArray($tpl, array $arguments);

    /**
     * Возвращает тип поля таблицы.
     * Returns table column type.
     *
     * @param string $table
     * @param string $column
     *
     * @return string
     */
    public function getFieldType(string $table, string $column): string;
}
