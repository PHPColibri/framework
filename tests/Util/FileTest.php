<?php
namespace Colibri\tests\Util;

use Colibri\tests\TestCase;
use Colibri\Util\File;

/**
 * Tests for File util.
 */
class FileTest extends TestCase
{
    /**
     * @covers \Colibri\Util\File::getMimeType
     *
     * @throws \Exception
     */
    public function testGetMimeType()
    {
        $mimeType = File::getMimeType(__DIR__ . '/FileTest.php');
        static::assertEquals('text/x-php', $mimeType);
    }
}
