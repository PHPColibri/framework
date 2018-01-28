<?php
namespace Colibri\Database\Concrete;

use Colibri\Database\AbstractDb\Driver;
use Colibri\Database\Exception\SqlException;

/**
 * Класс для работы с MySQL.
 */
class MySQL extends Driver
{
    /** @var \Colibri\Database\Concrete\MySQL\Connection $connection*/
    protected $connection;

    /**
     * Возвращает количество строк, затронутых запросом на изменение (insert, update, replace, delete, ...)
     * Returns count of rows that query affected.
     *
     * @return int|string returns string if count > PHP_INT_MAX
     */
    public function getAffectedRows()
    {
        return $this->connection->getAffectedRows();
    }

    /**
     * Идентификатор последней добавленной записи.
     * Returns the auto generated ID of last insert query.
     *
     * @return mixed
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Выполняет запрос к базе данных.
     * Executes given query.
     *
     * @param string $query
     *
     * @return bool|\Colibri\Database\AbstractDb\Driver\Query\ResultInterface
     *
     * @throws \Colibri\Database\Exception\SqlException
     * @global int   $time
     */
    public function query($query)//: Driver\Query\ResultInterface
    {
        return $this->connection->query($query);
    }

    /**
     * Соьирает шаблон запроса, подставляя значения из $arguments.
     * Compile query template with specified $arguments array.
     *
     * @param string $tpl
     * @param array  $arguments
     *
     * @return string
     */
    public static function getQueryTemplateArray($tpl, array $arguments)
    {
        $argNum = count($arguments);
        for ($i = $argNum; $i > 0; $i--) {
            $tpl = str_replace('%' . $i, $arguments[$i - 1], $tpl);
        }

        return $tpl;
    }

    /**
     * Собирает шаблон запроса, подставляя значения из переданных в метод агргументов.
     * Compile query template with passed into method arguments.
     *
     * @param string $template
     * @param array  $arguments
     *
     * @return string
     */
    public static function getQueryTemplate($template, ...$arguments)
    {
        $strQuery = $template;
        foreach ($arguments as $i => &$argument) {
            $strQuery = str_replace('%' . $i, $argument, $strQuery);
        }

        return $strQuery;
    }

    /**
     * Выполняет запрос, собранный из указанного шаблона, подставив значения из переданных в метод агргументов.
     * Executes query template compiles with passed into method arguments.
     *
     * @param string $template
     * @param array  $arguments
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function queryTemplate($template, ...$arguments)
    {
        $strQuery = self::getQueryTemplate($template, $arguments);

        $this->query($strQuery);
    }

    /**
     * Открывает транзакцию.
     * Starts database transaction.
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function transactionStart()
    {
        $this->query('START TRANSACTION;');
    }

    /**
     * Откатывает транзакцию.
     * Rolls back database transaction.
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function transactionRollback()
    {
        $this->query('ROLLBACK;');
    }

    /**
     * "Комитит" транзакцию в БД.
     * Commits database transaction.
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function transactionCommit()
    {
        $this->query('COMMIT;');
    }

    /**
     * Возвращает информацию о внешних ключах таблицы.
     * Returns table foreign keys info.
     *
     * @param string $tableName
     *
     * @return array fields:   [TABLE_SCHEMA] ,TABLE_NAME, COLUMN_NAME
     *               refs to:  [REFERENCED_TABLE_SCHEMA ], REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
     *
     * @throws SqlException
     */
    public function getTableFKs($tableName)
    {
        return $this
            ->query('SELECT * FROM `KEY_COLUMN_USAGE` WHERE `TABLE_NAME` = \'' . $tableName . '\' AND `REFERENCED_COLUMN_NAME` IS NOT NULL')
            ->fetchAllRows();
    }

    /**
     * Выполняет несколько запросов внутри одной транзакции.
     * Executes number of $queries within transaction.
     *
     * @param array $queries
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function commit(array $queries)
    {
        $this->transactionStart();
        $this->queries($queries, true);
        $this->transactionCommit();
    }
}
