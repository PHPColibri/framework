<?php
namespace Colibri\Routine\Controller;

use Colibri\Controller\ViewsController;

use Colibri\Database\Object;
use Colibri\Database\ObjectCollection;
use Colibri\Validation\Validation;

/**
 * Description of moduleRoutineViews
 *
 * @author		Александр Чибрикин aka alek13 <chibrikinalex@mail.ru>
 * @package		xTeam
 * @version		1.00.0
 * @exception	???
 */
abstract
class RoutineViewsController extends ViewsController
{
	protected	$itemClass	=null;
	protected	$itemTplVar	=null;
	protected	$listClass	=null;
	protected	$listTplVar	=null;

	protected	$pagedList		=false;
	protected	$recordsPerPage	=20;

	public		function	defaultView()
	{
		$items=new $this->listClass(\API::$db);
		$this->applyListFilters($items);
		if ($items->load()===false)
			$this->__raiseSqlError(9999, $items->error_number, $items->error_message);

		$this->template->vars[$this->listTplVar]=$items;
		if ($this->pagedList)
			$this->template->vars['pagination']=array(
				'page'			=> (int)(isset($_GET['page'])?$_GET['page']:0),
				'recordsPerPage'=> $items->recordsPerPage,
				'recordsCount'	=> $items->recordsCount,
				'pagesCount'	=> $items->pagesCount,
				'base_url'		=> '/'.$this->division.'/'.$this->module
			);
	}
	protected	function	applyListFilters(ObjectCollection $items)
	{
		if ($this->pagedList)
			$items->page(isset($_GET['page'])?$_GET['page']:0,$this->recordsPerPage);
	}

/*final*/public	function	create()	{	$this->prepareEditorTpl();		}
/*final*/public	function	edit($id)	{	$this->prepareEditorTpl($id);	}
	protected	function	prepareEditorTpl($id=null)
	{
		$item=new $this->itemClass(\API::$db);

		//kminaev - cancel button support
		if(isset($_POST['cancel']))
		{ // TODO: purge
			header('Location: /'.$this->division.'/'.$this->module);
			exit;
		}
		
		if ($_POST) // if must save any changes
		{	// do validation
			$post=new Validation($_POST);
			$this->validate($post,$id);
			if ($post->valid())
				if ($this->dbChange($item,$id)) // save changes
				{
					header('Location: /'.$this->division.'/'.$this->module);
					exit();
				}

			if (isset($this->template->vars['errors']))
				$errors=$this->template->vars['errors'];
			else
				$errors=array();

			$this->template->vars['errors']=array_merge($errors,$post->errors);
		}
		
		if ($id===null) // create mode
		{
			$tplPath=sprintf(MODULE_TEMPLATES,$this->module.'/'.$this->division);
			$tplName=$tplPath.$this->division.'_'.$this->module.'_edit.php';
			$this->template->load($tplName);
		}
		else // edit mode
		{
			if ($item->load($id)===false)
				$this->__raiseSqlError(2751, $item->error_number, $item->error_message);
		}

		$this->initItem($item,$id);
		
		if ($_POST) // if post data not valid ($_POST && isset($this->template->vars['errors']))
			foreach ($_POST as $key => $value)  // for fill form fields with previous values (entered by user)
				if (isset($item->$key))
					$item->$key=$value;

		$this->template->vars['mode']=$id===null?'create':'edit';
		$this->template->vars[$this->itemTplVar]=$item;
	}

	protected	function	initItem(Object $item,$id=null){}
	protected	function	defaultValuesOnDbChange($id=null){ return array(); }

	protected	function	dbChange(Object $item,$id=null)
	{
		// TODO: $this->setPKValue($id) instead "$PKName[0]" or some else
		$PKName=$item->getPKFieldName();
		$PKName=$PKName[0];
		if ($id!==null)		$item->$PKName=$id;
		$method=$id===null?'create':'save';
		$addVals=$this->defaultValuesOnDbChange($id);
		if (is_array($addVals))
			$vals=array_merge($_POST,$addVals);
		else
			$vals=$_POST;
		$changed=$item->$method($vals);
		if ($changed===false)
			if ($item->error_number==1062)
				$this->template->vars['errors'][]=\API::$db->getLastError().":\n".' такая запись существует или находится в Корзине.';
			else
				$this->__raiseSqlError(9999, $item->error_number, $item->error_message);

		return $changed;
	}
abstract 
	protected	function	validate(Validation $vScope,$id=null);

	public		function	delete($id)
	{
		$item=new $this->itemClass(\API::$db);
		if ($item->delete($id)===false)
			$this->__raiseSqlError(2741, $item->error_number, $item->error_message);
		header('Location: '.(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'/'.$this->division.'/'.$this->module));
	}
}
