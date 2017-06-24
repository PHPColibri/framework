<?php
namespace Colibri\Database;

use Exception as PhpException;

/**
 * Если что-то пошло не так при подключении, при закрытии или ещё где-то, кроме выполнения запроса.
 * If something went wrong on connection, on close, or elsewhere except query execution.
 */
class DbException extends PhpException
{

}
