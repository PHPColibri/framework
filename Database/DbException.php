<?php
namespace Colibri\Database;

use RuntimeException;

/**
 * Если что-то пошло не так при подключении, при закрытии или ещё где-то, кроме выполнения запроса.
 * If something went wrong on connection, on close, or elsewhere except query execution.
 */
class DbException extends RuntimeException
{
}
