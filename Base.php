<?php
namespace Colibri\Controller;

use Colibri\Base\PropertyAccess;

/**
 * base controller
 *
 * @author		Александр Чибрикин aka alek13 <chibrikinalex@mail.ru>
 * @package		xTeam
 * @version		1.01.0
 *
 * @property string $response
 * @property string $division site division
 * @property string $module
 * @property string $method
 */
abstract
class Base extends PropertyAccess
{
	protected	$_response=null;
	protected	$_division=null;
	protected	$_module=null;
	protected	$_method=null;

	final
	public		function	__construct($division,$module,$method)
	{
		// $this->db=&$db;
		$this->_division=$division;
		$this->_module=$module;
		$this->_method=$method;

		$this->init();
	}

	protected	function	init()		{}

	public		function	setUp()		{}
						//  anyMethod() {}
	public		function	tearDown()	{}

	protected	function	done()		{}

	final
	public		function	__destruct()
	{
		$this->done();
	}
}
