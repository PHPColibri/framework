<?php
namespace Colibri\Base;

/**
 * class Error
 *
 * Базовый класс ошибок. Все ошибки перечислены и описаны здесь и вызываются методом __raiseError(№) из класса, наследующего этот.
 * 
 * Пример:
 * <code>
 * class CMyNewClass extends Error
 * {
 *     function someMethod($str)
 *     {
 *         if ($str===null)		$this->__raiseError(2201); // 2201 => 'param $srt can\'t be <null>.'
 *         // do some other code
 *     }
 * }
 * </code>
 * и соответственно в классе Error надо добавить строку с описанием ошибки:
 * <code>
 * class Error
 * {
 *     private static  $_errors=array
 *     (
 *         // ...
 *         
 *         / * CMyNewClass * /
 *         2201    => 'param $str can\'t be <null>.' // <--!!!!!!!!!!!!!!!!!!!
 *         // ...
 *     );
 * </code>
 *
 * @deprecated
 * 
 * @author		Александр Чибрикин aka alek13 <chibrikinalex@mail.ru>
 * @package		xTeam
 * @subpackage	a13FW
 * @version		1.1.2
 */
class Error
{
	private	static	$_errors=array
	(
		   0	=> 'internal php error [%1]: %2. [%3(%4)]',
		  -1	=> 'unknown error || developer memory failed. :)',
		/* PropertyAccess */
		   1	=> 'свойство $%1 не определено в классе %2',
		/* cControl */
		   9	=> 'Внутренняя перемнная $%1 в классе %2 не установлена или равна null',
		  10	=> 'Доступ к свойству \'%1\' закрыт',
		/* сUploadedConverter */
		 101	=> '$_FILES[\'%1\'] not set.',
		 102	=> 'file upload failed: file \'%1\'(temp name: \'%2\') with key \'%3\' !is_uploaded_file.',
		 103	=> 'file upload error: %1.',
		 104	=> 'property \'%1\' of class \'%2\' is null or is not a \'%3\' object.',
		 105	=> 'can\'t move file \'%1\' to \'%2\': %3',
		 106	=> 'can\'t open url \'%1\': %2',
		 107	=> 'incorrect response format: can\'t find \'\n\'',
		 108	=> "convertion failed: %1 \n====================================>",
		 109	=> 'unknown mime type: %1',
		/* aFSObject */
		 201	=> 'property $%1 in class %2 is readonly.',
		 202	=> 'you have to implement property $%1 in class %2.',
		 203	=> 'property $%1 not implement in class %2.',
		/* Object */
		/** @deprecated */ //301	=> 'свойство $%1 в классе %2 не определено или не является public.',
		/** @deprecated */ //302	=> 'unknown errors type %1.',
		/* ObjectCollection (etc) */
		 401	=> 'can\'t rebuild query \'%1\' for custom load in %2 [line: %3]. possible: getFieldsAndTypes() failed (check for sql errors) or incorrect wherePlan() format',
		/* Colibri\XmlRpc\Request */
		// 501
		/* XmlRpc\Response */
		// 601
		/* PhpTemplate */
		 701	=> '\'filename\' not set.',
		 702	=> 'file \'%1\' does not exists.',

		/* Application\Engine\Base */
		1001	=> 'can\'t connect to memcache server.',
		/* Application\Engine */
		1203	=> 'wrong routing format',
		1211	=> 'unknown request type.',
		1212	=> 'method \'%2\' of module \'%1\' does not returns any value or returns <null>',
		1221	=> 'class \'%1\' does not exists.',
		1222	=> 'method \'%1\' does not contains in class \'%2\'.',
		1231	=> 'can\'t parse xmlrpc request: Colibri\XmlRpc\Request error[%1]: %2.',
		1241	=> 'can\'t load module: module \'%1\' does not installed.',
		1242	=> 'can\'t load module: file \'%1\' does not exists.',
		/* ViewsController */
		1311	=> 'template not loaded.',



		
		// Modules:
		/* CUsersViews */
		//2100
		/* CUsersMethods */
		//2200

		/* CArticlesViews */
		2511	=> 'can\'t load article.', // sql error
		/* CArticlesMethods */
		//2600
		2611	=> 'can\'t create article.', //sql error
		2621	=> 'can\'t load article.', // sql error
		/* CArticlesAdminViews */
		2711    => 'can\'t load article.', // sql error
		2712    => 'can\'t save article.', // sql error
		2713    => 'can\'t delete article.', // sql error
		2781	=> 'can\'t load articles.', // sql error
		2782	=> 'can\'t load news.', // sql error
		2783	=> 'can\'t load files.', // sql error
		/* CArticlesAdminMethods */
		//2800

		/* CCommentsViews */
		//2900
		/* CCommentsMethods */
		//3000
		/* CAddressAdminViews */
		//3100
		3101	=> 'can\'t load article',
		3102	=> 'can\'t save article',
		3103	=> 'can\'t create article',
		3104	=> 'can\'t create address',
		3105	=> 'can\'t load address',
		3106	=> 'can\'t save address',
		3107	=> 'can\'t delete address',

		/* CNewsAdminViews */
		//3200
		3201	=> 'can\'t load article',
		3202	=> 'can\'t save article',
		3203	=> 'can\'t create article',
		3204	=> 'can\'t create news',
		3205	=> 'can\'t load news',
		3206	=> 'can\'t save news',
		3207	=> 'can\'t delete news',

		/* CAnnouncesAdminViews */
		//3300
		3301	=> 'can\'t load article',
		3302	=> 'can\'t save article',
		3303	=> 'can\'t create article',
		3304	=> 'can\'t create announce',
		3305	=> 'can\'t load announce',
		3306	=> 'can\'t save announce',
		3307	=> 'can\'t delete announce',

		/* CCabinetArticlesCollection */
		//3400
		3401	=> 'can\'t load cabinet article',
		3402	=> 'can\'t save cabinet article',
		3403	=> 'can\'t create cabinet article',

		//CCabinetClaimsCollection errors
		3501	=> 'can\'t load cabinet claim(s)',
		3502	=> 'can\'t save cabinet claim(s)',
		3503	=> 'can\'t create cabinet claim(s)',

		//CSettingsViews errors
		3601	=> 'can\'t load settings',
		3602	=> 'can\'t save settings',
		3603	=> 'can\'t create settings',

		//CNewsSubscription errors
		3701	=> 'can\'t load news subscription',
		3702	=> 'can\'t save subscription',
		3703	=> 'can\'t create subscription',

		/* CMenuViews */
		3801	=> 'can\'t load menu subnodes',

		/* CGalleryViews */
		4001	=> 'can\'t load gallery with id %1.',

		//  Logic errors
		12611	=> 'article with same \'url alias\' olready exists.',
		13401	=> 'article with type \'%1\' not found.',
	);
	
	/**
	 * @deprecated
	 *
	 * @param	int $errNumber
	 * @param	...
	 */
	public		static	function	__raiseError($errNumber)
	{
		$argList=func_get_args();
		$argNum =func_num_args();
		$errMessage=isset(self::$_errors[$errNumber])?self::$_errors[$errNumber]:self::$_errors[-1].' ['.$errNumber.']';
		$errNumber =isset(self::$_errors[$errNumber])?$errNumber:-1;
		for ($i=1;$i<$argNum;$i++)
			$errMessage=str_replace('%'.$i,$argList[$i],$errMessage);
		throw new \Exception($errMessage,$errNumber);
	}
	/**
	 * @deprecated
	 */
	private		static	function	__raiseAddError($errNumber,$addErrNumber,$addErrMessage,$exceptionType)
	{
		$argList=func_get_args();
		$argNum =func_num_args();
		$errMessage=isset(self::$_errors[$errNumber])?self::$_errors[$errNumber]:self::$_errors[-1].' ['.$errNumber.']';
		$errNumber =isset(self::$_errors[$errNumber])?$errNumber:-1;
		for ($i=4;$i<$argNum;$i++)
		{
			$errMessage=str_replace('%'.($i-3),$argList[$i],$errMessage);
			$addErrMessage=str_replace('%'.($i-3),$argList[$i],$addErrMessage);
		}
		$errMessage.="\n".$exceptionType.' error['.$addErrNumber.']: '.$addErrMessage;
		$exceptionName=$exceptionType.'Exception';
		throw new $exceptionName($errMessage,$errNumber,$addErrNumber);
	}
	/**
	 * @deprecated 
	 */
	public		static	function	__raiseSqlError($errNumber,$sqlErrNumber,$sqlErrMessage)
	{
		$args=func_get_args();
		array_splice($args,3,0,'\Colibri\Base\Sql');
		call_user_func_array('self::__raiseAddError',$args);
	}
	/**
	 * @deprecated 
	 */
	public		static	function	__raiseLogicError($errNumber,$logicErrNumber)
	{
		$logicErrMessage=isset(self::$_errors[$logicErrNumber])?self::$_errors[$logicErrNumber]:self::$_errors[-1].' ['.$logicErrNumber.']';
		$args=func_get_args();
		array_splice($args,2,0,$logicErrMessage);
		array_splice($args,3,0,'\Colibri\Base\BuisnessLogic');
		call_user_func_array('self::__raiseAddError',$args);
	}
}

/**
 * Абстрактный класс AdditionalErrorException.
 *
 * @author		Александр Чибрикин aka alek13 <chibrikinalex@mail.ru>
 * @package		xTeam
 * @subpackage	a13FW
 */
abstract
class AdditionalErrorException extends \Exception
{
	protected	$addErrNumber;
	
	public	function	__construct($message,$errCode,$addErrNumber)
	{
		parent::__construct($message,$errCode);
		$this->addErrNumber=$addErrNumber;
	}
	public	function	getAddError()
	{
		return $this->addErrNumber;
	}
}

class SqlException				extends AdditionalErrorException	{}
class BuisnessLogicException	extends AdditionalErrorException	{}

