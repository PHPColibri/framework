<?php
namespace Colibri\Migration\Console\Command;

use Colibri\Console\Command as ColibriCommand;
use Colibri\Migration\Console\Command;
use Colibri\View\PhpTemplate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Create extends Command
{

    /**
     * @return \Colibri\Console\Command
     */
    protected function definition(): ColibriCommand
    {
        global $argv;

        return $this
            ->setDescription('Creates migration')
            ->setHelp(/** @lang text */
                '
Creates migration class by provided <info>name</info>.

<options=bold>Example:</>
  '
 . $argv[0] . ' create AddUserRatingColumn -c
')
            ->addArgument('class-name', InputArgument::REQUIRED, 'name of the class to be created')
            ->addOption('use-db', 'd', InputOption::VALUE_NONE)
            ->addOption('query', 'w', InputOption::VALUE_NONE)
            ->addOption('add-column', 'c', InputOption::VALUE_NONE)
            ;
    }

    /**
     * @return int
     */
    protected function go(): int
    {
        $this->saveCode($this->renderCode());

        return 0;
    }

    /**
     * @return string
     */
    private function renderCode(): string
    {
        $query = $this->option('query') || $this->option('add-column');

        /** @noinspection SqlResolve */
        $upQuery = $this->option('add-column')
            ? 'alter table t1 add new_column int default 7 not null comment \'comment for column\''
            : '';
        /** @noinspection SqlResolve */
        $downQuery = $this->option('add-column')
            ? 'alter table t1 drop column new_column'
            : '';

        return (new PhpTemplate(\realpath(__DIR__ . '/../../Migration/Stub.php')))
            ->setVars([
                'namespace' => $this->config('namespace'),
                'name'      => $this->argument('class-name'),
                'useDb'     => $this->option('use-db'),
                'query'     => $query,
                'upQuery'   => $upQuery,
                'downQuery' => $downQuery,
            ])
            ->compile()
            ;
    }

    /**
     * @param string $code
     */
    private function saveCode(string $code)
    {
        $folder = \realpath($this->config('folder')) . '/';
        $file   = $folder . $this->argument('class-name') . '.php';
        if (file_exists($file)) {
            throw new \LogicException('File already exists');
        }
        file_put_contents($file, $code);
    }
}
