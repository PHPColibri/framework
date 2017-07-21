<?php
namespace Colibri\Database;

interface ObjectInterface
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

    public function delete();

    /**
     * @param array|null $attributes
     */
    public function save(array $attributes = null);

    /**
     * @param int|array|null $id_or_where
     */
    public function load($id_or_where = null);

    /**
     * @return bool|null
     */
    public function reload();
}
