<?php
namespace Colibri\Base;

/**
 * Interface for DynamicCollection, that unite \ArrayAccess, \Iterator, & \Countable.
 */
interface DynamicCollectionInterface extends \ArrayAccess, \Iterator, \Countable
{
}
