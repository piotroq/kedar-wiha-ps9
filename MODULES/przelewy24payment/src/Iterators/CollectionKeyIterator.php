<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Przelewy24 powered by Waynet
 * @copyright Przelewy24
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Przelewy24\Iterators;

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class CollectionKeyIterator implements \Iterator, \ArrayAccess
{
    protected $collection = [];

    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->collection);
    }

    public function empty()
    {
        return empty($this->collection);
    }

    public function next(): void
    {
        next($this->collection);
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->collection);
    }

    public function valid(): bool
    {
        $key = key($this->collection);

        return isset($key);
    }

    public function rewind(): void
    {
        reset($this->collection);
    }

    public function getKey($key)
    {
        return $this->collection[$key];
    }

    public function isset($key)
    {
        return isset($this->collection[$key]);
    }

    public function unsetKey($key)
    {
        unset($this->collection[$key]);
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function count(): int
    {
        return count($this->collection);
    }

    public function keys()
    {
        return array_keys($this->collection);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->collection[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return isset($this->collection[$offset]) ? $this->collection[$offset] : null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->collection[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->collection[$offset]);
    }
}
