<?php
namespace Colibri\Tests\Util;

use Colibri\Util\File;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers       \Colibri\Util\File::getMimeType
     */
    public function testGetMimeType()
    {
        $mimeType = File::getMimeType(__DIR__ . '/FileTest.php');
        $this->assertEquals('text/x-php', $mimeType);
    }
}
