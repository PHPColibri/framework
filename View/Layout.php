<?php
namespace Colibri\View;

/**
 * Class Layout
 */
class Layout
{
    /**
     * @var string name of layout template to use
     */
    private static $templateName = null;
    // vars:
    private static $description = '';
    private static $keywords = '';
    private static $title = '';
    private static $css = [];
    private static $js = [];
    private static $jsText = [];
    private static $jsTextOnReady = '';
    private static $jsMgr = [];

    /**
     * @return string
     */
    public static function getTemplateName()
    {
        return self::$templateName;
    }

    /**
     * @param string $templateName
     */
    public static function setTemplateName($templateName)
    {
        self::$templateName = $templateName;
    }

    /**
     * @param string $cssFilename
     * @param string $path
     */
    public static function addCss($cssFilename, $path = RES_CSS)
    {
        static::$css[] = $path . $cssFilename;
    }

    /**
     * @param string $jsFilename
     * @param string $path
     */
    public static function addJs($jsFilename, $path = RES_JS)
    {
        static::$js [] = $path . $jsFilename;
    }

    /**
     * @param strnig $jsText
     */
    public static function addJsText($jsText)
    {
        static::$jsText[] = $jsText;
    }

    /**
     * @param strnig $jsText
     */
    public static function addJsTextOnReady($jsText)
    {
        static::$jsTextOnReady .= $jsText . "\n";
    }

    /**
     * @param        $jsMgrName
     * @param string $path
     */
    public static function addJsMgr($jsMgrName, $path = RES_JS)
    {
        static::addJs($jsMgrName . '_mgr.js', $path);
        static::$jsMgr[] = $jsMgrName;
    }

    /**
     * @param strnig $value
     *
     * @return string
     */
    public static function keywords($value = null)
    {
        return $value !== null ? static::$keywords = $value : static::$keywords;
    }

    /**
     * @param strnig $value
     *
     * @return string
     */
    public static function title($value = null)
    {
        return $value !== null ? static::$title = $value : static::$title;
    }

    /**
     * @param strnig $value
     *
     * @return string
     */
    public static function description($value = null)
    {
        return $value !== null ? static::$description = $value : static::$description;
    }

    /**
     * @param strnig $cssFilename
     * @param string $path
     */
    public static function delCss($cssFilename, $path = RES_CSS)
    {
        $cnt = count(static::$css);
        for ($i = 0; $i < $cnt; $i++) {
            if (static::$css[$i] == $path . $cssFilename) {
                array_splice(static::$css, $i, 1);
                $cnt--;
            }
        }
    }

    /**
     * @param $content
     *
     * @return string
     * @throws \Exception
     */
    public static function compile($content)
    {
        $layoutTplVars = [];
        $layoutTplVars['content'] = $content;
        //TODO: special chars
        $layoutTplVars['keywords'] = !empty(static::$keywords) ? "<meta name='keywords' content='" . static::$keywords . "' />\n" : '';
        $layoutTplVars['title']    = !empty(static::$title) ? "<title>" . htmlspecialchars(static::$title) . "</title>\n" : '';
        //TODO: special chars
        $layoutTplVars['description'] = !empty(static::$description) ? "<meta name='description' content='" . static::$description . "' />\n" : '';
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
        $jsMgrsCnt = count(static::$jsMgr);
        if ($jsMgrsCnt > 0) {
            $jsMgrsText = '';
            for ($i = 0; $i < $jsMgrsCnt; $i++) {
                $jsMgrsText .= "\tnew " . static::$jsMgr[$i] . "_mgr();\n";
            }
            static::addJsTextOnReady($jsMgrsText);
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

        if (static::$templateName === null)
            throw new \Exception('Layout template file name does not set: use Layout::setTemplateName().');

        $tpl       = new PhpTemplate(TEMPLATES . static::$templateName);
        $tpl->vars = $layoutTplVars;

        //login error information - to be shown only once
        // @todo bring out
        if (isset($_SESSION['login_error'])) {
            $tpl->vars['login_error'] = $_SESSION['login_error'];
            unset($_SESSION['login_error']);
        }

        $compiledHtml = $tpl->compile();
        foreach ($layoutTplVars as $key => $value) {
            $compiledHtml = str_replace('{' . $key . '}', $value, $compiledHtml);
        }

        return $compiledHtml;
    }
}