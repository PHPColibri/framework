<?php

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
