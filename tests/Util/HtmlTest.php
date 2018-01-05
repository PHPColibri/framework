<?php
namespace Colibri\tests\Util;

use Colibri\Util\Html;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Colibri\Util\Html
 */
class HtmlTest extends TestCase
{
    /**
     * @covers ::e
     * @dataProvider eProvider
     *
     * @param string $string
     * @param string $expected
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function testE($string, $expected)
    {
        static::assertEquals($expected, Html::e($string));
    }

    /**
     * @return array
     */
    public function eProvider()
    {
        return [
            ['<a href=\'http://phpcolibri.com/\'>Official Site</a>', '&lt;a href=&apos;http://phpcolibri.com/&apos;&gt;Official Site&lt;/a&gt;'],
            ['<div class="row">Test</div>', '&lt;div class=&quot;row&quot;&gt;Test&lt;/div&gt;'],
            ['Markdown code block: <code>```</code>', 'Markdown code block: &lt;code&gt;```&lt;/code&gt;'],
        ];
    }
}
