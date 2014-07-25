<?php
namespace Colibri\Controller;

use Colibri\Controller\Base;
use Colibri\Log\Log;
use Colibri\View\PhpTemplate;

/**
 * Description of CModule
 *
 * @author		Александр Чибрикин aka alek13 <chibrikinalex@mail.ru>
 * @package		xTeam
 * @version		1.05.5.1
 *
 * @property bool $showProfilerInfoOnDebug ...
 * @property bool $showAppDevToolsOnDebug ...
 *
 * @property string $description
 * @property string $keywords
 * @property string $title
 * @property string $css
 * @property string $js
 * @property string $jsText
 * @property string $jsTextOnReady
 *
 */
abstract
class ViewsController extends Base
{
	/**
	 * @var string name of backbone template to use at
	 */
	protected	$backboneTplName=null;
	/**
	 * @var PhpTemplate
	 */
	protected	$template=null;
	/**
	 * @var	bool tells to core to use temlate or not
	 */
	protected	$useTemplate=true;
	/**
	 * @var	bool
	 */
	protected	$useBackbone=true;
	/**
	 * @var bool
	 */
	protected	$_showProfilerInfoOnDebug=true;
	/**
	 * @var bool
	 */
	protected	$_showAppDevToolsOnDebug =true;

	private		$divPath=null;
	private		$divPrefix=null;
	private		$divPostfix=null;

	private 	$_description='';
	private 	$_keywords='';
	private		$_title='';
	private		$_css=array();
	private		$_js=array();
	private		$_jsText=array();
	private		$_jsTextOnReady='';
	private		$_jsMgr=array();

	public		function	getTemplate(){ return $this->template; }
	protected	function	addCss($cssFilename,$path=RES_CSS)	{	$this->_css[]=$path.$cssFilename;	}
	protected	function	addJs ( $jsFilename,$path=RES_JS )	{	$this->_js []=$path. $jsFilename;	}
	protected	function	addJsText($jsText)					{	$this->_jsText[]=$jsText;			}
	protected	function	addJsTextOnReady($jsText)			{	$this->_jsTextOnReady.=$jsText."\n";}
	protected	function	addJsMgr($jsMgrName,$path=RES_JS)
	{
		$this->addJs($jsMgrName.'_mgr.js',$path);
		$this->_jsMgr[]=$jsMgrName;
	}
	protected	function	keywords($value=null)
	{
		return $value!==null?$this->_keywords=$value:$this->_keywords;
	}
	protected	function	title($value=null)
	{
		return $value!==null?$this->_title=$value:$this->_title;
	}
	protected	function	description($value=null)
	{
		return $value!==null?$this->_description=$value:$this->_description;
	}
	protected	function	delCss($cssFilename,$path=RES_CSS)
	{
		$cnt=count($this->_css);
		for ($i=0;$i<$cnt;$i++)
		{
			if ($this->_css[$i]==$path.$cssFilename)
			{
				array_splice($this->_css,$i,1);
				$cnt--;
			}
		}
	}

	protected	function	init()
	{
		parent::init();
		$this->divPath		=$this->_module.($this->_division===''?'':'/'.$this->_division);
		$this->divPrefix	=($this->_division===''?'':$this->_division.'_');
		$this->divPostfix	=($this->_division===''?'':'.'.$this->_division);
	}


	public		function	setUp()
	{
		$this->template=new PhpTemplate();

		// TODO: bring out into application config
		$this->addJsText(
			'var MOD=\''.MOD.'\';'."\n".
			'var IMG=\''.RES_IMG.'\';'."\n".
			'var JS =\''.RES_JS .'\';'."\n".
			'var CSS=\''.RES_CSS.'\';'."\n".
			'var SWF=\''.RES_SWF.'\';'."\n".
			'var PTO=\''.RES_IMG_PTO.'\';'
		);
		$this->addJsMgr('backbone'.($this->_division===''?'':'_'.$this->_division));
		$this->addCss('backbone'.$this->divPostfix.'.css');
		if (DEBUG)
		{
			$this->addJs('console_log_mgr.js');
			$this->addJsTextOnReady('	if (addConsoleLog)	new console_log_mgr();');
		}

		$module_methodName=$this->_module.'_'.$this->_method;
		// add default js manager
		$jsPath=$this->divPath.'/js/managers/';
		$jsName=$this->divPrefix.$module_methodName;
		$fileName=MODULES.$jsPath.$jsName.'_mgr.js';
		if (file_exists($fileName))
			$this->addJsMgr($jsName,MOD.$jsPath);

		// add default css
		$cssPath=$this->divPath.'/css/';
		$cssName=$this->divPrefix.$module_methodName.'.css';
		$fileName=MODULES.$cssPath.$cssName;
		if (file_exists($fileName))
			$this->addCss($cssName,MOD.$cssPath);
	}
	/**
	 *
	 * @return <type>
	 */
	public		function	tearDown()
	{
		if (!$this->useTemplate)
			return;

		if ($this->template->filename===null)
		{
			$tplPath=sprintf(MODULE_TEMPLATES,$this->divPath);
			$tplName=$tplPath. $this->divPrefix.$this->_module.'_'.$this->_method.'.php';
			if (file_exists($tplName))
				$this->template->load($tplName);
		}


		if (!$this->useBackbone)
		{
			if ($this->template->filename===null)
				$this->__raiseError(1311);
			$this->_response=$this->template->compile();
			return;
		}

		$backboneTplVars=array();

		$backboneTplVars['content']		=$this->template->filename!==null?$this->template->compile():(DEBUG?'DEBUG Info: template not autoloaded.<br/> no such file: '.$tplName:'');
		//TODO: special chars
		$backboneTplVars['keywords']	=!empty($this->_keywords)?"<meta name='keywords' content='".$this->_keywords."' />\n":'';
		$backboneTplVars['title']		=!empty($this->_title)?"<title>". htmlspecialchars($this->_title) ."</title>\n":'';
		//TODO: special chars
		$backboneTplVars['description']	=!empty($this->_description)?"<meta name='description' content='".$this->_description."' />\n":'';
		$backboneTplVars['javascript']	='';
		$backboneTplVars['css']			='';

		$cssCnt=count($this->_css);
		if ($cssCnt>0)
			for ($i=0;$i<$cssCnt;$i++)
				$backboneTplVars['css'].=
					'<link   type="text/css" rel="stylesheet" href="'.$this->_css[$i].'"/>'."\n";

		$jsCnt=count($this->_js);
		if ($jsCnt>0)
			for ($i=0;$i<$jsCnt;$i++)
				$backboneTplVars['javascript'].=
					'<script type="text/javascript" src="'.$this->_js[$i].'"></script>'."\n";

		// make js init code for all js managers
		$jsMgrsCnt=count($this->_jsMgr);
		if ($jsMgrsCnt>0)
		{
			$jsMgrsText='';
			for ($i=0;$i<$jsMgrsCnt;$i++)
				$jsMgrsText.="\tnew ".$this->_jsMgr[$i]."_mgr();\n";
			$this->addJsTextOnReady($jsMgrsText);
		}

		if ($this->_jsTextOnReady!='')
			$this->addJsText("$(document).ready(function(){\n".$this->_jsTextOnReady.'});');

		$jsTextCnt=count($this->_jsText);
		if ($jsTextCnt>0)
			for ($i=0;$i<$jsTextCnt;$i++)
				$backboneTplVars['javascript'].=
					"<script type=\"text/javascript\">\n".$this->_jsText[$i]."\n</script>\n";

		$backboneTplName=
			$this->backboneTplName===null ?
				'backbone'.$this->divPostfix.'.php':
				$this->backboneTplName;
		$backboneTpl=new PhpTemplate(TEMPLATES.$backboneTplName);
		$backboneTpl->vars=$backboneTplVars;

		//login error information - to be shown only once
		if(isset($_SESSION['login_error'])) {
			$backboneTpl->vars['login_error'] = $_SESSION['login_error'];
			unset($_SESSION['login_error']);
		}

		$tpl=$backboneTpl->compile();
		foreach ($backboneTplVars as $key=>$value)
			$tpl=str_replace('{'.$key.'}',$value,$tpl);
		$this->_response=$tpl;
	}
}
