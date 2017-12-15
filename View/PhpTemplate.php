<?php
namespace Colibri\View;

/**
 * Simple Template engine based on php.
 */
class PhpTemplate
{
    /**
     * @var string path/name of template
     */
    protected $filename = null;

    /**
     * @var array variables of template for compile
     */
    public $vars = [];

    /**
     * @param string $filename имя файла
     *
     * @throws \Exception file does not exists
     */
    public function __construct($filename = null)
    {
        if ($filename === null) {
            return;
        }

        $this->load($filename);
    }

    /**
     * Sets or adds variables of template (merge).
     *
     * @param array $vars
     *
     * @return static
     */
    public function setVars(array $vars)
    {
        $this->vars = array_merge($this->vars, $vars);

        return $this;
    }

    /**
     * Loads the $filename in memory.
     *
     * @param string $filename
     *
     * @return $this
     *
     * @throws \Exception filename not set or file does not exists
     */
    public function load($filename = null)
    {
        if ($filename === null) {
            $filename = $this->filename;
        }
        if ($filename === null) {
            throw new \Exception('Can`t load template: property \'filename\' not set.');
        }
        if ( ! file_exists($filename)) {
            throw new \Exception("file '$filename' does not exists.");
        }
        $this->filename = $filename;

        return $this;
    }

    /**
     * Compiles template.
     *
     * @return string compiled template text
     */
    public function compile()
    {
        extract($this->vars);

        ob_start();
        /** @noinspection PhpIncludeInspection */
        include $this->filename;
        $__strCompiled__ = ob_get_contents();
        ob_end_clean();

        return $__strCompiled__;
    }

    /**
     * Returns template filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }
}
