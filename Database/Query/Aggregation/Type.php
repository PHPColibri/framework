<?php
namespace Colibri\Database\Query\Aggregation;

use Colibri\Util\Enum;

class Type extends Enum
{
    const COUNT = 'count';
    const MAX = 'max';
    const MIN = 'min';
    const AVG = 'avg';
}
