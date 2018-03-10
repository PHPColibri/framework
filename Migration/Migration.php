<?php
namespace Colibri\Migration;

use Carbon\Carbon;
use Colibri\Database\Db;
use Colibri\Database\Query;
use Colibri\Pattern\Helper;
use Colibri\Util\Str;

abstract class Migration extends Helper
{
    protected static $createdAt;

    /**
     * @return string
     */
    final public static function hash(): string
    {
        return md5(static::class);
    }

    /**
     * @return string
     */
    final public static function name(): string
    {
        return Str::snake(Str::lastPart(static::class, '\\'), ' ');
    }

    /**
     * @return \Carbon\Carbon
     */
    final public static function createdAt(): Carbon
    {
        return new Carbon(static::$createdAt);
    }

    /**
     * @return \Carbon\Carbon|null
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \UnexpectedValueException
     */
    final public static function migratedAt()
    {
        $row = Db::connection()->query(Query::select()->from('migrations')->where([
            'hash' => static::hash(),
        ]))->fetch();

        return $row !== null
            ? new Carbon($row['migratedAt'])
            : null;
    }

    /**
     * @return void
     */
    abstract public static function up();

    /**
     * @return void
     */
    abstract public static function down();
}
