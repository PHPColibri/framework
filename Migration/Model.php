<?php
namespace Colibri\Migration;

use Colibri\Database\Db;
use Colibri\Database\Query;

final class Model
{
    /** @var string|\Colibri\Migration\Migration */
    private $class;
    public $hash;
    public $name;
    public $description;
    public $createdAt;
    public $migratedAt;

    /**
     * @param string $class
     */
    private function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @param $class
     *
     * @return \Colibri\Migration\Model
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \UnexpectedValueException
     */
    public static function fromClass(string $class): self
    {
        /** @var \Colibri\Migration\Migration $class */

        $migration             = new self($class);
        $migration->hash       = $class::hash();
        $migration->name       = $class::name();
        $migration->createdAt  = $class::createdAt();
        $migration->migratedAt = $class::migratedAt();

        return $migration;
    }

//    /**
//     * @return string
//     */
//    public function getClass(): string
//    {
//        return $this->class;
//    }

    /**
     * @return bool
     */
    public function migrated(): bool
    {
        return $this->migratedAt !== null;
    }

    /**
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \UnexpectedValueException
     */
    public function run()
    {
        $this->class::up();
        Db::connection()->query(Query::insert()->into('migrations')->set([
            'hash' => $this->hash
        ]));
    }
}
