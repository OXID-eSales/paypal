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
 * PayPal IPN Payment validator class
 */
class oePayPalIPNPaymentValidator
{
    /**
     * Language object to get translations from.
     * @var object
     */
    protected $_oLang = null;

    /**
     * Payment created from PayPal request.
     * @var oePayPalOrderPayment
     */
    protected $_oRequestPayment = null;

    /**
     * Payment created by PayPal request id.
     * @var oePayPalOrderPayment
     */
    protected $_oOrderPayment = null;

    /**
     * Sets language object to get translations from.
     * @param object $oLang get translations from.
     */
    public function setLang( $oLang )
    {
        $this->_oLang = $oLang;
    }

    /**
     * Gets language object to get translations from.
     * @return object
     */
    public function getLang()
    {
        return $this->_oLang;
    }

    /**
     * @param oePayPalOrderPayment $oRequestPayment
     */
    public function setRequestOrderPayment( $oRequestPayment )
    {
        $this->_oRequestPayment = $oRequestPayment;
    }

    /**
     * @return oePayPalOrderPayment
     */
    public function getRequestOrderPayment()
    {
        return $this->_oRequestPayment;
    }

    /**
     * @param oePayPalOrderPayment $oPayment
     */
    public function setOrderPayment( $oPayment )
    {
        $this->_oOrderPayment = $oPayment;
    }

    /**
     * @return oePayPalOrderPayment
     */
    public function getOrderPayment()
    {
        return $this->_oOrderPayment;
    }

    /**
     * @return string
     */
    public function getValidationFailureMessage()
    {
        $oRequestPayment = $this->getRequestOrderPayment();
        $oOrderPayment = $this->getOrderPayment();

        $sCurrencyPayPal      = $oRequestPayment->getCurrency();
        $dPricePayPal         = $oRequestPayment->getAmount();

        $sCurrencyPayment   = $oOrderPayment->getCurrency();
        $dAmountPayment     = $oOrderPayment->getAmount();

        $oLang = $this->getLang();
        $sValidationMessage = $oLang->translateString( 'OEPAYPAL_PAYMENT_INFORMATION' ) .': '. $dAmountPayment .' '. $sCurrencyPayment .'. '. $oLang->translateString( 'OEPAYPAL_INFORMATION' ). ': '. $dPricePayPal .' '. $sCurrencyPayPal .'.';

        return $sValidationMessage;
    }

    /**
     * Check if PayPal response fits payment information.
     *
     * @return bool
     */
    public function isValid()
    {
        $oRequestPayment = $this->getRequestOrderPayment();
        $oOrderPayment = $this->getOrderPayment();

        $sCurrencyPayPal      = $oRequestPayment->getCurrency();
        $dPricePayPal         = $oRequestPayment->getAmount();

        $sCurrencyPayment   = $oOrderPayment->getCurrency();
        $dAmountPayment     = $oOrderPayment->getAmount();

        return ( $sCurrencyPayPal == $sCurrencyPayment && $dPricePayPal == $dAmountPayment );
    }
}