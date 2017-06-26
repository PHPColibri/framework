<?php
namespace Colibri\Controller;

use Colibri\Base\PropertyAccess;

/**
 * Base controller class.
 *
 * @property string $response
 * @property string $division site division
 * @property string $module
 * @property string $method
 */
abstract class Base extends PropertyAccess
{
    /**
     * @var string
     */
    protected $_response = null;
    /**
     * @var string
     */
    protected $_division = null;
    /**
     * @var string
     */
    protected $_module = null;
    /**
     * @var string
     */
    protected $_method = null;

    /**
     * Base constructor.
     *
     * @param string $division
     * @param string $module
     * @param string $method
     */
    final public function __construct($division, $module, $method)
    {
        $this->_division = $division;
        $this->_module   = $module;
        $this->_method   = $method;

        $this->init();
    }

    /**
     * Custom initialize for controller (calls after ::__construct()).
     */
    protected function init()
    {
    }

    /**
     * Custom setups before controller-method called (calls each time before any ::$method() called).
     */
    public function setUp()
    {
    }

    /**
     * Custom cleanups after controller-method called (calls each time after any ::$method() called).
     */
    public function tearDown()
    {
    }

    /**
     * Custom utilize/cleanups for whole controller (calls in ::__destruct()).
     */
    protected function done()
    {
    }

    /**
     * Base destructor.
     */
    final public function __destruct()
    {
        $this->done();
    }
}
