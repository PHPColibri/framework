<?php

namespace Colibri\View;

use Colibri\Base\PropertyAccess;

/**
 * @property	string	filename
 */
class PhpTemplate extends PropertyAccess
{
	/**
	 * Path of template.
	 *
	 * @var string
	 */
	protected $_filename = null;

	/**
	 * Variables of template for tpl compile.
	 *
	 * @var array
	 */
	public $vars = array();

	/**
	 * @param string $filename filename
	 */
	public function	__construct($filename = null)
	{
		if (null === $filename) {
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
	public function	setVars(array $vars)
	{
		$this->vars = array_merge($this->vars, $vars);

		return $this;
	}

	/**
	 * Load template.
	 *
	 * @param null $filename
	 *
	 * @return $this
	 *
	 * @throws \Exception
	 */
	public function	load($filename = null)
	{
		if (null === $filename) {
			$filename = $this->_filename;
		}
		if (null === $filename) {
			throw new \Exception('Can`t load template: property \'filename\' not set.');
		}

		if (!file_exists($filename)) {
			throw new \Exception("File '$filename' does not exists.");
		}

		$this->_filename = $filename;

		return $this;
	}

	/**
	 * Compile template.
	 *
	 * @return string
	 */
	public function	compile()
	{
		foreach ($this->vars as $key => $value) {
			$$key = $value;
		}


		ob_start();

		include($this->_filename);
		$__strCompiled__ = ob_get_contents();

		ob_end_clean();

		return $__strCompiled__;
	}

}
