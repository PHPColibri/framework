<?php
namespace Colibri\Database\Query;

/**
 * Class Aggregation.
 */
class Aggregation
{
    /** @var string */
    private $type;

    /** @var string */
    private $tableAlias;

    /** @var string */
    private $column;

    /** @var string */
    private $alias;

    /** @var bool */
    private $distinct = false;

    /**
     * Aggregation constructor.
     *
     * @param string $type
     * @param string $column
     * @param string $as
     * @param string $tableAlias
     */
    private function __construct(string $type, string $column, string $as = null, string $tableAlias = null)
    {
        $this->type       = $type;
        $this->column     = $column;
        $this->alias      = $as;
        $this->tableAlias = $tableAlias;
    }

    /**
     * @param string $tableAlias
     *
     * @return \Colibri\Database\Query\Aggregation
     */
    public function setTableAlias(string $tableAlias): self
    {
        $this->tableAlias = $tableAlias;

        return $this;
    }

    /**
     * @return \Colibri\Database\Query\Aggregation
     */
    public function distinct(): self
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $column   = $this->tableAlias ? $this->tableAlias . '.' . $this->column : $this->column;
        $alias    = $this->alias ? ' as ' . $this->alias : '';
        $distinct = $this->distinct ? 'distinct ' : '';

        $column = $distinct . $column;

        return "$this->type($column)$alias";
    }

    /**
     * @param string      $column
     * @param string|null $as
     * @param string|null $tableAlias
     *
     * @return \Colibri\Database\Query\Aggregation
     */
    public static function count(string $column, string $as = null, string $tableAlias = null): self
    {
        return new self(Aggregation\Type::COUNT, $column, $as, $tableAlias);
    }

    /**
     * @param string      $column
     * @param string|null $as
     * @param string|null $tableAlias
     *
     * @return \Colibri\Database\Query\Aggregation
     */
    public static function countDistinct(string $column, string $as = null, string $tableAlias = null): self
    {
        return (new self(Aggregation\Type::COUNT, $column, $as, $tableAlias))->distinct();
    }

    /**
     * @param string $column
     *
     * @return \Colibri\Database\Query\Aggregation
     */
    public static function max(string $column)
    {
        return new self(Aggregation\Type::MAX, $column);
    }

    /**
     * @param string $column
     *
     * @return \Colibri\Database\Query\Aggregation
     */
    public static function min(string $column)
    {
        return new self(Aggregation\Type::MIN, $column);
    }

    /**
     * @param string $column
     *
     * @return \Colibri\Database\Query\Aggregation
     */
    public static function avg(string $column)
    {
        return new self(Aggregation\Type::AVG, $column);
    }
}
