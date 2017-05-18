<?php
namespace Colibri\Session\Storage;

/**
 * Interface StorageInterface for Session Storage
 */
interface StorageInterface
{
	/**
	 * @param string $dottedKey
	 *
	 * @return mixed
	 */
	public function has($dottedKey);
	/**
	 * @param string $dottedKey
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get($dottedKey, $default = null);
	/**
	 * @param string $dottedKey
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public function set($dottedKey, $value);

	/**
	 * @param string $dottedKey
	 *
	 * @return mixed|null returns removed value or null if key not found
	 */
	public function remove($dottedKey);
}