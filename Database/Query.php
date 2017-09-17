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
     * @return static
     */
    public static function update()
    {
        return new static(Query\Type::UPDATE);
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

    // for where() additional functions:
    ///////////////////////////////////////////////////////////////////////////

    /**
     * @param array  $where
     * @param string $type  one of 'and'|'or'
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    private function configureClauses(array $where, $type = 'and')
    {
        if ( ! in_array($type, ['and', 'or'])) {
            throw new \InvalidArgumentException('where-type must be `and` or `or`');
        }
        $whereClauses = [];
        foreach ($where as $name => $value) {
            $whereClauses[] = [$name, $value];
        }

        return [$type => $whereClauses];
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
     * @param string $tableName
     *
     * @return $this
     */
    public function from(string $tableName)
    {
        $this->table = $tableName;

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
        $toAliasAndColumn = explode('.', $toColumn);
        if (count($toAliasAndColumn) === 2) {
            $toAlias  = $toAliasAndColumn[0];
            $toColumn = $toAliasAndColumn[1];
        } else {
            $toAlias  = 't';
            $toColumn = $toAliasAndColumn[0];
        }


        $this->joins[] = [
            'type'   => $type,
            'table'  => $table,
            'alias'  => $this->joinCurrentAlias++,
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
    public function values(array $values)
    {
        $this->values = $values;

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
        $where = $this->configureClauses($where, $type);
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
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function build(DbInterface $db): string
    {
        $this->db = $db;

        $sql = $this->type;

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
                    $this->buildWhere();
                break;
            case Query\Type::UPDATE:
                $sql .=
                    ' ' . $this->table .
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

        return $sql . ';';
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
            $columnsGroups[] = $alias . '.' . implode(", $alias.", $columns);
        }

        return ' ' . implode(', ', $columnsGroups);
    }

    /**
     * @return string
     */
    private function buildFrom(): string
    {
        $alias = $this->type == Query\Type::SELECT ? ' t' : '';

        return ' from ' . $this->table . $alias . $this->buildJoins();
    }

    /**
     * @return string
     */
    private function buildJoins(): string
    {
        /**
         * формат $this->joins[i] (памятка):
         *      type   => left / right / inner / cross,
         *      table  => joined table name,
         *      alias  => joined table alias,
         *      column => column in joined table (usually FK),
         *      to     => [to-Alias, to-Column] - of table to which joined-to,
         */
        $joinSQLs = [];
        foreach ($this->joins as $join) {
            /** @var string $type */ /** @var string $table */ /** @var string $alias */
            /** @var string $column */ /** @var string $to */
            extract($join);
            list($toAlias, $toColumn) = $to;
            $joinSQLs[] = "$type join $table $alias on $alias.$column = $toAlias.$toColumn";
        }

        return $joinSQLs ? ' ' . implode(' ', $joinSQLs) : '';
    }

    /**
     * @return string
     *
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
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

        return ' where ' . $this->buildClauses($clauses, $logicOp);
    }

    /**
     * @param array  $clauses
     * @param string $logicOp
     *
     * @return string
     *
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     */
    private function buildClauses(array $clauses, string $logicOp): string
    {
        $clausesParts = [];
        foreach ($clauses as $clause) {
            $name  = $nestedLogicOp = $clause[0];
            $value = $nestedClauses = $clause[1];

            $clausesParts[] =
                is_array($nestedClauses) && ($nestedLogicOp == LogicOp:: AND || $nestedLogicOp == LogicOp:: OR)
                    ? $this->buildClauses($nestedClauses, $nestedLogicOp)
                    : $this->buildClause($name, $value);
        }

        return '(' . implode(' ' . $logicOp . ' ', $clausesParts) . ')';
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return string
     *
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     */
    private function buildClause($name, $value): string
    {
        $nameAndOp = explode(' ', $name, 2);
        $name      = $nameAndOp[0];
        $operator  = isset($nameAndOp[1]) ? $nameAndOp[1] : ($value === null ? 'is' : '=');
        $value     = $this->db->prepareValue($value, $this->db->getFieldType($this->table, $name));

        return "`$name` $operator $value";
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
     *
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     */
    private function buildSet(): string
    {
        $assignments = [];
        foreach ($this->values as $column => $value) {
            $assignments[] = $this->buildClause($column, $value);
        }

        return ' set ' . implode(', ', $assignments);
    }
}
