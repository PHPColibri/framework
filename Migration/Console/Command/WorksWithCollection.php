<?php
namespace Colibri\Migration\Console\Command;

use Colibri\Migration\Migration;

trait WorksWithCollection
{
    abstract protected function config(string $option);

    /**
     * @return Migration\Collection|\Colibri\Migration\Model[]
     */
    protected function migrations(): Migration\Collection
    {
        $folder     = $this->config('folder');
        $namespace  = $this->config('namespace');

        return new Migration\Collection($folder, $namespace);
    }
}
