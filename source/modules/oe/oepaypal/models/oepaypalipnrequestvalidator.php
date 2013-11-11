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
 * PayPal IPN request validator class
 */
class oePayPalIPNRequestValidator
{
    /**
     * String PayPal receiver email. It should be same as shop owner credential for PayPal.
     * @var string
     */
    const RECEIVER_EMAIL = 'receiver_email';

    /**
     * Shop owner Email from configuration of PayPal module.
     * @var string
     */
    protected $_sShopOwnerUserName = null;

    /**
     * PayPal response if OK.
     * @var string
     */
    protected $_oPayPalResponse = null;

    /**
     * PayPal request to get email.
     * @var string
     */
    protected $_aPayPalRequest = null;

    /**
     * Set shop owner user name - payPal ID.
     * @param string $sShopOwnerUserName
     */
    public function setShopOwnerUserName( $sShopOwnerUserName )
    {
        $this->_sShopOwnerUserName = $sShopOwnerUserName;
    }

    /**
     * get shop owner user name - payPal ID.
     * @return string
     */
    public function getShopOwnerUserName()
    {
        return $this->_sShopOwnerUserName;
    }

    /**
     * Set PayPal response object.
     * @param oePayPalResponseDoVerifyWithPayPal $sPayPalResponse
     */
    public function setPayPalResponse( $sPayPalResponse )
    {
        $this->_oPayPalResponse = $sPayPalResponse;
    }

    /**
     * Get PayPal response object.
     * @return oePayPalResponseDoVerifyWithPayPal
     */
    public function getPayPalResponse()
    {
        return $this->_oPayPalResponse;
    }

    /**
     * Set PayPal request array.
     * @param array $sPayPalRequest
     */
    public function setPayPalRequest( $sPayPalRequest )
    {
        $this->_aPayPalRequest = $sPayPalRequest;
    }

    /**
     * Get PayPal request array.
     * @return array
     */
    public function getPayPalRequest()
    {
        return $this->_aPayPalRequest;
    }

    /**
     * @return array
     */
    public function getValidationFailureMessage()
    {
        $aPayPalRequest = $this->getPayPalRequest();
        $oPayPalResponse = $this->getPayPalResponse();
        $sShopOwnerUserName = $this->getShopOwnerUserName();
        $sReceiverEmailPayPal = $aPayPalRequest[ self::RECEIVER_EMAIL ];

        $aValidationMessage = array(
            'Shop owner' => (string) $sShopOwnerUserName,
            'PayPal ID' => (string) $sReceiverEmailPayPal,
            'PayPal ACK' => ( $oPayPalResponse->isPayPalAck() ? 'VERIFIED' : 'NOT VERIFIED' ),
            'PayPal Full Request' => print_r($aPayPalRequest, true),
            'PayPal Full Response' => print_r($oPayPalResponse->getData(), true),
        );
        return $aValidationMessage;
    }

    /**
     * Validate if IPN request from PayPal and to correct shop.
     *
     * @return bool
     */
    public function isValid()
    {
        $aPayPalRequest = $this->getPayPalRequest();
        $oPayPalResponse = $this->getPayPalResponse();
        $sShopOwnerUserName = $this->getShopOwnerUserName();
        $sReceiverEmailPayPal = $aPayPalRequest[ self::RECEIVER_EMAIL ];

        return ( $oPayPalResponse->isPayPalAck() &&  $sReceiverEmailPayPal == $sShopOwnerUserName );
    }
}