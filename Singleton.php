<?php
namespace Colibri\Pattern;

/**
 * Represent a singlton pattern:
 *   not public __construct & __clone,
 *   implement ::getInstance()
 * 
 * @author Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 */
abstract class Singleton extends Helper
{
	static protected $instance = null;

	abstract protected function __construct();
	private function __clone() {}
	private function __wakeup() {}

	/**
	 * @return static
	 */
	static public function getInstance()
	{
		return static::$instance === null
			? new static()
			: static::$instance
		;
	}
}
