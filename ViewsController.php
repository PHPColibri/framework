<?php
namespace Colibri\Controller;

use Colibri\Controller\Base;
use Colibri\Log\Log;
use Colibri\View\Layout;
use Colibri\View\PhpTemplate;
use Colibri\Config\Config;

/**
 * Description of CModule
 *
 * @author        Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 *
 * @property bool $showProfilerInfoOnDebug ...
 * @property bool $showAppDevToolsOnDebug  ...
 *
 */
abstract
class ViewsController extends Base
{
    /**
     * @var string name of backbone template to use at
     */
    protected $backboneTplName = null;
    /**
     * @var PhpTemplate
     */
    protected $template = null;
    /**
     * @var    bool tells to core to use temlate or not
     */
    protected $useTemplate = true;
    /**
     * @var    bool
     */
    protected $useBackbone = true;
    /**
     * @var bool
     */
    protected $_showProfilerInfoOnDebug = true;
    /**
     * @var bool
     */
    protected $_showAppDevToolsOnDebug = true;

    private $divPath = null;
    private $divPrefix = null;
    private $divPostfix = null;



    protected function init()
    {
        parent::init();
        $this->divPath    = $this->_module . ($this->_division === '' ? '' : '/' . $this->_division);
        $this->divPrefix  = ($this->_division === '' ? '' : $this->_division . '_');
        $this->divPostfix = ($this->_division === '' ? '' : '.' . $this->_division);
    }


    public function setUp()
    {
        $this->template = new PhpTemplate();
    }

    /**
     */
    public function    tearDown()
    {
        if (!$this->useTemplate) {
            return;
        }

        if ($this->template->filename === null) {
            $tplPath = sprintf(MODULE_TEMPLATES, $this->divPath);
            $tplName = $tplPath . $this->divPrefix . $this->_module . '_' . $this->_method . '.php';
            if (file_exists($tplName)) {
                $this->template->load($tplName);
            }
        }


        if (!$this->useBackbone) {
            if ($this->template->filename === null) {
                throw new \Exception('template not loaded.');
            }
            $this->_response = $this->template->compile();
            return;
        }

        $this->setUpLayout();

        $this->_response = Layout::compile(
            $this->template->filename !== null
                ? $this->template->compile()
                : (DEBUG ? 'DEBUG Info: template not autoloaded.<br/> no such file: ' . str_replace(ROOT, '', $tplName) : '')
        );
    }

    private function setUpLayout()
    {
        if (!Layout::filename()) {
            Layout::filename('backbone' . $this->divPostfix . '.php');
        }

        // TODO: bring out into application config ??
        Layout::addJsText(
            'var MOD=\'' . MOD . '\';' . "\n" .
            'var IMG=\'' . RES_IMG . '\';' . "\n" .
            'var JS =\'' . RES_JS . '\';' . "\n" .
            'var CSS=\'' . RES_CSS . '\';' . "\n" .
            'var SWF=\'' . RES_SWF . '\';' . "\n" .
            'var PTO=\'' . RES_IMG_PTO . '\';'
        );
        Layout::addJsMgr('backbone' . ($this->_division === '' ? '' : '_' . $this->_division));
        Layout::addCss('backbone' . $this->divPostfix . '.css');

        $module_methodName = $this->_module . '_' . $this->_method;
        // add default js manager
        $jsPath   = $this->divPath . '/js/managers/';
        $jsName   = $this->divPrefix . $module_methodName;
        $fileName = MODULES . $jsPath . $jsName . '_mgr.js';
        if (file_exists($fileName)) {
            Layout::addJsMgr($jsName, MOD . $jsPath);
        }

        // add default css
        $cssPath  = $this->divPath . '/css/';
        $cssName  = $this->divPrefix . $module_methodName . '.css';
        $fileName = MODULES . $cssPath . $cssName;
        if (file_exists($fileName)) {
            Layout::addCss($cssName, MOD . $cssPath);
        }
    }
}
