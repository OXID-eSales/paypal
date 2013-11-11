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
 * PayPal order action void class
 */
class oePayPalOrderVoidActionHandler extends oePayPalOrderActionHandler
{
    /**
     * PayPal Request
     *
     * @var oePayPalRequest
     */
    protected $_oPayPalRequest = null;

    /**
     * Returns PayPal response; initiates if not set
     *
     * @return mixed
     */
    public function getPayPalResponse()
    {
        $oService = $this->getPayPalService();
        $oRequest = $this->getPayPalRequest();
        return $oService->doVoid( $oRequest );
    }

    /**
     * Returns PayPal request; initiates if not set
     *
     * @return oePayPalPayPalRequest
     */
    public function getPayPalRequest()
    {
        if ( is_null( $this->_oPayPalRequest ) ) {
            $oRequestBuilder = $this->getPayPalRequestBuilder();

            $oData = $this->getData();

            $oRequestBuilder->setAuthorizationId( $oData->getAuthorizationId() );
            $oRequestBuilder->setAmount( $oData->getAmount(), $oData->getCurrency() );
            $oRequestBuilder->setComment( $oData->getComment() );

            $this->_oPayPalRequest = $oRequestBuilder->getRequest();
        }

        return $this->_oPayPalRequest;
    }

    /**
     * Sets PayPal request
     *
     * @param $oPayPalRequest
     */
    public function setPayPalRequest( $oPayPalRequest )
    {
        $this->_oPayPalRequest = $oPayPalRequest;
    }
}