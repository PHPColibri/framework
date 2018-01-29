<?php
namespace Colibri\Database\AbstractDb\Driver;

interface ConnectionInterface
{
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
     * Выполняет запрос к базе данных.
     * Executes given query.
     *
     * @param string $query
     *
     * @return bool|Query\ResultInterface
     *
     * @throws \Colibri\Database\Exception\SqlException
     */
    public function query(string $query);

    /**
     * Запускается при десериализации. Обычно требуется переподключение к базе.
     * Executes on unserialize. Usually reconnect needed.
     */
    public function __wakeup();

    /**
     * @param string $value
     *
     * @return string
     */
    public function escape(string $value): string;

    /**
     * @return \Colibri\Database\AbstractDb\Driver\Connection\Metadata
     */
    public function metadata(): Connection\MetadataInterface;
}
