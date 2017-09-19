<?php
namespace Colibri\tests;

use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use ReflectionClass;

/**
 * Extended TestCase for common functionality.
 */
class TestCase extends PhpUnitTestCase
{
    /**
     * Sets a protected property on a given object via reflection.
     *
     * @param object|string $objectOrClass  instance or class name in which protected value is being modified
     * @param array         $propertyValues assoc array with property name as key and setting value as value
     *
     * @return void
     */
    protected function inject($objectOrClass, array $propertyValues)
    {
        $reflection = new ReflectionClass($objectOrClass);
        foreach ($propertyValues as $property => $value) {
            $reflectionProperty = $reflection->getProperty($property);
            if ($reflectionProperty->isPrivate() || $reflectionProperty->isProtected()) {
                $reflectionProperty->setAccessible(true);
            }
            $reflectionProperty->setValue($objectOrClass, $value);
        }
    }
}
