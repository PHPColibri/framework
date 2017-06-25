<?php
namespace Colibri\Database;

/**
 *
 */
interface IObject
{
    /**
     * @param string $propertyName
     *
     * @return mixed
     */
    public function __get($propertyName);

    /**
     * @return static
     */
    public function create();

    /**
     * @return void
     */
    public function delete();

    /**
     * @param array|null $attributes
     *
     * @return bool
     */
    public function save(array $attributes = null);

    /**
     * @param int|array|null $id_or_where
     *
     * @return bool|null
     */
    public function load($id_or_where = null);

    /**
     * @return bool|null
     */
    public function reload();
}
