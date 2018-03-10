<?php
namespace Colibri\Migration\Migration;

use Colibri\Base\DynamicCollection;
use Colibri\Migration\Model;
use Colibri\Util\Arr;
use Colibri\Util\Directory;
use Colibri\Util\Str;

class Collection extends DynamicCollection
{
    private $folder;
    private $namespace;
    private $thatNotMigrated = false;
    private $onlyMigrated    = false;
    private $onlyOne         = false;
    private $withHash;
    private $last;

    /**
     * Collection constructor.
     *
     * @param $folder
     * @param $namespace
     */
    public function __construct($folder, $namespace)
    {
        $this->folder    = $folder;
        $this->namespace = $namespace;
    }

    /**
     * @return void
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \UnexpectedValueException
     */
    protected function fillItems()
    {
        $migrations = [];
        foreach (Directory::iterator($this->folder) as $file) {
            $class = $this->namespace . '\\' . Str::cut($file->getFilename(), '.php');
            $model = Model::fromClass($class);
            if ($this->withHash !== null && $model->hash !== $this->withHash) {
                continue;
            }
            if ($this->thatNotMigrated && $model->migrated()) {
                continue;
            }
            if ($this->onlyMigrated && ! $model->migrated()) {
                continue;
            }
            $migrations[] = $model;
        }

        $migrations = $this->sort($migrations);

        $this->items = $this->onlyOne
            ? count($migrations) ? [$migrations[0]] : []
            : ($this->last === null
                ? $migrations
                : Arr::last($migrations, $this->last)
            )
        ;
    }

    /**
     * @return \Colibri\Migration\Migration\Collection|Model[]
     */
    public function thatNotMigrated()
    {
        $this->thatNotMigrated = true;

        return $this;
    }

    /**
     * @return \Colibri\Migration\Migration\Collection|Model[]
     */
    public function onlyMigrated()
    {
        $this->onlyMigrated = true;

        return $this;
    }

    /**
     * @param bool $one
     *
     * @return \Colibri\Migration\Migration\Collection|Model[]
     */
    public function one($one = true)
    {
        $this->onlyOne = $one;

        return $this;
    }

    /**
     * @param int $count
     *
     * @return \Colibri\Migration\Migration\Collection|Model[]
     */
    public function last(int $count)
    {
        $this->last = $count;

        return $this;
    }

    /**
     * @param string $hash
     *
     * @return \Colibri\Migration\Migration\Collection|Model[]
     */
    public function withHash(string $hash = null)
    {
        $this->withHash = $hash;

        return $this;
    }

    /**
     * @param array $migrations
     *
     * @return array
     */
    private function sort(array $migrations): array
    {
        $sorted = usort($migrations, function (Model $m1, Model $m2) {
            return $m1->createdAt->timestamp - $m2->createdAt->timestamp;
        });
        if ( ! $sorted) {
            throw new \LogicException('Can`t sort migrations');
        }

        return $migrations;
    }
}
