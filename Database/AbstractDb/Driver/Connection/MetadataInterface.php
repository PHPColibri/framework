<?php
namespace Colibri\Database\AbstractDb\Driver\Connection;

interface MetadataInterface
{

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

    /**
     * Кеширует и возвращает информацию о полях таблицы.
     * Caches and returns table columns info.
     *
     * @param string $tableName
     *
     * @return array
     */
    public function &getColumnsMetadata($tableName);
}
