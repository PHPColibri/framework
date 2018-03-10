<?php
namespace Colibri\Controller;

use Colibri\Config\Config;
use Colibri\View\Layout;
use Colibri\View\PhpTemplate;

/**
 * Base Controller for Views.
 * Extends from this class & place code for actions methods.
 * Corresponding template wil be used automatically.
 *
 * @property bool $showProfilerInfoOnDebug ...
 * @property bool $showAppDevToolsOnDebug  ...
 */
abstract class ViewsController extends Base
{
    /**
     * @var PhpTemplate
     */
    protected $template = null;
    /**
     * @var bool tells to core to use template or not
     */
    protected $useTemplate = true;
    /**
     * @var bool
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

    /**
     * @var string
     */
    private $divisionPath = null;
    /**
     * @var string
     */
    private $divisionPrefix = null;
    /**
     * @var string
     */
    private $divisionPostfix = null;

    /**
     * Initialize private $divisionPath, $divisionPrefix, $divisionPostfix.
     */
    protected function init()
    {
        parent::init();
        $this->divisionPath    = $this->_module . '/' . Config::divisions($this->_division);
        $this->divisionPrefix  = ($this->_division === '' ? '' : $this->_division . '_');
        $this->divisionPostfix = ($this->_division === '' ? '' : '.' . $this->_division);
    }

    /**
     * Initialize template for called method.
     */
    public function setUp()
    {
        $this->template = new PhpTemplate();
    }

    /**
     * Sets template variables.
     *
     * @param array $variables
     */
    protected function view(array $variables)
    {
        $this->template->setVars($variables);
    }

    /**
     * @return $this
     */
    protected function withoutLayout()
    {
        $this->useBackbone = false;

        return $this;
    }

    /**
     * Compile template. Prepare ::$response.
     *
     * @throws \Exception
     */
    public function tearDown()
    {
        if ( ! $this->useTemplate) {
            return;
        }

        if ($this->template->getFilename() === null) {
            $tplPath = sprintf(MODULE_TEMPLATES, $this->divisionPath);
            $tplName = $tplPath . $this->_method . '.php';
            if (file_exists($tplName)) {
                $this->template->load($tplName);
            }
        }

        if ( ! $this->useBackbone) {
            if ($this->template->getFilename() === null) {
                throw new \Exception('template not loaded.');
            }
            $this->_response = $this->template->compile();

            return;
        }

        $this->setUpLayout();

        $this->_response = Layout::compile(
            $this->template->getFilename() !== null
                ? $this->template->compile()
                : (DEBUG ? 'DEBUG Info: template not autoloaded.<br/> no such file: ' . str_replace(ROOT, '', $tplName) : '')
        );
    }

    /**
     * Prepare layout with automatically added consts, js, js-mgr, css.
     */
    private function setUpLayout()
    {
        if (Layout::filename()) {
            return;
        }

        Layout::filename('backbone' . $this->divisionPostfix . '.php');

        Layout::addJsMgr('backbone' . ($this->_division === '' ? '' : '_' . $this->_division));
        Layout::addCss('backbone' . $this->divisionPostfix . '.css');

        $fileBaseName = $this->_module . '_' . $this->_method;
        // add default js manager
        $jsPath   = $this->divisionPath . '/js/managers/';
        $jsName   = $this->divisionPrefix . $fileBaseName;
        $fileName = MODULES . $jsPath . $jsName . '_mgr.js';
        if (file_exists($fileName)) {
            Layout::addJsMgr($jsName, MOD . $jsPath);
        }

        // add default css
        $cssPath  = $this->divisionPath . '/css/';
        $cssName  = $this->divisionPrefix . $fileBaseName . '.css';
        $fileName = MODULES . $cssPath . $cssName;
        if (file_exists($fileName)) {
            Layout::addCss($cssName, MOD . $cssPath);
        }
    }
}
