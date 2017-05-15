<?php
namespace Colibri\Application;

use Colibri\Log\Log;
use Colibri\Config\Config;
use Colibri\XmlRpc\Request as XmlRpcRequest;
use Colibri\XmlRpc\Response as XmlRpcResponse;
use LogicException;

/**
 * Description of CModuleEngine
 *
 * @author		Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package		xTeam
 * @version		1.10.3
 * @exception	12xx
 */
class Engine extends Engine\Base
{
	const		rpcquery_POSTvarName='xquery';

	protected	$_responser=null;
	protected	$_domainPrefix=null;
	private		$_division=null;
	private		$_module=null;
	private		$_method=null;
	private		$_params=array();

	private		$_requestType	=RequestType ::none;
	protected 	$_responseType	=ResponseType::none;
	protected	$_showProfilerInfoOnDebug=true;
	protected	$_showAppDevToolsOnDebug =true;

    /**
     */
    private static function setUpErrorHandling()
    {
        error_reporting(0xffff);
        ini_set('display_errors', DEBUG);
        set_error_handler('\Colibri\Application\Engine::errorHandler', 0xffff);
        set_exception_handler('\Colibri\Application\Engine::exceptionHandler');
    }


    /**
	 * @exception	120x
	 */
	protected	function	initialize()
	{
		$appConfig = Config::get('application');
		mb_internal_encoding($appConfig['encoding']);
		date_default_timezone_set($appConfig['timezone']);
		umask($appConfig['umask']);
        define('DEBUG', $appConfig['debug']);
        self::setUpErrorHandling();


        $this->_domainPrefix=$this->getDomainPrefix();

		new \API($this);// initialize API

		// identifying request type & parse params
		if (isset($_POST[self::rpcquery_POSTvarName]))
		{
			$this->_requestType=RequestType::callModuleMethod;
			$GLOBALS['xmlrpc_internalencoding']=$appConfig['encoding'];
			$this->parseRpcQuery($_POST[self::rpcquery_POSTvarName]);
			unset($_POST[self::rpcquery_POSTvarName]);
			return;
		}

		//TODO: refactor to config. This is route by SEO requirements
		$requestedUri=$this->getRequestedUri();
		$routes = Config::routing('rewrite');
		foreach ($routes as $route)
		{
			$pattern	=$route['pattern'];
			$replacement=$route['replacement'];
			$requestedUri=preg_replace($pattern,$replacement,$requestedUri);

			/*
			if (isset($route['requestType']))
				$this->_requestType=$route['requestType'];
			if ($this->_requestType==RequestType::callModuleMethod)
			{
				$GLOBALS['xmlrpc_internalencoding']=$appConfig['encoding'];
				if (isset($route['parseRpcQuery']))
				{
					eval('$rpcQuery='.$route['parseRpcQuery']);
					$this->parseRpcQuery($rpcQuery);
				}
				else
				{
					if (!isset($route['module']) || !isset($route['method']))
			            throw new LogicException('Wrong routing format');
					$this->_module=$route['module'];
					$this->_method=$route['method'];
					$this->_params=isset($route['params'])?$route['params']:array();
				}
			}
			*/
			if (isset($route['last']))
				break;
		}

		$this->parseRequestedFile($requestedUri);
		$this->_requestType=RequestType::getModuleView;
	}
	/**
	 * @return	string		returns reqwested file name with path: for "http://example.com/some/dir/somefile.php?arg1=val1&arg2=val2" returns "/some/dir/somefile.php"
	 */
	private		function	getRequestedUri()
	{
		$questPos=strpos($_SERVER['REQUEST_URI'],'?');
		if ($questPos===false)
			return $_SERVER['REQUEST_URI'];

		return substr($_SERVER['REQUEST_URI'],0,$questPos);
	}
	/**
	 * @return string returns prefix of domain: for "sub.domain.example.com" and const $conf['domain']=="example.com", returns "sub.domain"
	 */
	private		function	getDomainPrefix()
	{
		$appConfig = Config::get('application');
		$prefix=str_replace($appConfig['domain'],'',$_SERVER['HTTP_HOST']);
		$pLen=strlen($prefix);
		if ($pLen)
			$prefix=substr($prefix,0,$pLen-1);

		return $prefix;
	}
	/**
	 * @param	string	$file	requested file name
	 */
	protected	function	parseRequestedFile($file)
	{
		$appConfig = Config::get('application');

		$dotPos=strpos($file,'.');
		if ($dotPos!==false)	$file=substr($file,0,$dotPos);
		if ($file[0]==='/')		$file=substr($file,1);

		$parts=explode('/',$file);
		$partsCnt=count($parts);

		if ($partsCnt > 0 && in_array($parts[0], Config::get('divisions'))) {
			$this->_division = $parts[0];
			$parts = array_slice($parts, 1);
		}
		else
			$this->_division='';

		if (empty($parts[0]))		$this->_module=$appConfig['module']['default'];
		else						$this->_module=$parts[0];
		if ($partsCnt<2 ||
			empty($parts[1]))		$this->_method=$appConfig['module']['defaultViewsControllerAction'];
		else						$this->_method=$parts[1];

		if ($partsCnt>2)			$this->_params=array_slice($parts,2);
	}

    /**
     * @return string
     * @throws Exception\NotFoundException
     * @throws LogicException
     */
	public		function	generateResponse()
	{
		switch ($this->_requestType) {
			default:
			case RequestType::none:
			    throw new Exception\NotFoundException('Unknown request type.');
				break;

			case RequestType::getModuleView:
				$this->_responseType = ResponseType::html;
				$strResponse		 = $this->getModuleView($this->_division, $this->_module, $this->_method, $this->_params);
				break;
			case RequestType::callModuleMethod:
				$this->_responseType = ResponseType::rpc;
				$strResponse		 = $this->callModuleMethod($this->_division, $this->_module, $this->_method, $this->_params);
				break;
		}

		if ($this->_responseType==ResponseType::rpc)
		{
			if ($strResponse===null)
			    throw new LogicException("Method '$this->_method' of module '$this->_module' does not returns any value or returns <null>");
			$rpcResponse=new XmlRpcResponse($strResponse);
			$strResponse=$rpcResponse->xml;
		}
		if ($this->_responseType & ResponseType::xml)
			header('Content-type: text/xml');

		return $strResponse;
	}

    /**
     * @param string $division
     * @param string $module
     * @param string $method
     * @param array  $params
     *
     * @return string
     * @throws Exception\NotFoundException
     */
	public		function	getModuleView    (      $division,$module,$method,$params){
		return       $this->callModuleEssence (CallType::view   ,$division,$module,$method,$params);
	}
	public		function	callModuleMethod (      $division,$module,$method,$params){
		return       $this->callModuleEssence (CallType::method ,$division,$module,$method,$params);
	}
	private		function	callModuleEssence($type,$division,$module,$method,$params)
	{
		$this->loadModule($division,$module,$type);

		$className=ucfirst($module).ucfirst($division).($type==CallType::view?'Views':'Methods').'Controller';
		if (!class_exists($className))
		    throw new Exception\NotFoundException("Class '$className' does not exists.");
		$responser=new $className($division,$module,$method);
		$this->_responser=&$responser;

		$classMethods=get_class_methods($className);
		if (!in_array($method,$classMethods))
            throw new Exception\NotFoundException("Method '$method' does not contains in class '$className'.");

				  call_user_func_array(array(&$responser,'setUp'   ),$params);
		$response=call_user_func_array(array(&$responser,$method   ),$params);
				  call_user_func_array(array(&$responser,'tearDown'),$params);

		if ($type==CallType::view)
		{
			$this->_showProfilerInfoOnDebug=$responser->showProfilerInfoOnDebug;
			$this->_showAppDevToolsOnDebug =$responser->showAppDevToolsOnDebug ;
		}

		if ($type==CallType::view)
			return $responser->response;

		return $response;
	}

    /**
     * @deprecated
     *
     * @param string $rpcQuery with xmlrpc
     *
     * @throws Exception\NotFoundException
     */
	private		function	parseRpcQuery($rpcQuery)
	{
		$rpcRequest=new XmlRpcRequest($rpcQuery);
		if ($rpcRequest->errno)
            throw new Exception\NotFoundException("Can't parse xmlrpc request: Colibri\XmlRpc\Request error[$rpcRequest->errno]: $rpcRequest->error.");

		$appConfig = Config::get('application');

		$parts=explode('.',$rpcRequest->methodName);
		$partsCnt=count($parts);

		if ($partsCnt > 0 && in_array($parts[0], Config::get('divisions'))) {
			$this->_division=$parts[0];
			$parts=array_slice($parts,1);
		}
		else
			$this->_division='';

		if (empty($parts[0]))		$this->_module=$appConfig['module']['default'];
		else						$this->_module=$parts[0];
		if ($partsCnt<2 ||
			empty($parts[1]))		$this->_method=$appConfig['module']['defaultMethodsControllerAction'];
		else						$this->_method=$parts[1];

		$this->_params=$rpcRequest->params;
	}

    /**
     * @param string $division   name of division (as a folder name)
     * @param string $moduleName name of module (as a folder name)
     * @param int    $type       one of CallType::<const> 'views' or 'methods'
     *
     * @throws Exception\NotFoundException
     * @throws LogicException
     */
	private		function	loadModule($division,$moduleName,$type=CallType::view)
	{
		$mPath=$moduleName.'/'.($division===''?'primary/':$division.'/');
		$mName=ucfirst($moduleName).ucfirst($division);

		$fileName=MODULES.$mPath;
		if     ($type==CallType::view)		$fileName.=$mName.'ViewsController.php';
		elseif ($type==CallType::method)	$fileName.=$mName.'Methods.php';
		else                                throw new LogicException("Unknown CallType $type");

		if (!file_exists($fileName))
            throw new Exception\NotFoundException("Can't load module: file '$fileName' does not exists.");
		else
		    // @todo remove this (carefully)
			require_once($fileName);
	}

    /**
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     *
     * @throws \Exception
     */
	public static function errorHandler($code,$message,$file,$line)
	{
		throw new \Exception("php error [$code]: '$message' in $file:$line");
	}

    /**
     * @param \Throwable|\Exception $exc
     */
	public static function exceptionHandler($exc)
	{
		$message = $exc->__toString();
		if (DEBUG)
			$error = $message;

		include(HTTPERRORS . '500.php');

		Log::add($message,'core.module');
	}
}
