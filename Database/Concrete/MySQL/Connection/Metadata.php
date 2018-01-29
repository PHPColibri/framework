<?php
namespace Colibri\Database\Concrete\MySQL\Connection;

use Colibri\Database\AbstractDb\Driver\Connection\Metadata as AbstractMetadata;

class Metadata extends AbstractMetadata
{
    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * Возвращает информацию о полях таблицы.
     * Returns table columns info.
     *
     * @param string $tableName
     *
     * @return array
     */
    protected function &retrieveColumnsMetadata($tableName)
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $result = $this->connection->query('SHOW COLUMNS FROM ' . $tableName);
        $result = $result->fetchAll();

        $fields       = [];
        $fieldTypes   = [];
        $fieldLengths = [];

        $cnt = count($result);
        for ($i = 0; $i < $cnt; $i++) {
            $fName                = &$result[$i]['Field'];
            $fType                = &$result[$i]['Type'];
            $fields[]             = &$fName;
            $fieldTypes[$fName]   = explode('(', $fType)[0];
            $fieldLengths[$fName] = $this->extractFieldTypeLength($fType);
        }

        $returnArray = [ // compact() ???
            'fields'       => &$fields,
            'fieldTypes'   => &$fieldTypes,
            'fieldLengths' => &$fieldLengths,
        ];

        return $returnArray;
    }

    /**
     * @param string $strFieldType
     *
     * @return int|null
     */
    private function extractFieldTypeLength(&$strFieldType)
    {
        $len = explode(')', $strFieldType);
        $len = explode('(', $len[0]);

        return (int)(count($len) > 1 ? $len[1] : null);
    }
}
