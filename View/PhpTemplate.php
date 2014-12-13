<?php
namespace Colibri\View;

use Colibri\Base\PropertyAccess;

/**
 *
 *
 * @author		Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package		xTeam
 * @subpackage	a13FW
 * @version		1.01
 *
 * @property	string	filename
 */
class	PhpTemplate extends PropertyAccess
{
	/**
	 * @var string	path/name of template
	 */
	protected	$_filename=null;
	/**
	 * @var array	variables of template for tpl compile
	 */
	public		$vars=array();


	/**
	 *
	 * @param	string		$filename	имя файла
	 */
	public		function	__construct($filename=null)
	{
		if ($filename===null)	return;

		$this->load($filename);
	}
	/**
	 * Sets or adds variables of template (merge)
	 * 
	 * @param array $vars
	 * @return static 
	 */
	public		function	setVars(array $vars)
	{
		$this->vars=array_merge($this->vars,$vars);
		return $this;
	}

	public		function	load($filename=null)
	{
		if ($filename===null)			$filename=$this->_filename;
		if ($filename===null)			throw new \Exception('Can`t load template: property \'filename\' not set.');
		if (!file_exists($filename))	throw new \Exception("file '$filename' does not exists.");
		$this->_filename=$filename;
		return $this;
	}
	/**
	 *
	 * @return string compiled template text
	 */
	public		function	compile()
	{
		$__strCompiled__='';
		
		foreach ($this->vars as $key=>$value)
			$$key=$value;
		ob_start();
		include($this->_filename);
		$__strCompiled__=ob_get_contents();
		ob_end_clean();

		return $__strCompiled__;
	}

}
