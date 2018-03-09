<?php

use Carbon\Carbon;

/** @var bool $useDb */

/** @var string $name */
/** @var string|bool $query */
/** @var string $upQuery */
/** @var string $downQuery */
echo "<?php\n"
?>
namespace <?= $namespace ?>;

use Carbon\Carbon;
use Colibri\Migration\Migration;
<?php if ($useDb || $query): ?>
use Colibri\Database\Db;
<?php endif; ?>

class <?= $name ?> extends Migration
{
    /**
     * @var string
     */
    protected static $createdAt = '<?= Carbon::now()->toDateTimeString() ?>';

    public static function up()
    {
<?php if ($query): ?>
        Db::connection()->getConnection()
            ->query('<?= addslashes($upQuery) ?>');
<?php else: ?>
        // TODO: Implement up() method.
<?php endif; ?>
    }

    public static function down()
    {
<?php if ($query): ?>
        Db::connection()->getConnection()
            ->query('<?= addslashes($downQuery) ?>');
<?php else: ?>
        // TODO: Implement down() method.
<?php endif; ?>
    }
}
