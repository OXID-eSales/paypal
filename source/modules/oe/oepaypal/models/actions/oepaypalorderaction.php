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
abstract class oePayPalOrderAction
{
    /**
     *
     * @var oePayPalRequest
     */
    protected $_oOrder = null;

    /**
     * @var string
     */
    protected $_sOrderStatus = null;

    /**
     * @var oePayPalOrderCaptureActionHandler
     */
    protected $_oHandler = null;

    /**
     * @param $oHandler
     * @param $oOrder
     */
    public function __construct( $oHandler, $oOrder )
    {
        $this->_oHandler = $oHandler;
        $this->_oOrder = $oOrder;
    }

    /**
     * @return oePayPalOrderCaptureActionHandler
     */
    public function getHandler()
    {
        return $this->_oHandler;
    }

    /**
     * @return oePayPalPayPalOrder
     */
    public function getOrder()
    {
        return $this->_oOrder;
    }

    /**
     * Returns formatted date
     *
     * @return string
     */
    public function getDate()
    {
        return date( 'Y-m-d H:i:s', oxRegistry::get("oxUtilsDate")->getTime() );
    }

    /**
     * Processes PayPal action
     */
    abstract public function process();
}