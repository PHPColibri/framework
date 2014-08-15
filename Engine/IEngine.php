<?php
namespace Colibri\Application\Engine;
/**
 * IEngine
 *
 * @author		Александр Чибрикин aka alek13 <chibrikinalex@mail.ru>
 * @version		1.0.0
 * @package		xTeam
 * @subpackage	a13FW
 */
interface IEngine
{
	public		function	__construct();
	public		function	generateResponse();
}
