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
 * @copyright (C) OXID eSales AG 2003-2013
 */

/**
 * List manager class
 */
class oePayPalList implements Iterator, Countable
{
    /**
     * Array of objects (some object list).
     *
     * @var array $_aArray
     */
    protected $_aArray = array();

    /**
     * Save the state, that active element was unset
     * needed for proper foreach iterator functionality
     *
     * @var bool $_blRemovedActive
     */
    protected $_blRemovedActive = false;

    /**
     * Flag if array is ok or not
     *
     * @var boolean $_blValid
     */
    private $_blValid = true;

    /**
     * -----------------------------------------------------------------------------------------------------
     *
     * Implementation of SPL Array classes functions follows here
     *
     * -----------------------------------------------------------------------------------------------------
     */


    /**
     * Returns SPL array keys
     *
     * @return array
     */
    public function arrayKeys()
    {
        return array_keys( $this->_aArray );
    }

    /**
     * rewind for SPL
     *
     * @return null;
     */
    public function rewind()
    {
        $this->_blRemovedActive = false;
        $this->_blValid = ( false !== reset( $this->_aArray ) );
    }

    /**
     * current for SPL
     *
     * @return null;
     */
    public function current()
    {
        return current( $this->_aArray );
    }

    /**
     * key for SPL
     *
     * @return mixed
     */
    public function key()
    {
        return key( $this->_aArray );
    }

    /**
     * previous / first array element
     *
     * @return mixed
     */
    public function prev()
    {
        $oVar = prev($this->_aArray);
        if ($oVar === false) {
            // the first element, reset pointer
            $oVar = reset($this->_aArray);
        }
        $this->_blRemovedActive = false;
        return $oVar;
    }

    /**
     * next for SPL
     *
     * @return null;
     */
    public function next()
    {
        if ($this->_blRemovedActive === true && current($this->_aArray)) {
            $oVar = $this->prev();
        } else {
            $oVar = next($this->_aArray);
        }

        $this->_blValid = ( false !== $oVar );
    }

    /**
     * valid for SPL
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->_blValid;
    }

    /**
     * count for SPL
     *
     * @return integer
     */
    public function count()
    {
        return count( $this->_aArray );
    }

    /**
     * clears/destroys list contents
     *
     * @return null;
     */
    public function clear()
    {
        $this->_aArray = array();
    }

    /**
     * copies a given array over the objects internal array (something like old $myList->aList = $aArray)
     *
     * @param array $aArray array of list items
     *
     * @return null
     */
    public function setArray( $aArray )
    {
        $this->_aArray = $aArray;
    }

    /**
     * returns the array reversed, the internal array remains untouched
     *
     * @return array
     */
    public function reverse()
    {
        return array_reverse( $this->_aArray );
    }

    /**
     * -----------------------------------------------------------------------------------------------------
     * SPL implementation end
     * -----------------------------------------------------------------------------------------------------
     */

    /**
     * Backward compatibility method
     *
     * @param string $sName Variable name
     *
     * @return mixed
     */
    public function __get( $sName )
    {
        return $this->_aArray;
    }

    /**
     * Returns list items array
     *
     * @return array
     */
    public function getArray()
    {
        return $this->_aArray;
    }
}
