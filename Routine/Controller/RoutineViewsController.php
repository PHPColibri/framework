<?php
namespace Colibri\Routine\Controller;

use Colibri\Controller\ViewsController;
use Colibri\Database;
use Colibri\Database\Exception\SqlException;
use Colibri\Database\ModelCollection;
use Colibri\Http\Redirect;
use Colibri\Validation\Validation;

/**
 * Controller for routine CRUD actions like list, create, edit, delete.
 */
abstract class RoutineViewsController extends ViewsController
{
    /**
     * @var string class name of CRUD entity
     */
    protected $itemClass = null;
    /**
     * @var string name of variable in template, that represents one entity (used in edit.php template)
     */
    protected $itemTplVar = null;
    /**
     * @var string class name of Collection of entities
     */
    protected $listClass = null;
    /**
     * @var string name of variable in template, that represents list(Collection) of entities
     *             (used in defaultView.php template)
     */
    protected $listTplVar = null;

    /**
     * @var bool
     */
    protected $pagedList = false;
    /**
     * @var int
     */
    protected $recordsPerPage = 20;

    /**
     * Action for list entities.
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     */
    public function defaultView()
    {
        /** @var \Colibri\Database\ModelCollection $items */
        $items = new $this->listClass();
        $this->applyListFilters($items);
        $items->load();

        $this->template->vars[$this->listTplVar] = $items;
        if ($this->pagedList) {
            $this->template->vars['pagination'] = [
                'page'           => (int)(isset($_GET['page']) ? $_GET['page'] : 0),
                'recordsPerPage' => $items->recordsPerPage,
                'recordsCount'   => $items->recordsCount,
                'pagesCount'     => $items->pagesCount,
                'base_url'       => '/' . $this->division . '/' . $this->module,
            ];
        }
    }

    /**
     * Apply additional criteria to list query.
     *
     * @param \Colibri\Database\ModelCollection $items
     */
    protected function applyListFilters(ModelCollection $items)
    {
        if ($this->pagedList) {
            $items->page(isset($_GET['page']) ? $_GET['page'] : 0, $this->recordsPerPage);
        }
    }

    /**
     * Create action.
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \Exception
     */
    final public function create()
    {
        $this->prepareEditorTpl();
    }

    /**
     * Edit action.
     *
     * @param $id
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \Exception
     */
    final public function edit($id)
    {
        $this->prepareEditorTpl($id);
    }

    /**
     * Prepare template for create & edit action.
     *
     * @param int|mixed $id if equals null, then create action is called, and otherwise edit action is called
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \Exception
     */
    protected function prepareEditorTpl($id = null)
    {
        $mode = $id === null ? 'create' : 'edit';
        /** @var \Colibri\Database\Model $item */
        $item = new $this->itemClass();

        // if must save any changes
        if ($_POST) {
            $this->save($id, $item);

            return;
        }

        $this->loadTemplate($mode);

        if ($mode == 'edit') {
            $item->load($id);
        }

        $this->initItem($item, $id);

        if ($_POST) { // if post data not valid ($_POST && isset($this->template->vars['errors']))
            $this->fillWithUserValues($item);
        }

        $this->template->vars['mode']            = $mode;
        $this->template->vars[$this->itemTplVar] = $item;
    }

    /**
     * Override this method, if you want to initialize entity before display. For example, with defaults.
     *
     * @param \Colibri\Database\Model $item entity to be initialized
     * @param int|mixed               $id   if equals null, then create action is called, and otherwise edit action is
     *                                      called
     */
    protected function initItem(Database\Model $item, $id = null)
    {
    }

    /**
     * Override this method, if you want to automatically overwrite some entity properties before save.
     *
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
     * @param \Colibri\Database\Model $item
     * @param int                     $id
     *
     * @return bool
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    protected function dbChange(Database\Model $item, $id = null)
    {
        if ($id !== null) {
            $item->setPKValue([$id]);
        }

        $addValues = $this->defaultValuesOnDbChange($id);
        $values    = is_array($addValues)
            ? array_merge($_POST, $addValues)
            : $values = $_POST;

        try {
            if ($id === null) {
                $item->create($values);
            } else {
                $item->save($values);
            }
        } catch (SqlException $exception) {
            if ($exception->getCode() != 1062) {
                throw $exception;
            }

            $this->template->vars['errors'][] = 'Такая запись существует или находится в Корзине.';

            return false;
        }

        return true;
    }

    /**
     * Implement the validation.
     *
     * @param \Colibri\Validation\Validation $scope $_POST scope for validate
     * @param int|mixed                      $id    if equals null, then create action is called, and otherwise edit
     *                                              action is called
     *
     * @return mixed
     */
    abstract protected function validate(Validation $scope, $id = null);

    /**
     * Delete action.
     *
     * @param int|mixed $id
     *
     * @throws \Colibri\Database\DbException
     * @throws \Exception
     */
    public function delete($id)
    {
        /** @var \Colibri\Database\Model $item */
        $item = new $this->itemClass();
        $item->delete($id);

        header(
            'Location: ' .
            (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/' . $this->division . '/' . $this->module)
        );
    }

    /**
     * @param $item
     */
    private function fillWithUserValues($item)
    {
        foreach ($_POST as $key => $value) { // for fill form fields with previous values (entered by user)
            if (isset($item->$key)) {
                $item->$key = $value;
            }
        }
    }

    /**
     * @param string $mode
     *
     * @throws \Exception
     */
    protected function loadTemplate(string $mode)
    {
        if ($mode == 'create') { // create mode
            $tplPath = sprintf(MODULE_TEMPLATES, $this->module . '/' . $this->division);
            $tplName = $tplPath . 'edit.php';
            $this->template->load($tplName);
        }
    }

    /**
     * @param $id
     * @param $item
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    private function save($id, $item)
    {
        // do validation
        $post = new Validation($_POST);
        $this->validate($post, $id);
        if ($post->valid()) {
            if ($this->dbChange($item, $id)) { // save changes
                Redirect::to('/' . $this->division . '/' . $this->module);

                return;
            }
        }

        $errors                         = $this->template->vars['errors'] ?? [];
        $this->template->vars['errors'] = array_merge($errors, $post->errors);
    }
}
