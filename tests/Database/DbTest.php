<?php
namespace Colibri\Tests\Database;

use Colibri\Config\Config;
use Colibri\Database\Db;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    public function testCreateDb_NoDefault()
    {
        // no database type
        Config::setBaseDir(__DIR__ . '/sample/config');

        $this->expectException('Colibri\Database\DbException');
        Db::setConfig(Config::database());
    }

    /**
     * @depends testCreateDb_NoDefault
     */
    public function testCreateDb_WrongDefault()
    {
        $config            = Config::database('connection');
        $config['default'] = '__mysql';
        $this->expectException('Colibri\Database\DbException');
        Db::setConfig($config);
    }

    /**
     * @depends testCreateDb_WrongDefault
     */
    public function testCreateDb()
    {
        $config            = Config::database('connection');
        $config['default'] = 'mysql1';
        $this->expectException('Colibri\Database\DbException');
        Db::setConfig($config);
        $db = Db::connection();
        $this->assertInstanceOf('Colibri\Database\MySQL', $db);
    }
}
