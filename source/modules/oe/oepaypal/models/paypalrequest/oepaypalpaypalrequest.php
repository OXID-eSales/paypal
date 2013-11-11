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
 * PayPal request class
 */
class oePayPalPayPalRequest
{
    /**
     * PayPal response data
     *
     * @var array
     */
    protected $_aData = array();

    /**
     * Sets value to data by given key
     *
     * @param string $sKey key of data value
     * @param string $sValue data value
     */
    public function setParameter( $sKey, $sValue )
    {
        $this->_aData[ $sKey ] = $sValue;
    }

    /**
     * Returns value by given key
     *
     * @param string $sKey key of data value
     *
     * @return string
     */
    public function getParameter( $sKey )
    {
        return $this->_aData[ $sKey ];
    }

    /**
     * Set request data
     *
     * @param array $aResponseData Response data from PayPal
     */
    public function setData( $aResponseData )
    {
        $this->_aData = $aResponseData;
    }

    /**
     * Return request data
     *
     * @return array
     */
    public function getData()
    {
        return $this->_aData;
    }

    /**
     * Return value from data by given key
     *
     * @param string $sKey key of data value
     * @param string $sValue data value
     */
    protected function _setValue( $sKey, $sValue )
    {
        $this->_aData[ $sKey ] = $sValue;
    }
}