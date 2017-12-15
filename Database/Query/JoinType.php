<?php
namespace Colibri\Database\Query;

use Colibri\Util\Enum;

/**
 * Type of Sql Join-s.
 */
class JoinType extends Enum
{
    const INNER = 'inner';

    const LEFT  = 'left';

    const RIGHT = 'right';

    const CROSS = 'cross';
}
