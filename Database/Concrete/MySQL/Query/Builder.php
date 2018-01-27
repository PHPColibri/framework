<?php
namespace Colibri\Database\Concrete\MySQL\Query;

use Colibri\Database\AbstractDb;

class Builder extends AbstractDb\Driver\Query\Builder
{
    /**
     * @return string
     */
    protected function buildQueryStart(): string
    {
        return parent::buildQueryStart() . ($this->query->getLimit() ? ' sql_calc_found_rows' : '');
    }
}
