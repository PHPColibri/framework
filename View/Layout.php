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
    protected static $filename = null;

    // Variables that injected into layout:
    /** @var string */
    protected static $description = '';
    /** @var string */
    protected static $keywords = '';
    /** @var string */
    protected static $title = '';
    /** @var array */
    protected static $css = [];
    /** @var array */
    protected static $js = [];
    /** @var array */
    protected static $jsText = [];
    /** @var string */
    protected static $jsTextOnReady = '';
    /** @var array */
    protected static $jsMgr = [];

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
        $layoutTplVars                = [];
        $layoutTplVars['content']     = $content;
        $layoutTplVars['keywords']    = ! empty(static::$keywords)
            ? "<meta name='keywords' content='" . htmlspecialchars(static::$keywords) . "' />\n"
            : '';
        $layoutTplVars['title']       = ! empty(static::$title)
            ? '<title>' . htmlspecialchars(static::$title) . "</title>\n"
            : '';
        $layoutTplVars['description'] = ! empty(static::$description)
            ? "<meta name='description' content='" . htmlspecialchars(static::$description) . "' />\n"
            : '';
        $layoutTplVars['css']         =
            static::concatWrapped(static::$css, '<link   type="text/css" rel="stylesheet" href="%s"/>' . "\n");
        $layoutTplVars['javascript']  =
            static::concatWrapped(static::$js, '<script type="text/javascript" src="%s"></script>' . "\n");

        // make js init code for all js managers
        if (count(static::$jsMgr)) {
            static::addJsTextOnReady(static::concatWrapped(static::$jsMgr, "  new %s_mgr();\n"));
        }

        if (static::$jsTextOnReady != '') {
            static::addJsText("$(document).ready(function(){\n" . static::$jsTextOnReady . '});');
        }

        $layoutTplVars['javascript'] .=
            static::concatWrapped(static::$jsText, "<script type=\"text/javascript\">%s</script>\n");

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

    /**
     * @param array  $texts
     * @param string $tpl
     *
     * @return string
     */
    protected static function concatWrapped(array $texts, $tpl)
    {
        return array_reduce($texts, function ($concatenated, $textItem) use ($tpl) {
            return $concatenated . sprintf($tpl, $textItem);
        }, '');
    }
}
