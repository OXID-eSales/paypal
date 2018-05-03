<?php
/**
 * This file is part of OXID eSales PayPal module.
 *
 * OXID eSales PayPal module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

namespace OxidEsales\PayPalModule\Core;

use Countable;
use Iterator;

/**
 * List manager class.
 */
class PayPalList implements Iterator, Countable
{
    /**
     * Array of objects (some object list).
     *
     * @var array $_aArray
     */
    protected $array = array();

    /**
     * Save the state, that active element was unset
     * needed for proper foreach iterator functionality.
     *
     * @var bool $_blRemovedActive
     */
    protected $removedActive = false;

    /**
     * Flag if array is ok or not.
     *
     * @var boolean $_blValid
     */
    private $valid = true;

    /**
     * -----------------------------------------------------------------------------------------------------
     *
     * Implementation of SPL Array classes functions follows here
     *
     * -----------------------------------------------------------------------------------------------------
     */

    /**
     * Returns SPL array keys.
     *
     * @return array
     */
    public function arrayKeys()
    {
        return array_keys($this->array);
    }

    /**
     * Rewind for SPL.
     */
    public function rewind()
    {
        $this->removedActive = false;
        $this->valid = (false !== reset($this->array));
    }

    /**
     * Current for SPL.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->array);
    }

    /**
     * Key for SPL.
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->array);
    }

    /**
     * Previous / first array element.
     *
     * @return mixed
     */
    public function prev()
    {
        $var = prev($this->array);
        if ($var === false) {
            // the first element, reset pointer
            $var = reset($this->array);
        }
        $this->removedActive = false;

        return $var;
    }

    /**
     * Next for SPL.
     */
    public function next()
    {
        if ($this->removedActive === true && current($this->array)) {
            $var = $this->prev();
        } else {
            $var = next($this->array);
        }

        $this->valid = (false !== $var);
    }

    /**
     * Valid for SPL.
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->valid;
    }

    /**
     * Count for SPL.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->array);
    }

    /**
     * Clears/destroys list contents.
     */
    public function clear()
    {
        $this->array = array();
    }

    /**
     * copies a given array over the objects internal array (something like old $myList->aList = $array).
     *
     * @param array $array array of list items
     */
    public function setArray($array)
    {
        $this->array = $array;
    }

    /**
     * Returns the array reversed, the internal array remains untouched.
     *
     * @return array
     */
    public function reverse()
    {
        return array_reverse($this->array);
    }

    /**
     * -----------------------------------------------------------------------------------------------------
     * SPL implementation end
     * -----------------------------------------------------------------------------------------------------
     */

    /**
     * Backward compatibility method.
     *
     * @param string $name Variable name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->array;
    }

    /**
     * Returns list items array.
     *
     * @return array
     */
    public function getArray()
    {
        return $this->array;
    }
}
