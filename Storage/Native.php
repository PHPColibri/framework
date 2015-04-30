<?php
namespace Colibri\Session\Storage;

use Colibri\Pattern\Singleton;
use Colibri\Util\Arr;

/**
 * Class Native
 *
 * @package Colibri\Session\Storage
 */
class Native extends Singleton implements StorageInterface
{
	/**
	 */
	protected function __construct()
	{
		session_start();
	}

	/**
	 * @param string $dottedKey
	 *
	 * @return mixed
	 */
	public function has($dottedKey)
	{
		return Arr::get($dottedKey, '~no~value~in~storage~') === '~no~value~in~storage~';
	}

	/**
	 * @param string $dottedKey
	 *
	 * @return mixed
	 */
	public function get($dottedKey)
	{
		return Arr::get($_SESSION, $dottedKey);
	}

	/**
	 * @param string $dottedKey
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public function set($dottedKey, $value)
	{
		return Arr::set($_SESSION, $dottedKey, $value);
	}

	/**
	 * @param string $dottedKey
	 *
	 * @return mixed|null
	 */
	public function remove($dottedKey)
	{
		return Arr::remove($_SESSION, $dottedKey);
	}
}