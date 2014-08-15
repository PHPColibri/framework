<?php
namespace Colibri\Validation;

use Colibri\Base\PropertyAccess;
use Colibri\Util\String;

/**
 * Description of Validation
 *
 * @author		Александр Чибрикин aka alek13 <chibrikinalex@mail.ru>
 * @package		xTeam
 * @version		1.00.0
 *
 * @property array $errors array of validation errors.
 */
class Validation extends PropertyAccess
{
	static	public	$requiredMessage='поле \'%s\' является обязательным для заполнения.';
	static	public	$minLengthMessage='поле \'%s\' должно быть не меньше %d символов.';
	static	public	$maxLengthMessage='поле \'%s\' не должно быть больше %d символов.';
	static	public	$regexMessage='поле \'%s\' не удовлетворяет условию.';
	static	public	$isIntGt0Message='поле \'%s\' должно быть целым числом больше 0.';
	static	public	$isJSONMessage='поле \'%s\' должно быть строкой в формате JSON.';
	static	public	$isEmailMessage='поле \'%s\' должно содержать существующий почтовый ящик.';
	static	public	$isEqualMessage='поля \'%s\' должны быть одинаковыми.';
	
	protected	$_errors=array();
	protected	$scope=array();
	
	
	public		function	__construct(array $scope=null)
	{
		if ($scope!==null)
			$this->scope=$scope;
	}
	public		function	extendScope(array $scope)
	{
		return $this->scope=array_merge($this->scope,$scope);
	}
	public		function	setScope(array $scope)
	{
		$this->scope=$scope;
		$this->_errors=array();
	}
	public		function	addError($key,$message)
	{
		$this->_errors[$key]=$message;
	}



	public		function	required($key,$message=null)
	{
		if (is_array($key))
			foreach ($key as $name)
				$this->required($name,$message);
		else
			if (!(isset($this->scope[$key]) && !empty($this->scope[$key])))
				$this->_errors[$key]=sprintf($message!==null?$message:self::$requiredMessage,$key);
		return $this;
	}
	public		function	minLength($key,$minLength,$message=null)
	{
		if (is_array($key))
			foreach ($key as $k => $name)
				$this->minLength($name,is_array($minLength)?$minLength[$k]:$minLength,$message);
		else
			if (!(isset($this->scope[$key]) && mb_strlen($this->scope[$key])>=$minLength))
				$this->_errors[$key]=sprintf($message!==null?$message:self::$minLengthMessage,$key,$minLength);
		return $this;
	}
	public		function	maxLength($key,$maxLength,$message=null)
	{
		if (is_array($key))
			foreach ($key as $k => $name)
				$this->maxLength($name,is_array($maxLength)?$maxLength[$k]:$maxLength,$message);
		else
			if (!(isset($this->scope[$key]) && mb_strlen($this->scope[$key])<=$maxLength))
				$this->_errors[$key]=sprintf($message!==null?$message:self::$maxLengthMessage,$key,$maxLength);
		return $this;
	}
	public		function	regex($key,$pattern,$message=null)
	{
		if (is_array($key))
			foreach ($key as $k => $name)
				$this->regex($name,is_array($pattern)?$pattern[$k]:$pattern,$message);
		else
			if (isset($this->scope[$key]) && !(bool)preg_match($pattern,$this->scope[$key]))
				$this->_errors[$key]=sprintf($message!==null?$message:self::$regexMessage,$key);
		return $this;
	}
	public		function	isIntGt0($key,$message=null)
	{
		if (is_array($key))
			foreach ($key as $name)
				$this->isIntGt0($name,$message);
		else
			if (!(isset($this->scope[$key]) && String::isInt($this->scope[$key]) && ((int)$this->scope[$key])>0))
				$this->_errors[$key]=sprintf($message!==null?$message:self::$isIntGt0Message,$key);
		return $this;
	}
	public		function	isJSON($key,$message=null)
	{
		if (is_array($key))
			foreach ($key as $name)
				$this->isJSON($name,$message);
		else
			if (!(isset($this->scope[$key]) && String::isJSON($this->scope[$key])))
				$this->_errors[$key]=sprintf($message!==null?$message:self::$isJSONMessage,$key);
		return $this;
	}
	public		function	isEmail($key,$message=null)
	{
		if (is_array($key))
			foreach ($key as $name)
				$this->isEmail($name,$message);
		else
			if (!(isset($this->scope[$key]) && String::isEmail($this->scope[$key])))
				$this->_errors[$key]=sprintf($message!==null?$message:self::$isEmailMessage,$key);
		return $this;
	}
	public		function	isEqual(array $keys,$message=null)
	{
		$settedKey=null;
		foreach ($keys as $key)
			if ( isset($this->scope[$key]) )
			{
				$settedKey=$key;
				break;
			}
		if ($settedKey===null)
			return $this;
			
		foreach ($keys as $key)
			if (!(isset($this->scope[$key]) && $this->scope[$key]==$this->scope[$settedKey]))
			{
				$keysList = implode("', '", $keys);
				$this->_errors['[\''.$keysList.'\']']=sprintf($message!==null?$message:self::$isEqualMessage,$keysList);
			}
		return $this;
	}
	/**
	 *
	 * @param callable $checkFunc
	 * @param type $key
	 * @param type $message
	 * @return static 
	 */
	public		function	is($checkFunc,$key,$message)
	{
		//if (!is_callable($checkFunc));
		//	throw new Exception('$checkFunc param is not callable');
			
		if (is_array($key))
			foreach ($key as $name)
				$this->is($checkFunc,$name,$message);
		else
			if (!(isset($this->scope[$key]) && call_user_func($checkFunc,$this->scope[$key])))
				$this->_errors[$key]=sprintf($message,$key);
		return $this;
	}
	/**
	 *
	 * @param callable $checkFunc
	 * @param string $key
	 * @param string $message
	 * @return static 
	 */
	public		function	isNot($checkFunc,$key,$message)
	{
		//if (!is_callable($checkFunc));
		//	throw new Exception('$checkFunc param is not callable');
			
		if (is_array($key))
			foreach ($key as $name)
				$this->isNot($checkFunc,$name,$message);
		else
			if (!(isset($this->scope[$key]) && !call_user_func($checkFunc,$this->scope[$key])))
				$this->_errors[$key]=sprintf($message,$key);
		return $this;
	}
	
	

	public		function	valid()
	{
		return !(bool)count($this->_errors);
	}


}