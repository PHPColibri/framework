<?php
namespace Colibri\Database\AbstractDb;

use Colibri\Database\AbstractDb\Driver\ConnectionInterface;
use Colibri\Database\Exception\SqlException;

/**
 * IDb Интерфейс класса для работы с базами данных.
 */
interface DriverInterface
{
    /**
     * Конструктор
     *
     * @param \Colibri\Database\AbstractDb\Driver\ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection);

    /**
     * Получение переменной соединения.
     * Gets the connection.
     *
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface;

    /**
     * Выполняет запрос к базе данных.
     * Executes given query.
     *
     * @param string $query Строка запроса
     *
     * @return bool|\Colibri\Database\AbstractDb\Driver\Query\ResultInterface
     *
     * @throws SqlException
     */
    public function query($query);

    /**
     * Идентификатор последней добавленной записи.
     * Returns the auto generated ID of last insert query.
     *
     * @return mixed
     */
    public function lastInsertId();

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
     * @return \Colibri\Database\AbstractDb\Driver\Query\Builder
     */
    public function getQueryBuilder(): Driver\Query\Builder;

    /**
     * @return mixed
     */
    public function metadata(): Driver\Connection\MetadataInterface;
}
