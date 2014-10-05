<?php
namespace Colibri\Application;

/**
 * Description of ResponseType
 *
 * @author		Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package		xTeam
 * @version		1.00
 */
class RequestType
{
	const	none					=0x00;

	const	getModuleView			=0x01;  //  0000 0001
	const	callModuleMethod		=0x02;  //  0000 0010
}
