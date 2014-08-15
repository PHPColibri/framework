<?php
namespace Colibri\Application;

/**
 * Description of ResponseType
 *
 * @author		Александр Чибрикин aka alek13 <chibrikinalex@mail.ru>
 * @package		xTeam
 * @version		1.00
 */
class ResponseType
{
	const	none	=0x00;

	const	html	=0x01;  //  0000 0001
	const	xml		=0x02;  //  0000 0010
	const	rpc		=0x06;  //  0000 0110
}
