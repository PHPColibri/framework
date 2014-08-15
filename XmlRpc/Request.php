<?php
namespace Colibri\XmlRpc;

require_once('xmlrpc.php');

/**
 * Description of XmlRpc\Request
 * 
 * @deprecated 
 *
 * @author		Александр Чибрикин aka alek13 <chibrikinalex@mail.ru>
 * @package		xTeam
 * @subpackage	a13FW
 * @version		1.01.0
 *
 * //@ e x c eption	4xx
 */
class Request
{
	public	$message=null;

	public	$methodName=null;
	public	$params=array();
	public	$paramsCount=0;

	public	$error='';
	public	$errno=0;

	public	function	__construct($xmlText)
	{
		if (($msg=$this->message=\php_xmlrpc_decode_xml($xmlText))===false)
			return !$this->setError('wrong XML-RPC format',1);
		
		$this->methodName	=$msg->methodname;
		$this->paramsCount	=$msg->getNumParams();
		$this->params		=\php_xmlrpc_decode($msg);
	}
	
	final
	protected	function	setError($error,$errno)
	{
		$this->error=$error;
		$this->errno=$errno;
		return true;
	}
}
