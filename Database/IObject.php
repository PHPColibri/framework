<?php
namespace Colibri\Database;

/**
 *
 */
interface    IObject
{
    public function __get($propertyName);

    public function create();

    public function delete();

    public function save(array $attributes = null);

    public function load($id = null);

    public function reload();
}
