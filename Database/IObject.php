<?php
namespace Colibri\Database;

/**
 *
 *
 * @author		Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package		xTeam
 * @subpackage	a13FW
 * @category	interfaces
 * @version		1.00
 */
interface	IObject
{
		public	function	__get($propertyName);
		public	function	create();
		public	function	delete();
		public	function	save(array $attributes=null);
		public	function	load($id=null);
		public	function	reload();
}
