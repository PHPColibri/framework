<?php

use Colibri\Http\NotFoundException;
use Colibri\Application\Engine as ApplicationEngine;

use Colibri\Log\Log;
use Colibri\Cache\Memcache;
use Colibri\Config\Config;
use Colibri\Database\Concrete\MySQL;


$mEngine=null;

try
{
	$time=microtime(true);

	$mEngine=new ApplicationEngine();
	$content=$mEngine->generateResponse();

	echo($content);

	// TODO [alek13]: bring out
	if (DEBUG)
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

catch (NotFoundException $exc)
{
    Log::warning(
        ' Request: '.$_SERVER['REQUEST_URI'].
        ' Referer: '.(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'')
        ,
        'core.notFound');

    if (Config::application('debug'))
        $error = htmlspecialchars($exc);
    header('HTTP/1.1 404 Not Found');
    include(HTTPERRORS.'404.php');
}

catch (\Exception $exc)
{
    if (Config::application('debug'))
        $error = htmlspecialchars($exc);

    header('HTTP/1.1 500 Internal Server Error');
    include(HTTPERRORS.'500.php');

	Log::add($exc,'core.module');
}
