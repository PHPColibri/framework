<?php
require_once(CONFIGS.'application.php');

use Colibri\Application\ResponseType;
use Colibri\Application\Engine as ApplicationEngine;

use Colibri\Log\Log;
use Colibri\Cache\Memcache;
use Colibri\Config\Config;
use Colibri\Database\MySQL;
use Colibri\Base\BuisnessLogicException;
use Colibri\Base\AdditionalErrorException;
use Colibri\XmlRpc\Response as XmlRpcResponse;


$mEngine=null;

try
{
	$time=microtime(true);

	$mEngine=new ApplicationEngine();
	$content=$mEngine->generateResponse();

	echo($content);

	// TODO [alek13]: bring out
	if (DEBUG && $mEngine->responseType==ResponseType::html)
	{
		if ($mEngine->showProfilerInfoOnDebug)
		{
			echo('<pre style="background-color:#333;color:lime;padding:4px;border:solid 1px lime">');
			echo('<div align=center>');
			echo('memory usage: <b>'.memory_get_peak_usage().'</b>');
			$time=microtime(true)-$time;
			echo('<div style=/*font-size:'.(10+round($time*10)).'px>Время генерации страницы: <b>'.$time.'</b></div>');
			echo('количество запросов: <b>'. MySQL::$queriesCount.'</b><br>');
			echo('количество запросов к Memcache: <b>'.  Memcache::getQueriesCount().'</b><br>');
			echo('</div>');
			echo(MySQL::$strQueries);
			echo('</pre>');
		}
		if ($mEngine->showAppDevToolsOnDebug)
		{
			echo('<div style="position:absolute;top:0px;right:0px;border:solid 1px #678;margin:4px;padding:4px 6px;background-color:#def;opacity:0.6;z-index:10000">');
			echo('<a href=/devtools/sess_destroy>session destroy</a> | ');
			echo('<a href=/devtools/unset_session>unset session</a> | ');
			echo('<a href=/devtools/show_session>show session</a>');
			echo('</div>');
		}
	}
}

catch (BuisnessLogicException $exc)
{
	$message="\n".'error ['.$exc->getCode().']: '. $exc->getMessage();
	
	if ($mEngine->responseType==ResponseType::rpc)
	{
		$appConfig = Config::get('application');
		header('Content-type: text/xml; charset='.$appConfig['encoding']);
		echo(XmlRpcResponse::additionalFault(
			$exc->getCode(),
			$exc->getAddError(),
			$exc->getMessage()
		));
	}
	else
		if (DEBUG)
			echo('<tt>'.nl2br(htmlspecialchars("\n".'DEBUG Info: '.$message)).'</tt><br/>');
		else
		{
			header('HTTP/1.1 500 Internal Server Error');
			include(HTTPERRORS.'500.php');
		}
}
catch (AdditionalErrorException $exc)
{
	$message="\n".
		'error ['. $exc->getCode().']: '. $exc->getMessage()."\n\n".
		$exc->getTraceAsString();
	$screenMsg=$message."\n\n".
		'$_GET: ' .print_r($_GET ,true)."\n".
		'$_POST: '.print_r($_POST,true);

	if ($mEngine!==null && $mEngine->responseType==ResponseType::rpc)
	{
		$appConfig = Config::get('application');
		header('Content-type: text/xml; charset='.$appConfig['encoding']);
		echo(XmlRpcResponse::additionalFault(
			$exc->getCode(),
			$exc->getAddError(),
			DEBUG ? $screenMsg : 'Internal Server Error.'
		));
	}
	else
		if (DEBUG)
			echo('<tt>'.nl2br(htmlspecialchars("\n".'DEBUG Info: '.$screenMsg)).'</tt><br/>');
		else
		{
			header('HTTP/1.1 500 Internal Server Error');
			include(HTTPERRORS.'500.php');
		}

	Log::add($message,'core.module');
}

catch (\Exception $exc)
{
	$message="\n".
		'error ['. $exc->getCode().']: '. $exc->getMessage()."\n\n".
		$exc->getTraceAsString();
	$screenMsg=$message."\n\n".
		'$_GET: ' .print_r($_GET ,true)."\n".
		'$_POST: '.print_r($_POST,true);

	if ($mEngine!==null && $mEngine->responseType==ResponseType::rpc)
	{
		$appConfig = Config::get('application');
		header('Content-type: text/xml; charset='.$appConfig['encoding']);
		echo(XmlRpcResponse::fault(
			$exc->getCode(),
			DEBUG?$screenMsg:'Internal Server Error.'
		));
	}
	else
	{
		if (DEBUG)
			$error = htmlspecialchars($screenMsg);
		if ($exc->getCode()==1242 || $exc->getCode()==1222)
		{
			header('HTTP/1.1 404 Not Found');
			include(HTTPERRORS.'404.php');
		}
		else
		{
			header('HTTP/1.1 500 Internal Server Error');
			include(HTTPERRORS.'500.php');
		}
	}
	if ($exc->getCode()==1242)
		Log::warning(
			' Request: '.$_SERVER['REQUEST_URI'].
			' Referer: '.(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'')
			,
			'core.notFound');
	else
		Log::add($message,'core.module');
}
