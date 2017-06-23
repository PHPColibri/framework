<?php
namespace Colibri\Routine\Controller;

use Colibri\Controller\ViewsController;
use Colibri\Database;
use Colibri\Database\Exception\SqlException;
use Colibri\Database\ObjectCollection;
use Colibri\Validation\Validation;

/**
 * Description of moduleRoutineViews
 */
abstract class RoutineViewsController extends ViewsController
{
    protected $itemClass = null;
    protected $itemTplVar = null;
    protected $listClass = null;
    protected $listTplVar = null;

    protected $pagedList = false;
    protected $recordsPerPage = 20;

    public function defaultView()
    {
        /** @var \Colibri\Database\ObjectCollection $items */
        $items = new $this->listClass();
        $this->applyListFilters($items);
        $items->load();

        $this->template->vars[$this->listTplVar] = $items;
        if ($this->pagedList)
            $this->template->vars['pagination'] = [
                'page'           => (int)(isset($_GET['page']) ? $_GET['page'] : 0),
                'recordsPerPage' => $items->recordsPerPage,
                'recordsCount'   => $items->recordsCount,
                'pagesCount'     => $items->pagesCount,
                'base_url'       => '/' . $this->division . '/' . $this->module,
            ];
    }

    protected function applyListFilters(ObjectCollection $items)
    {
        if ($this->pagedList)
            $items->page(isset($_GET['page']) ? $_GET['page'] : 0, $this->recordsPerPage);
    }

    final public function create()
    {
        $this->prepareEditorTpl();
    }

    final public function edit($id)
    {
        $this->prepareEditorTpl($id);
    }

    protected function prepareEditorTpl($id = null)
    {
        /** @var \Colibri\Database\Object $item */
        $item = new $this->itemClass();

        if ($_POST) // if must save any changes
        {    // do validation
            $post = new Validation($_POST);
            $this->validate($post, $id);
            if ($post->valid())
                if ($this->dbChange($item, $id)) // save changes
                {
                    header('Location: /' . $this->division . '/' . $this->module);
                    exit();
                }

            if (isset($this->template->vars['errors']))
                $errors = $this->template->vars['errors'];
            else
                $errors = [];

            $this->template->vars['errors'] = array_merge($errors, $post->errors);
        }

        if ($id === null) // create mode
        {
            $tplPath = sprintf(MODULE_TEMPLATES, $this->module . '/' . $this->division);
            $tplName = $tplPath . 'edit.php';
            $this->template->load($tplName);
        } else // edit mode
        {
            $item->load($id);
        }

        $this->initItem($item, $id);

        if ($_POST) // if post data not valid ($_POST && isset($this->template->vars['errors']))
            foreach ($_POST as $key => $value)  // for fill form fields with previous values (entered by user)
                if (isset($item->$key))
                    $item->$key = $value;

        $this->template->vars['mode']            = $id === null ? 'create' : 'edit';
        $this->template->vars[$this->itemTplVar] = $item;
    }

    /**
     * @param \Colibri\Database\Object $item
     * @param null                     $id
     */
    protected function initItem(Database\Object $item, $id = null)
    {
    }

    /**
     * @param null $id
     *
     * @return array
     */
    protected function defaultValuesOnDbChange(/** @noinspection PhpUnusedParameterInspection */
        $id = null)
    {
        return [];
    }

    /**
     * @param \Colibri\Database\Object $item
     * @param int                      $id
     *
     * @return bool
     * @throws \Colibri\Database\Exception\SqlException
     */
    protected function dbChange(Database\Object $item, $id = null)
    {
        // TODO: $this->setPKValue($id) instead "$PKName[0]" or some else
        $PKName = $item->getPKFieldName();
        $PKName = $PKName[0];
        if ($id !== null) $item->$PKName = $id;
        $method  = $id === null ? 'create' : 'save';
        $addValues = $this->defaultValuesOnDbChange($id);
        if (is_array($addValues))
            $values = array_merge($_POST, $addValues);
        else
            $values = $_POST;
        try {
            $item->$method($values);
        } catch (SqlException $exception) {
            if ($exception->getCode() != 1062)
                throw $exception;

            $this->template->vars['errors'][] = 'Такая запись существует или находится в Корзине.';

            return false;
        }

        return true;
    }

    abstract
    protected function validate(Validation $vScope, $id = null);

    public function delete($id)
    {
        /** @var \Colibri\Database\Object $item */
        $item = new $this->itemClass();
        $item->delete($id);

        header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/' . $this->division . '/' . $this->module));
    }
}
