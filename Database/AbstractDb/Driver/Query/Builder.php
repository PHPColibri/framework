<?php
namespace Colibri\Database\AbstractDb\Driver\Query;

use Colibri\Database\AbstractDb\Driver;
use Colibri\Database\Query;
use Colibri\Database\Query\LogicOp;

abstract class Builder
{
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var Query
     */
    protected $query;
    /**
     * @var \Colibri\Database\AbstractDb\Driver\ConnectionInterface
     */
    protected $connection;

    /**
     * Builder constructor.
     *
     * @param Driver\ConnectionInterface $connection
     */
    public function __construct(Driver\ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Подготавливает значение для вставки в строку запроса.
     * Prepares value for insert into query string.
     *
     * @param mixed  $value
     * @param string $type
     *
     * @return float|int|string
     */
    public function prepareValue(&$value, $type)
    {
        if ($value === null) {
            return $value = 'NULL';
        }

        if (is_array($value)) {
            foreach ($value as &$v) {
                $this->prepareValue($v, $type);
            }

            return '(' . implode(', ', $value) . ')';
        }

        switch (strtolower($type)) {
            case 'timestamp':
                $value = is_int($value)
                    ?
                    '\'' . date(static::DATETIME_FORMAT, $value) . '\''
                    :
                    ($value instanceof \DateTime
                        ?
                        '\'' . $value->format(static::DATETIME_FORMAT) . '\''
                        :
                        '\'' . $this->connection->escape($value) . '\''
                    );
                break;

            case 'bit':
            case 'dec':
            case 'decimal':
            case 'tinyint':
            case 'smallint':
            case 'bigint':
            case 'int':
                $value = (int)$value;
                break;
            case 'double':
            case 'float':
                $value = (float)$value;
                break;

            default:
                $value = '\'' . $this->connection->escape($value) . '\'';
        }

        return $value;
    }

    /**
     * @param \Colibri\Database\Query $query
     *
     * @return string
     *
     * @throws \UnexpectedValueException
     */
    public function build(Query $query): string
    {
        $this->query = $query;

        $sql = $this->buildQueryStart();

        switch ($this->query->getType()) {
            case Query\Type::INSERT:
                $sql .=
                    $this->buildInto() .
                    $this->buildSet();
                break;
            case Query\Type::SELECT:
                $sql .=
                    $this->buildColumns() .
                    $this->buildFrom() .
                    $this->buildWhere() .
                    $this->buildOrderBy() .
                    $this->buildGroupBy() .
                    $this->buildLimit();
                break;
            case Query\Type::UPDATE:
                $sql .=
                    ' ' . $this->query->getTable() . ' t' .
                    $this->buildSet() .
                    $this->buildWhere();
                break;
            case Query\Type::DELETE:
                $sql .=
                    $this->buildFrom() .
                    $this->buildWhere();
                break;
            default:
                throw new \UnexpectedValueException('Unexpected value of property $type');
        }

        return $sql;
    }

    /**
     * @param array $clause
     *
     * @return bool
     */
    private static function clauseIsNested(array $clause): bool
    {
        $nestedLogicOp = $clause[0];
        $nestedClauses = $clause[1];

        return is_array($nestedClauses) && ($nestedLogicOp == LogicOp:: AND || $nestedLogicOp == LogicOp:: OR);
    }

    /**
     * @return string
     */
    protected function buildQueryStart(): string
    {
        return $this->query->getType();
    }

    /**
     * @return string
     */
    protected function buildColumns(): string
    {
        $columnsGroups = [];
        foreach ($this->query->getColumns() as $alias => $columns) {
            $columnsGroups[] = $this->buildColumnsGroup($alias, $columns);
        }

        return ' ' . implode(', ', $columnsGroups);
    }

    /**
     * @param $alias
     * @param $columns
     *
     * @return string
     */
    protected function buildColumnsGroup(string $alias, array $columns): string
    {
        $parts = [];
        foreach ($columns as $column) {
            $parts[] = $column instanceof Query\Aggregation
                ? $column->setTableAlias($alias)
                : $alias . '.' . $column;
        }

        return implode(', ', $parts);
    }

    /**
     * @return string
     */
    protected function buildFrom(): string
    {
        $using = $this->query->getType() === Query\Type::DELETE ? 't using ' : '';
        $alias = $this->query->getType() !== Query\Type::INSERT ? ' t' : '';

        return ' from ' . $using . $this->query->getTable() . $alias . $this->buildJoins();
    }

    /**
     * @return string
     */
    protected function buildJoins(): string
    {
        /**
         * формат joins[<alias>] (памятка):
         * (key is joined table alias)
         *      type   => left / right / inner / cross,
         *      table  => joined table name,
         *      column => column in joined table (usually FK),
         *      to     => [to-Alias, to-Column] - of table to which joined-to,.
         */
        $joinSQLs = [];
        foreach ($this->query->getJoins() as $alias => $join) {
            /* @var string $type */
            /* @var string $table */
            /* @var string $column */
            /* @var string $to */
            extract($join);
            list($toAlias, $toColumn) = $to;

            $joinSQLs[] = "$type join $table $alias on $alias.$column = $toAlias.$toColumn";
        }

        return $joinSQLs ? ' ' . implode(' ', $joinSQLs) : '';
    }

    /**
     * @return string
     *
     * @throws \UnexpectedValueException
     */
    protected function buildWhere(): string
    {
        if ($this->query->getWhere() === null) {
            return '';
        }

        $where = $this->query->getWhere();
        if (count($where) !== 1) {
            throw new \UnexpectedValueException('Something went wrong: internal query property should always contain only one root element or bu null');
        }

        if (isset($where[LogicOp:: AND])) {
            $logicOp = LogicOp:: AND;
            $clauses = $where[LogicOp:: AND];
        } else {
            if (isset($where[LogicOp:: OR])) {
                $logicOp = LogicOp:: OR;
                $clauses = $where[LogicOp:: OR];
            } else {
                return false;
            }
        }

        return ' where ' . $this->buildClauses($logicOp, $clauses);
    }

    /**
     * @param string $logicOp
     * @param array  $clauses
     *
     * @return string
     */
    protected function buildClauses(string $logicOp, array $clauses): string
    {
        $clausesParts = [];
        foreach ($clauses as $clause) {
            $clausesParts[] =
                self::clauseIsNested($clause)
                    ? $this->buildClauses(...$clause)
                    : $this->buildClause(...$clause);
        }

        return '(' . implode(' ' . $logicOp . ' ', $clausesParts) . ')';
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @param string $operator
     * @param string $alias
     *
     * @return string
     */
    protected function buildClause(string $name, $value, string $operator, string $alias = null): string
    {
        $table = $alias === 't' || $alias === null
            ? $this->query->getTable()
            : $this->query->getJoins()[$alias]['table'];

        $sqlName  = $alias !== null ? "$alias.`$name`" : "`$name`";
        $sqlValue = $this->prepareValue($value, $this->connection->metadata()->getFieldType($table, $name));

        return "$sqlName $operator $sqlValue";
    }

    /**
     * @return string
     */
    protected function buildOrderBy(): string
    {
        if ($this->query->getOrderBy() === null) {
            return '';
        }

        $orderSQLs = [];
        foreach ($this->query->getOrderBy() as $column => $orientation) {
            $orderSQLs[] = '`' . $column . '` ' . $orientation;
        }

        return ' order by ' . implode(', ', $orderSQLs);
    }

    /**
     * @return string
     */
    protected function buildGroupBy(): string
    {
        if ($this->query->getGroupBy() === null) {
            return '';
        }

        return ' group by `' . implode('`, `', $this->query->getGroupBy()) . '`';
    }

    /**
     * @return string
     */
    protected function buildLimit(): string
    {
        return $this->query->getLimit() ? ' limit ' . implode(', ', $this->query->getLimit()) : '';
    }

    /**
     * @return string
     */
    protected function buildInto(): string
    {
        return ' into ' . $this->query->getTable();
    }

    /**
     * @return string
     */
    protected function buildSet(): string
    {
        $assignments = [];
        foreach ($this->query->getValues() as $column => $value) {
            $alias         = $this->query->getType() !== Query\Type::INSERT ? $value['alias'] : null;
            $name          = $this->query->getType() !== Query\Type::INSERT ? $value['column'] : $column;
            $assignments[] = $this->buildClause($name, $value['value'], '=', $alias);
        }

        return ' set ' . implode(', ', $assignments);
    }
}
