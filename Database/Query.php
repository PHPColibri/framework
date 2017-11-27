<?php
namespace Colibri\Database;

use Colibri\Database\Query\LogicOp;

/**
 * Sql Query component container & builder(compiler).
 */
class Query
{
    /** @var \Colibri\Database\DbInterface */
    private $db;
    /** @var string */
    protected $type = null;
    /** @var string */
    private $joinCurrentAlias = 'j1';

    /** @var array */
    protected $columns = null;
    /** @var string */
    protected $table = null;
    /** @var array */
    protected $joins = [];
    /** @var array */
    protected $values = null;

    /** @var array */
    protected $where = null;
    /** @var array */
    protected $orderBy = null;
    /** @var array */
    protected $groupBy = null;
    /** @var array */
    protected $limit = null;

    /**
     * @param string $type one of Query\Type::<CONST>-ants
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $type = null)
    {
        if ( ! Query\Type::isValid($type)) {
            throw new \InvalidArgumentException("Unknown query type '$type'");
        }
        $this->type = $type;
    }

    // 'factories' static functions:
    ///////////////////////////////////////////////////////////////////////////

    /**
     * Creates instance of insert-type Query.
     *
     * @return static
     */
    public static function insert()
    {
        return new static(Query\Type::INSERT);
    }

    /**
     * Creates instance of select-type Query.
     *
     * @param array $columns
     * @param array $joinsColumns
     *
     * @return static
     */
    public static function select(array $columns = ['*'], ...$joinsColumns)
    {
        $query               = new static(Query\Type::SELECT);
        $query->columns['t'] = $columns;

        $alias = 'j1';
        foreach ($joinsColumns as $joinColumns) {
            $query->columns[$alias] = (array)$joinColumns;
            $alias++;
        }

        return $query;
    }

    /**
     * Creates instance of update-type Query.
     *
     * @param string $tableName
     *
     * @return static
     */
    public static function update(string $tableName)
    {
        return (new static(Query\Type::UPDATE))->into($tableName);
    }

    /**
     * Creates instance of delete-type Query.
     *
     * @return static
     */
    public static function delete()
    {
        return new static(Query\Type::DELETE);
    }

    /**
     * @param \Colibri\Database\DbInterface $db
     *
     * @return $this
     */
    public function forDb(DbInterface $db)
    {
        $this->db = $db;

        return $this;
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

    // for where() additional functions:
    ///////////////////////////////////////////////////////////////////////////

    /**
     * @param array  $where array('column [op]' => value, ...)
     * @param string $type  one of 'and'|'or'
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    private static function configureClauses(array $where, $type = 'and')
    {
        if ( ! in_array($type, ['and', 'or'])) {
            throw new \InvalidArgumentException('where-type must be `and` or `or`');
        }
        $whereClauses = [];
        foreach ($where as $name => $value) {
            $nameAndOp = explode(' ', $name, 2);
            $name      = $nameAndOp[0];
            $operator  = isset($nameAndOp[1]) ? $nameAndOp[1] : ($value === null ? 'is' : '=');
            $alias     = self::cutAlias($name);

            $whereClauses[] = [$name, $value, $operator, $alias];
        }

        return [$type => $whereClauses];
    }

    /**
     * @param string $column
     *
     * @return string
     */
    private static function cutAlias(string &$column): string
    {
        $aliasAndColumn = explode('.', $column);
        if (count($aliasAndColumn) === 2) {
            $alias  = $aliasAndColumn[0];
            $column = $aliasAndColumn[1];
        } else {
            $alias  = 't';
            $column = $aliasAndColumn[0];
        }

        return $alias;
    }

    // public user functions:
    ///////////////////////////////////////////////////////////////////////////

    /**
     * @param string $tableName
     *
     * @return $this
     */
    public function into(string $tableName)
    {
        $this->table = $tableName;

        return $this;
    }

    /**
     * @param string|Query $tableName
     *
     * @return $this
     */
    public function from($tableName)
    {
        $this->table = is_string($tableName) ? $tableName : "($tableName)";

        return $this;
    }

    /**
     * @param string $table
     * @param string $column
     * @param string $toColumn
     * @param string $type
     *
     * @return $this
     */
    public function join(string $table, string $column, string $toColumn, string $type = Query\JoinType::LEFT)
    {
        $alias = $this->joinCurrentAlias++;

        $toAlias = self::cutAlias($toColumn);

        $this->joins[$alias] = [
            'type'   => $type,
            'table'  => $table,
            'column' => $column,
            'to'     => [$toAlias, $toColumn],
        ];

        return $this;
    }

    /**
     * @param array $values
     *
     * @return $this
     */
    public function set(array $values)
    {
        $this->values = $this->values === null ? $values : array_replace($this->values, $values);

        return $this;
    }

    /**
     * @param array  $where array('column [op]' => value, ...)
     * @param string $type  one of 'and'|'or'
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    final public function where(array $where, $type = 'and')
    {
        $where = self::configureClauses($where, $type);
        if ($this->where === null) {
            $this->where = $where;

            return $this;
        }

        if (isset($this->where[$type])) {
            $this->where[$type] = array_merge($this->where[$type], $where[$type]);
        } else {
            $this->where = $type == 'or'
                ? ['and' => array_merge($this->where['and'], [['or', $where['or']]])]
                : ['and' => array_merge($where['and'], [['or', $this->where['or']]])];
        }

        return $this;
    }

    /**
     * @param array $plan
     *
     * @return $this
     */
    final public function wherePlan(array $plan)
    {
        $this->where = $plan;

        return $this;
    }

    /**
     * @param array $orderBy array('column1'=>'orientation','column2'=>'orientation'), 'columnN' - name of column,
     *                       'orientation' - ascending or descending abbreviation ('asc' or 'desc')
     *
     * @return $this
     */
    final public function orderBy(array $orderBy)
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * @param array $groupBy
     *
     * @return $this
     */
    final public function groupBy(array $groupBy): self
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    /**
     * @param int $offsetOrCount
     * @param int $count
     *
     * @return $this
     */
    final public function limit(int $offsetOrCount, int $count = null)
    {
        if ($count === null) {
            $this->limit['offset'] = 0;
            $this->limit['count']  = $offsetOrCount;
        } else {
            $this->limit['offset'] = $offsetOrCount;
            $this->limit['count']  = $count;
        }

        return $this;
    }

    /**
     * @param \Colibri\Database\DbInterface $db
     *
     * @return string
     *
     * @throws \UnexpectedValueException
     */
    public function build(DbInterface $db): string
    {
        $this->db = $db;

        $sql = $this->type . ($this->limit ? ' sql_calc_found_rows' : '');

        switch ($this->type) {
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
                    ' ' . $this->table . ' t' .
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
     * @return string
     *
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function __toString()
    {
        if ($this->db === null) {
            throw new \LogicException('Can`t build query: Database not set. Use ::forDb() method before.');
        }

        return $this->build($this->db);
    }

    // private build-functions:
    ///////////////////////////////////////////////////////////////////////////

    /**
     * @return string
     */
    private function buildColumns(): string
    {
        $columnsGroups = [];
        foreach ($this->columns as $alias => $columns) {
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
    private function buildColumnsGroup(string $alias, array $columns): string
    {
        $parts = [];
        foreach ($columns as $column) {
            $parts [] = $column instanceof Query\Aggregation
                ? $column->setTableAlias($alias)
                : $alias . '.' . $column;
        }

        return implode(', ', $parts);
    }

    /**
     * @return string
     */
    private function buildFrom(): string
    {
        $using = $this->type === Query\Type::DELETE ? 't using ' : '';
        $alias = $this->type !== Query\Type::INSERT ? ' t' : '';

        return ' from ' . $using . $this->table . $alias . $this->buildJoins();
    }

    /**
     * @return string
     */
    private function buildJoins(): string
    {
        /**
         * формат $this->joins[<alias>] (памятка):
         * (key is joined table alias)
         *      type   => left / right / inner / cross,
         *      table  => joined table name,
         *      column => column in joined table (usually FK),
         *      to     => [to-Alias, to-Column] - of table to which joined-to,.
         */
        $joinSQLs = [];
        foreach ($this->joins as $alias => $join) {
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
    private function buildWhere(): string
    {
        if ($this->where === null) {
            return '';
        }

        $where = $this->where;
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
    private function buildClauses(string $logicOp, array $clauses): string
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
    private function buildClause(string $name, $value, string $operator, string $alias = null): string
    {
        $table = $alias === 't' || $alias === null
            ? $this->table
            : $this->joins[$alias]['table'];

        $sqlName  = $alias !== null ? "$alias.`$name`" : "`$name`";
        $sqlValue = $this->db->prepareValue($value, $this->db->getFieldType($table, $name));

        return "$sqlName $operator $sqlValue";
    }

    /**
     * @return string
     */
    private function buildOrderBy(): string
    {
        if ($this->orderBy === null) {
            return '';
        }

        $orderSQLs = [];
        foreach ($this->orderBy as $column => $orientation) {
            $orderSQLs[] = '`' . $column . '` ' . $orientation;
        }

        return ' order by ' . implode(', ', $orderSQLs);
    }

    /**
     * @return string
     */
    private function buildGroupBy(): string
    {
        if ($this->groupBy === null) {
            return '';
        }

        return ' group by `' . implode('`, `', $this->groupBy) . '`';
    }

    /**
     * @return string
     */
    private function buildLimit(): string
    {
        return $this->limit ? ' limit ' . implode(', ', $this->limit) : '';
    }

    /**
     * @return string
     */
    private function buildInto(): string
    {
        return ' into ' . $this->table;
    }

    /**
     * @return string
     */
    private function buildSet(): string
    {
        $assignments = [];
        foreach ($this->values as $column => $value) {
            $alias         = $this->type !== Query\Type::INSERT ? self::cutAlias($column) : null;
            $assignments[] = $this->buildClause($column, $value, '=', $alias);
        }

        return ' set ' . implode(', ', $assignments);
    }
}
