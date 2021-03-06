<?php
namespace Colibri\View;

use Colibri\Pattern\Helper;
use Colibri\Util\Html;
use Colibri\Util\Str;

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
    /** @var string */
    protected static $canonical = '';
    /** @var array */
    protected static $meta = [];
    /** @var array */
    protected static $openGraph = [];
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

    /** @var array */
    protected static $userVars = [];

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
    public static function filename($value = null): ?string
    {
        return $value !== null ? static::$filename = $value : static::$filename;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public static function set(string $name, string $value)
    {
        static::$userVars[$name] = $value;
    }

    /**
     * Adds included css file from specified uri-$path.
     *
     * @param string $cssFilename
     * @param string $path
     */
    public static function addCss(string $cssFilename, $path = RES_CSS)
    {
        static::$css[] = $path . $cssFilename;
    }

    /**
     * Adds included js file from specified uri-$path.
     *
     * @param string $jsFilename
     * @param string $path
     */
    public static function addJs(string $jsFilename, $path = RES_JS)
    {
        static::$js[] = $path . $jsFilename;
    }

    /**
     * Adds specified $jsText as injected js code.
     *
     * @param string $jsText
     */
    public static function addJsText(string $jsText)
    {
        static::$jsText[] = $jsText;
    }

    /**
     * Adds specified $jsText as js code, that will be called on document.ready.
     * (that will be wrapped with $(document).ready(function(){ ...js-code... });).
     *
     * @param string $jsText
     */
    public static function addJsTextOnReady(string $jsText)
    {
        static::$jsTextOnReady .= $jsText . "\n";
    }

    /**
     * Adds included js page manager class & its call on document.ready.
     *
     * @param string $jsManagerName
     * @param string $path
     */
    public static function addJsMgr(string $jsManagerName, $path = RES_JS)
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
    public static function keywords($value = null): string
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
    public static function title($value = null): string
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
    public static function description($value = null): string
    {
        return $value !== null ? static::$description = $value : static::$description;
    }

    /**
     * Sets or gets layout page canonical url.
     *
     * @param string|null $url
     *
     * @return string
     */
    public static function canonicalUrl(string $url = null): string
    {
        if ($url !== null && ! Str::beginsWith($url, ['http://', 'https://'])) {
            throw new \InvalidArgumentException('Canonical url have to specified as full url');
        }

        return $url !== null ? static::$canonical = $url : static::$canonical;
    }

    /**
     * Sets or gets layout meta tag of $name with specified $content.
     *
     * @param string      $name
     * @param string|null $content
     *
     * @return mixed|null
     */
    protected static function meta(string $name, string $content = null): ?string
    {
        return $content !== null ? static::$meta[$name] = $content : (static::$meta[$name] ?? null);
    }

    /**
     * Sets or gets layout `meta` tag of `name='robots'` with specified $value.
     *
     * @param string|null $value
     *
     * @return string|null
     */
    public static function robots(string $value = null): ?string
    {
        return static::meta('robots', $value);
    }

    /**
     * Sets or gets layout OpenGraph property.
     *
     * @param string      $property
     * @param string|null $value
     *
     * @return string
     */
    public static function og(string $property, string $value = null): ?string
    {
        return $value !== null ? static::$openGraph[$property] = $value : (static::$openGraph[$property] ?? null);
    }

    /**
     * Deletes specified css filename.
     *
     * @param string $cssFilename
     * @param string $path
     */
    public static function delCss(string $cssFilename, $path = RES_CSS)
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
    public static function compile(string $content): string
    {
        $layoutTplVars = static::assembleTemplateVars($content);

        return static::compileWith($layoutTplVars);
    }

    /**
     * Goes through $texts array, wraps items with $template & concatenates into single string.
     *
     * @param array  $texts
     * @param string $template sprintf-like
     *
     * @return string
     */
    protected static function concatWrapped(array $texts, string $template): string
    {
        return array_reduce($texts, function ($concatenated, $textItem) use ($template) {
            return $concatenated . sprintf($template, $textItem);
        }, '');
    }

    /**
     * If $value is not Empty, escapes html & wraps with $template.
     *
     * @param string $value
     * @param string $template sprintf-like
     *
     * @return string
     */
    protected static function eWrap(string $value, string $template): string
    {
        return ! empty($value)
            ? sprintf($template, Html::e($value))
            : '';
    }

    /**
     * Prepare and assemble all variables for Layout PhpTemplate.
     *
     * @param string $content
     *
     * @return array
     */
    protected static function assembleTemplateVars(string $content): array
    {
        $layoutTplVars = [
            'content'       => $content,
            'keywords'      => static::eWrap(static::$keywords, "<meta name='keywords' content='%s' />\n"),
            'title'         => static::eWrap(static::$title, "<title>%s</title>\n"),
            'description'   => static::eWrap(static::$description, "<meta name='description' content='%s'/>\n"),
            'canonical-url' => static::eWrap(static::$canonical, "<link rel='canonical' href='%s'/>\n"),
            'meta'          => static::assembleMeta(),
            'opengraph'     => static::assembleOpenGraph(),
            'css'           => static::concatWrapped(static::$css,
                '<link   type="text/css" rel="stylesheet" href="%s"/>' . "\n\t"),
            'javascript'    => static::concatWrapped(static::$js,
                '<script type="text/javascript" src="%s"></script>' . "\n\t"),
        ];

        // make js init code for all js managers
        if (count(static::$jsMgr)) {
            static::addJsTextOnReady(static::concatWrapped(static::$jsMgr, "  new %s_mgr();\n"));
        }

        if (static::$jsTextOnReady != '') {
            static::addJsText("$(document).ready(function(){\n" . static::$jsTextOnReady . '});');
        }

        $layoutTplVars['javascript'] .=
            static::concatWrapped(static::$jsText, "<script type=\"text/javascript\">%s</script>\n");

        return $layoutTplVars + static::$userVars;
    }

    /**
     * Assemble meta tags.
     *
     * @return string
     */
    protected static function assembleMeta(): string
    {
        $meta = [];
        foreach (static::$meta as $key => $value) {
            $meta[] = "<meta name='$key' content='" . Html::e($value) . "' />";
        }

        return implode("\n\t", $meta) . "\n";
    }

    /**
     * Assemble OpenGraph properties into html meta tags.
     *
     * @return string
     */
    protected static function assembleOpenGraph(): string
    {
        $meta = [];
        foreach (static::$openGraph as $key => $value) {
            $meta[] = "<meta property='og:$key' content='" . Html::e($value) . "' />";
        }

        return implode("\n\t", $meta) . "\n";
    }

    /**
     * Compiles layout template with given variables.
     *
     * @param array $layoutTplVars
     *
     * @return string
     *
     * @throws \Exception
     */
    protected static function compileWith(array $layoutTplVars): string
    {
        if (static::$filename === null) {
            throw new \Exception('Layout template file name does not set: use Layout::filename().');
        }

        $tpl       = new PhpTemplate(VIEWS . static::$filename);
        $tpl->vars = $layoutTplVars;

        $compiledHtml = $tpl->compile();
        foreach ($layoutTplVars as $key => $value) {
            $compiledHtml = str_replace('{' . $key . '}', $value, $compiledHtml);
        }

        return $compiledHtml;
    }
}
