<?php
namespace Colibri\Database\Query;

use Colibri\Util\Enum;

/**
 * Query logic operator: 'and', 'or'
 */
class LogicOp extends Enum
{
    const AND = 'and';
    const OR = 'or';
}
