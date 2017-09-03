<?php
namespace Colibri\Database\Query;

use Colibri\Util\Enum;

/**
 * Type of SQL query (INSERT, SELECT, UPDATE, DELETE).
 */
class Type extends Enum
{
    const INSERT = 'insert';
    const SELECT = 'select';
    const UPDATE = 'update';
    const DELETE = 'delete';
}
