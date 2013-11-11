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
 * PayPal order action factory class
 */
class oePayPalOrderActionData
{

    /**
     * Request object
     *
     * @var oePayPalRequest
     */
    protected $_oRequest = null;

    /**
     * Order object
     *
     * @var oePayPalPayPalOrder
     */
    protected $_oOrder = null;

    /**
     * Sets dependencies
     *
     * @param oePayPalRequest $oRequest
     * @param oePayPalPayPalOrder $oOrder
     */
    public function __construct( $oRequest, $oOrder )
    {
        $this->_oRequest = $oRequest;
        $this->_oOrder = $oOrder;
    }

    /**
     * Returns Request object
     *
     * @return oePayPalRequest
     */
    public function getRequest()
    {
        return $this->_oRequest;
    }

    /**
     * Returns PayPal Order object
     *
     * @return oePayPalRequest
     */
    public function getOrder()
    {
        return $this->_oOrder;
    }

    /**
     * returns action amount
     *
     * @return string
     */
    public function getAuthorizationId()
    {
        return $this->getOrder()->oxorder__oxtransid->value;
    }

    /**
     * returns comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->getRequest()->getRequestParameter( 'action_comment' );
    }

    /**
     * Returns order status
     *
     * @return string
     */
    public function getOrderStatus()
    {
        return $this->getRequest()->getRequestParameter( 'order_status' );
    }

}