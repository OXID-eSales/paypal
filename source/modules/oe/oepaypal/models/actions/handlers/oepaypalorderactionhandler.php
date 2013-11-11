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
 * PayPal order action class
 */
abstract class oePayPalOrderActionHandler
{
    /**
     * @var object
     */
    protected $_oData = null;

    /**
     * @var oePayPalService
     */
    protected $_oPayPalService = null;

    /**
     * PayPal order
     *
     * @var oePayPalPayPalOrder
     */
    protected $_oPayPalRequestBuilder = null;

    /**
     * @param object $oData
     */
    function __construct( $oData )
    {
        $this->_oData = $oData;
    }

    /**
     * Returns Data object
     *
     * @return object
     */
    public function getData()
    {
        return $this->_oData;
    }

    /**
     * Sets PayPal request builder
     *
     * @param oePayPalPayPalRequestBuilder $oBuilder
     */
    public function setPayPalRequestBuilder( $oBuilder )
    {
        $this->_oPayPalRequestBuilder = $oBuilder;
    }

    /**
     * Returns PayPal request builder
     *
     * @return oePayPalPayPalRequestBuilder
     */
    public function getPayPalRequestBuilder()
    {
        if ( $this->_oPayPalRequestBuilder === null ) {
            $this->_oPayPalRequestBuilder = oxNew( 'oePayPalPayPalRequestBuilder' );
        }
        return $this->_oPayPalRequestBuilder;
    }

    /**
     * Sets PayPal service
     *
     * @param oePayPalService $oService
     */
    public function setPayPalService( $oService )
    {
        $this->_oPayPalService = $oService;
    }

    /**
     * Returns PayPal service
     *
     * @return oePayPalService
     */
    public function getPayPalService()
    {
        if ( $this->_oPayPalService === null ) {
            $this->_oPayPalService = oxNew( 'oePayPalService' );
        }
        return $this->_oPayPalService;
    }
}