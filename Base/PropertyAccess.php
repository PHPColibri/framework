<?php
namespace Colibri\Base;

/**
 * Абстрактный клас простого доступа к private переменным.
 *
 * Наследуясь от этого класса, вы можете объявлять внутренние переменные
 * (private или protected) с первым символом подчёркивания в имени и
 * иметь сразу простой public доступ к этим переменным, обращаясь к ним
 * без символа подчёркивания. В последствии вы можете некоторые из них
 * закрыть на запись и/или чтение перепереопределив методы __get и __set.
 *
 * <code>
 * class CMyClass extends PropertyAccess
 * {
 *     private $_var1;
 * }
 *
 * $x=new CMyClass();
 * $x->var1='foobar';
 * echo($x->var1);
 * </code>
 */
abstract class PropertyAccess
{
    /**
     * @param string $propName
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public function __get($propName)
    {
        $p = '_' . $propName;
        if ( ! property_exists($this, $p)) {
            throw new \RuntimeException("свойство $p не определено в классе " . static::class);
        }

        return $this->$p;
    }

    /**
     * @param string $propName
     * @param mixed  $propValue
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public function __set($propName, $propValue)
    {
        $p = '_' . $propName;
        if ( ! property_exists($this, $p)) {
            throw new \RuntimeException("свойство $p не определено в классе " . static::class);
        }

        return $this->$p = $propValue;
    }
}
