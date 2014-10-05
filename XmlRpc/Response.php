<?php
namespace Colibri\XmlRpc;

use Colibri\Config\Config;

require_once('xmlrpc.php');

/**
 * Description of XmlRpc\Response
 * 
 * @deprecated 
 *
 * @author		Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package		xTeam
 * @subpackage	a13FW
 * @version		2.0.0
 *
 * //@ e x c eption	5xx
 */
class Response
{
	public	$xml;

	public	function	__construct($retValue)
	{
		$resp=new \xmlrpcresp(\php_xmlrpc_encode($retValue));
		$appConfig = Config::get('application');
		$this->xml='<?xml version="1.0" encoding="'.$appConfig['encoding']."\" ?>\n".$resp->serialize();
	}
static
	public	function	fault($errCode,$errMessage)
	{
		$resp=new \xmlrpcresp(0, $errCode==0?-1:$errCode, $errMessage);
		$appConfig = Config::get('application');
		return '<?xml version="1.0" encoding="'.$appConfig['encoding']."\" ?>\n".$resp->serialize();
	}
static
	public	function	additionalFault($errCode,$addErrCode,$errMessage)
	{
		$resp=new \xmlrpcresp(0, $errCode==0?-1:$errCode, $addErrCode, $errMessage);
		$appConfig = Config::get('application');
		return '<?xml version="1.0" encoding="'.$appConfig['encoding']."\" ?>\n".$resp->serialize();
	}
}
