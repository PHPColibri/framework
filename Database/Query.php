<?php
namespace Colibri\Database;

/**
 * Sql Query component container & builder(compiler).
 */
class Query
{
    /** @var \Colibri\Database\AbstractDb\DriverInterface */
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
     * @param \Colibri\Database\AbstractDb\DriverInterface $db
     *
     * @return $this
     */
    public function forDb(AbstractDb\DriverInterface $db)
    {
        $this->db = $db;

        return $this;
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
            $operator  = $nameAndOp[1] ?? ($value === null ? 'is' : '=');
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
        foreach ($values as $column => &$value) {
            $value = [
                'alias'  => self::cutAlias($column),
                'column' => $column,
                'value'  => $value,
            ];
        }

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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array|null
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return array
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @return array|null
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return array|null
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @return array|null
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @return array|null
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @return array|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param \Colibri\Database\AbstractDb\DriverInterface $db
     *
     * @return string
     *
     * @throws \UnexpectedValueException
     */
    public function build(AbstractDb\DriverInterface $db): string
    {
        return $db->getQueryBuilder()->build($this);
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
}
