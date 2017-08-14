<?php
namespace Colibri\View;

use Colibri\Pattern\Helper;

/**
 * Class Layout.
 */
class Layout extends Helper
{
    /**
     * @var string name of layout template to use
     */
    private static $filename = null;

    // Variables that injected into layout:
    /** @var string */
    private static $description = '';
    /** @var string */
    private static $keywords = '';
    /** @var string */
    private static $title = '';
    /** @var array */
    private static $css = [];
    /** @var array */
    private static $js = [];
    /** @var array */
    private static $jsText = [];
    /** @var string */
    private static $jsTextOnReady = '';
    /** @var array */
    private static $jsMgr = [];

    /**
     * Cleans up all layout variables & resets filename if specified.
     *
     * @param string $filename
     */
    public static function clean($filename = null)
    {
        static::filename($filename);

        static::$description   =
        static::$keywords      =
        static::$title         =
        static::$jsTextOnReady = '';
        static::$css           =
        static::$js            =
        static::$jsText        =
        static::$jsMgr         = [];
    }

    /**
     * Sets or gets filename of layout.
     *
     * @param string $value
     *
     * @return string
     */
    public static function filename($value = null)
    {
        return $value !== null ? static::$filename = $value : static::$filename;
    }

    /**
     * Adds included css file from specified uri-$path.
     *
     * @param string $cssFilename
     * @param string $path
     */
    public static function addCss($cssFilename, $path = RES_CSS)
    {
        static::$css[] = $path . $cssFilename;
    }

    /**
     * Adds included js file from specified uri-$path.
     *
     * @param string $jsFilename
     * @param string $path
     */
    public static function addJs($jsFilename, $path = RES_JS)
    {
        static::$js[] = $path . $jsFilename;
    }

    /**
     * Adds specified $jsText as injected js code.
     *
     * @param string $jsText
     */
    public static function addJsText($jsText)
    {
        static::$jsText[] = $jsText;
    }

    /**
     * Adds specified $jsText as js code, that will be called on document.ready.
     * (that will be wrapped with $(document).ready(function(){ ...js-code... });).
     *
     * @param string $jsText
     */
    public static function addJsTextOnReady($jsText)
    {
        static::$jsTextOnReady .= $jsText . "\n";
    }

    /**
     * Adds included js page manager class & its call on document.ready.
     *
     * @param string $jsManagerName
     * @param string $path
     */
    public static function addJsMgr($jsManagerName, $path = RES_JS)
    {
        static::addJs($jsManagerName . '_mgr.js', $path);
        static::$jsMgr[] = $jsManagerName;
    }

    /**
     * Sets or gets layout page keywords to be injected into meta tag.
     *
     * @param string $value
     *
     * @return string
     */
    public static function keywords($value = null)
    {
        return $value !== null ? static::$keywords = $value : static::$keywords;
    }

    /**
     * Sets or gets layout page title.
     *
     * @param string $value
     *
     * @return string
     */
    public static function title($value = null)
    {
        return $value !== null ? static::$title = $value : static::$title;
    }

    /**
     * Sets or gets layout page description.
     *
     * @param string $value
     *
     * @return string
     */
    public static function description($value = null)
    {
        return $value !== null ? static::$description = $value : static::$description;
    }

    /**
     * Deletes specified css filename.
     *
     * @param string $cssFilename
     * @param string $path
     */
    public static function delCss($cssFilename, $path = RES_CSS)
    {
        static::$css = array_diff(static::$css, [$path . $cssFilename]);
    }

    /**
     * Compiles the layout. Inject layout variables & given $content.
     *
     * @param string $content
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function compile($content)
    {
        $layoutTplVars            = [];
        $layoutTplVars['content'] = $content;
        //TODO: special chars
        $layoutTplVars['keywords'] = ! empty(static::$keywords) ? "<meta name='keywords' content='" . static::$keywords . "' />\n" : '';
        $layoutTplVars['title']    = ! empty(static::$title) ? '<title>' . htmlspecialchars(static::$title) . "</title>\n" : '';
        //TODO: special chars
        $layoutTplVars['description'] = ! empty(static::$description) ? "<meta name='description' content='" . static::$description . "' />\n" : '';
        $layoutTplVars['javascript']  = '';
        $layoutTplVars['css']         = '';

        $cssCnt = count(static::$css);
        if ($cssCnt > 0) {
            for ($i = 0; $i < $cssCnt; $i++) {
                $layoutTplVars['css'] .=
                    '<link   type="text/css" rel="stylesheet" href="' . static::$css[$i] . '"/>' . "\n";
            }
        }

        $jsCnt = count(static::$js);
        if ($jsCnt > 0) {
            for ($i = 0; $i < $jsCnt; $i++) {
                $layoutTplVars['javascript'] .=
                    '<script type="text/javascript" src="' . static::$js[$i] . '"></script>' . "\n";
            }
        }

        // make js init code for all js managers
        $jsManagersCount = count(static::$jsMgr);
        if ($jsManagersCount > 0) {
            $jsManagersText = '';
            for ($i = 0; $i < $jsManagersCount; $i++) {
                $jsManagersText .= '  new ' . static::$jsMgr[$i] . "_mgr();\n";
            }
            static::addJsTextOnReady($jsManagersText);
        }

        if (static::$jsTextOnReady != '') {
            static::addJsText("$(document).ready(function(){\n" . static::$jsTextOnReady . '});');
        }

        $jsTextCnt = count(static::$jsText);
        if ($jsTextCnt > 0) {
            for ($i = 0; $i < $jsTextCnt; $i++) {
                $layoutTplVars['javascript'] .=
                    "<script type=\"text/javascript\">\n" . static::$jsText[$i] . "\n</script>\n";
            }
        }

        if (static::$filename === null) {
            throw new \Exception('Layout template file name does not set: use Layout::setFilename().');
        }

        $tpl       = new PhpTemplate(TEMPLATES . static::$filename);
        $tpl->vars = $layoutTplVars;

        $compiledHtml = $tpl->compile();
        foreach ($layoutTplVars as $key => $value) {
            $compiledHtml = str_replace('{' . $key . '}', $value, $compiledHtml);
        }

        return $compiledHtml;
    }
}
