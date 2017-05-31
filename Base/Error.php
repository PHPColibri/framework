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
 * @author		Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package		xTeam
 * @subpackage	a13FW
 * @version		1.1.2
 */
class Error
{
	private	static	$_errors=[
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

		/* Application\Engine\Base */
		1001	=> 'can\'t connect to memcache server.',



		/* ViewsController */
		1311	=> 'template not loaded.',


	];
	
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
 * @author		Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package		xTeam
 * @subpackage	a13FW
 */
abstract
class AdditionalErrorException extends \Exception
{
	protected $addErrNumber;

    /**
     * @param string $message
     * @param int    $errCode
     * @param int    $addErrNumber
     */
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
/** @deprecated */
class SqlException				extends AdditionalErrorException	{}
