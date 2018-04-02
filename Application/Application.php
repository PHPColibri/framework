<?php
namespace Colibri\Application;

class Application
{

    /**
     * Application constructor.
     */
    public function __construct()
    {
    }

    /**
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Http\NotFoundException
     */
    public function run()
    {
        $engine = new Engine();
        $content = $engine->generateResponse();

        echo $content;
    }


}
