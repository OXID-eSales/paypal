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
 * oxViewConfig class wrapper for PayPal module
 */
class oePayPalOxViewConfig extends oePayPalOxViewConfig_parent
{
    protected $_oPayPalConfig = null;

    /**
     * PayPal payment object
     * @var bool
     */
    protected $_oPayPalPayment = null;

    /**
     * Status if Express checkout is ON
     * @var bool
     */
    protected $_blExpressCheckoutEnabled = null;

    /**
     * Status if Standard checkout is ON
     * @var bool
     */
    protected $_blStandardCheckoutEnabled = null;

    /**
     * Status if PayPal is ON
     * @var bool
     */
    protected $_blPayPalEnabled = null;

    /**
     * PayPal Payment Validator object
     * @var oePayPalPaymentValidator
     */
    protected $_oPaymentValidator = null;

    /**
     * @param oePayPalPaymentValidator $oPaymentValidator
     */
    public function setPaymentValidator( $oPaymentValidator )
    {
        $this->_oPaymentValidator = $oPaymentValidator;
    }

    /**
     * @return oePayPalPaymentValidator
     */
    public function getPaymentValidator()
    {
        if ( is_null( $this->_oPaymentValidator ) ) {
            $this->setPaymentValidator( oxNew( 'oePayPalPaymentValidator' ) );
        }
        return $this->_oPaymentValidator;
    }

    /**
     * Returns TRUE if express checkout is enabled
     * Does payment amount or user country/group check.
     *
     * @return bool
     */
    public function isExpressCheckoutEnabled()
    {
        if ( $this->_blExpressCheckoutEnabled === null ) {
            $this->_blExpressCheckoutEnabled = false;
            if ( $this->_getPayPalConfig()->isExpressCheckoutEnabled() ) {
                $oUser = $this->getUser();
                $oValidator = $this->getPaymentValidator();
                $oValidator->setUser( $oUser );
                $oValidator->setConfig( $this->getConfig() );
                $oValidator->setCheckCountry( false );

                $this->_blExpressCheckoutEnabled = $oValidator->isPaymentValid();
            }

        }
        return $this->_blExpressCheckoutEnabled;
    }

    /**
     * Returns TRUE if express checkout and displaying it in mini basket is enabled
     *
     * @return bool
     */
    public function isExpressCheckoutEnabledInMiniBasket()
    {
        $blExpressCheckoutEnabledInMiniBasket = false;
        if ( $this->isExpressCheckoutEnabled() && $this->_getPayPalConfig()->isExpressCheckoutInMiniBasketEnabled() ) {
            $blExpressCheckoutEnabledInMiniBasket = true;
        }

        return $blExpressCheckoutEnabledInMiniBasket;
    }

    /**
     * Returns TRUE if express checkout is enabled
     * Does payment amount or user country/group check.
     *
     * @return bool
     */
    public function isExpressCheckoutEnabledInDetails()
    {
        $blExpressCheckoutEnabledInDetails = false;
        if ( $this->isExpressCheckoutEnabled() && $this->_getPayPalConfig()->isExpressCheckoutInDetailsPage() ) {
            $blExpressCheckoutEnabledInDetails = true;
        }

        return $blExpressCheckoutEnabledInDetails;
    }

    /**
     * Returns TRUE if standard checkout is enabled.
     * Does payment amount or user country/group check.
     *
     * @return bool
     */
    public function isStandardCheckoutEnabled()
    {
        if ( $this->_blStandardCheckoutEnabled === null ) {
            $this->_blStandardCheckoutEnabled = false;
            if ( $this->_getPayPalConfig()->isStandardCheckoutEnabled() ) {
                $oUser = $this->getUser();
                $oValidator = $this->getPaymentValidator();
                $oValidator->setUser( $oUser );
                $oValidator->setConfig( $this->getConfig() );

                $this->_blStandardCheckoutEnabled = $oValidator->isPaymentValid();
            }
        }
        return $this->_blStandardCheckoutEnabled;
    }

    /**
     * Checks if PayPal standard or express checkout is enabled.
     * Does not do payment amount or user country/group check.
     *
     * @return bool
     */
    public function isPayPalActive()
    {
        return $this->getPaymentValidator()->isPaymentActive();
    }

    /**
     * Returns PayPal payment description text
     *
     * @return string
     */
    public function getPayPalPaymentDescription()
    {
        $sDesc = "";
        if ( ( $oPayPalPayment = $this->getPayPalPayment() ) ) {
            $sDesc = $oPayPalPayment->oxpayments__oxlongdesc->getRawValue();
        }
        return $sDesc;
    }

    /**
     * Returns PayPal payment object
     *
     * @return oxPayment
     */
    public function getPayPalPayment()
    {
        if ( $this->_oPayPalPayment === null ) {
            $this->_oPayPalPayment = false;
            $oPayPalPayment = oxNew( "oxpayment" );

            // payment is not available/active?
            if ( $oPayPalPayment->load( "oxidpaypal" ) && $oPayPalPayment->oxpayments__oxactive->value ) {
                $this->_oPayPalPayment = $oPayPalPayment;
            }
        }

        return $this->_oPayPalPayment;
    }

    /**
     * Returns state if order info should be send to PayPal
     *
     * @return bool
     */
    public function sendOrderInfoToPayPal()
    {
        return $this->_getPayPalConfig()->sendOrderInfoToPayPal();
    }

    /**
     * Returns default (on/off) state if order info should be send to PayPal
     *
     * @return bool
     */
    public function sendOrderInfoToPayPalDefault()
    {
        return $this->_getPayPalConfig()->sendOrderInfoToPayPalDefault();
    }

    /**
     * Returns PayPal config
     *
     * @return oePayPalConfig
     */
    protected function _getPayPalConfig()
    {
        if ( is_null( $this->_oPayPalConfig ) ) {
            $this->_oPayPalConfig = oxNew( "oePayPalConfig" );
        }

        return $this->_oPayPalConfig;
    }

    /**
     * Returns current URL
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_getPayPalConfig()->getCurrentUrl();
    }

    /**
     * Check if module is active.
     *
     * @param string $sModuleId module id.
     * @param string $sVersionFrom module from version.
     * @param string $sVersionTo module to version.
     *
     * @return  bool
     */
    public function oePayPalIsModuleActive( $sModuleId, $sVersionFrom = null, $sVersionTo = null )
    {
        $blModuleIsActive = false;

        $aModules = $this->getConfig()->getConfigParam( 'aModules' );

        if ( is_array( $aModules ) ) {
            $blModuleIsActive = $this->_oePayPalModuleExists( $sModuleId, $aModules );

            if ( $blModuleIsActive ) {
                $blModuleIsActive = $this->_oePayPalIsModuleEnabled( $sModuleId ) && $this->_oePayPalIsModuleVersionCorrect( $sModuleId, $sVersionFrom, $sVersionTo );
            }

        }

        return $blModuleIsActive;
    }

    /**
     * Checks if module exists.
     *
     * @param $sModuleId
     * @param $aModules
     * @return bool
     */
    protected function _oePayPalModuleExists( $sModuleId, $aModules )
    {
        $blModuleExists = false;
        foreach ( $aModules as $sExtendPath ) {
            if ( false !== strpos( $sExtendPath, '/' . $sModuleId . '/' ) ) {
                $blModuleExists = true;
                break;
            }
        }
        return $blModuleExists;
    }

    /**
     * Checks whether module is enabled.
     *
     * @param $sModuleId
     * @return bool
     */
    protected function _oePayPalIsModuleEnabled( $sModuleId )
    {
        $blModuleIsActive = false;

        $aDisabledModules = $this->getConfig()->getConfigParam( 'aDisabledModules' );
        if ( !( is_array( $aDisabledModules ) && in_array( $sModuleId, $aDisabledModules ) ) ) {
            $blModuleIsActive = true;
        }
        return $blModuleIsActive;
    }

    /**
     * Checks whether module version is between given range.
     *
     * @param $sModuleId
     * @param $sVersionFrom
     * @param $sVersionTo
     * @return bool
     */
    protected function _oePayPalIsModuleVersionCorrect( $sModuleId, $sVersionFrom, $sVersionTo )
    {
        $blModuleIsActive = true;

        $aModuleVersions = $this->getConfig()->getConfigParam( 'aModuleVersions' );

        if ( $sVersionFrom && !version_compare( $aModuleVersions[$sModuleId], $sVersionFrom, '>=' ) ) {
            $blModuleIsActive = false;
        }

        if ( $blModuleIsActive && $sVersionTo && !version_compare( $aModuleVersions[$sModuleId], $sVersionTo, '<' ) ) {
            $blModuleIsActive = false;
        }

        return $blModuleIsActive;
    }
}