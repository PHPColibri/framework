<?php
namespace Colibri\Tests\Database;

use Colibri\Config\Config;
use Colibri\Database\Db;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    /**
     * @throws \Colibri\Database\DbException
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     */
    public function testCreateDb_NoDefault()
    {
        // no database type
        Config::setBaseDir(__DIR__ . '/sample/config');

        $this->expectException('Colibri\Database\DbException');
        /* @noinspection PhpUndefinedMethodInspection */
        Db::setConfig(Config::database());
    }

    /**
     * @depends testCreateDb_NoDefault
     *
     * @throws \Colibri\Database\DbException
     * @throws \PHPUnit\Framework\Exception
     */
    public function testCreateDb_WrongDefault()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $config            = Config::database('connection');
        $config['default'] = '__mysql';
        $this->expectException('Colibri\Database\DbException');
        Db::setConfig($config);
    }

    /**
     * @depends testCreateDb_WrongDefault
     *
     * @throws \Colibri\Database\DbException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testCreateDb()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $config            = Config::database('connection');
        $config['default'] = 'mysql1';
        $this->expectException('Colibri\Database\DbException');
        Db::setConfig($config);
        $db = Db::connection();
        $this->assertInstanceOf('Colibri\Database\MySQL', $db);
    }
}
