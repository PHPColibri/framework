<?php
namespace Colibri\Tests\Database;


use Colibri\Config\Config;
use Colibri\Database\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateDb_NoDefault()
    {
        // no database type
        Config::setBaseDir(__DIR__ . '/sample/config');

        $this->setExpectedException('Colibri\Database\DbException');
        Factory::createDb();
    }

    /**
     * @depends testCreateDb_NoDefault
     */
    public function testCreateDb_WrongDefault()
    {
        $config = Config::database('connection');
        $config['default'] = '__mysql';
        $this->setExpectedException('PHPUnit_Framework_Error_Notice');
        Factory::createDb($config);
    }

    /**
     * @depends testCreateDb_WrongDefault
     */
    public function testCreateDb()
    {
        $config = Config::database('connection');
        $config['default'] = 'mysql1';
        $this->setExpectedException('PHPUnit_Framework_Error_Warning');
        $db = Factory::createDb($config);
        $this->assertInstanceOf('Colibri\Database\MySQL', $db);
    }
}
