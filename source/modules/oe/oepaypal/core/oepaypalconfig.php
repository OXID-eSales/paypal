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
 * PayPal config class
 */
class oePayPalConfig
{
    /**
     * PayPal module id.
     * @var string
     */
    protected $_sPayPalId = 'oepaypal';

    /**
     * PayPal host.
     * @var string
     */
    protected $_sPayPalHost = 'api-3t.paypal.com';

    /**
     * PayPal sandbox host.
     * @var string
     */
    protected $_sPayPalSandboxHost = 'api-3t.sandbox.paypal.com';

    /**
     * PayPal sandbox Url where user must be redirected after his session gets PayPal token.
     * @var string
     */
    protected $_sPayPalSandboxUrl = 'https://www.sandbox.paypal.com/webscr';

    /**
     * PayPal Url where user must be redirected after his session gets PayPal token.
     * @var string
     */
    protected $_sPayPalUrl = 'https://www.paypal.com/webscr';

    /**
     * PayPal sandbox API url.
     * @var string
     */
    protected $_sPayPalSandboxApiUrl = 'https://api-3t.sandbox.paypal.com/nvp';

    /**
     * PayPal API url.
     * @var string
     */
    protected $_sPayPalApiUrl = 'https://api-3t.paypal.com/nvp';

    /**
     * Maximum possible delivery costs value.
     * @var double
     */
    protected $_dMaxDeliveryAmount = 30;

    /**
     * Please do not change this place.
     * It is important to guarantee the future development of this OXID eShop extension and to keep it free of charge.
     * Thanks!
     * @var array Partner codes based on edition
     */
    protected $_aPartnerCodes = array(
        'EE' => 'OXID_Cart_EnterpriseECS',
        'PE' => 'OXID_Cart_ProfessionalECS',
        'CE' => 'OXID_Cart_CommunityECS',
    );

    /**
     * Return PayPal module id.
     * @return string
     */
    public function getModuleId()
    {
        return $this->_sPayPalId;
    }

    /**
     * Sets PayPal host.
     * @param string $sPayPalHost
     */
    public function setPayPalHost( $sPayPalHost )
    {
        $this->_sPayPalHost = $sPayPalHost;
    }

    /**
     * Returns PayPal host.
     *
     * @return string
     */
    public function getPayPalHost()
    {
        $sHost = $this->_getConfig()->getConfigParam( 'sPayPalHost' );
        if ( $sHost ) {
            $this->setPayPalHost( $sHost );
        }
        return $this->_sPayPalHost;
    }

    /**
     * Sets PayPal sandbox host.
     *
     * @param string $sPayPalSandboxHost
     */
    public function setPayPalSandboxHost( $sPayPalSandboxHost )
    {
        $this->_sPayPalSandboxHost = $sPayPalSandboxHost;
    }

    /**
     * Returns PayPal sandbox host.
     *
     * @return string
     */
    public function getPayPalSandboxHost()
    {
        $sHost = $this->_getConfig()->getConfigParam( 'sPayPalSandboxHost' );
        if ( $sHost ) {
            $this->setPayPalSandboxHost( $sHost );
        }
        return $this->_sPayPalSandboxHost;
    }

    /**
     * Returns PayPal OR PayPal sandbox host.
     *
     * @return string
     */
    public function getHost()
    {
        if ( $this->isSandboxEnabled() ) {
            $sUrl = $this->getPayPalSandboxHost();
        } else {
            $sUrl = $this->getPayPalHost();
        }

        return $sUrl;
    }

    /**
     *  Api Url setter
     *
     * @param string $sPayPalApiUrl
     */
    public function setPayPalApiUrl( $sPayPalApiUrl )
    {
        $this->_sPayPalApiUrl = $sPayPalApiUrl;
    }

    /**
     *  Api Url getter
     *
     * @return string
     */
    public function getPayPalApiUrl()
    {
        $sUrl = $this->_getConfig()->getConfigParam( 'sPayPalApiUrl' );
        if ( $sUrl ) {
            $this->setPayPalApiUrl( $sUrl );
        }
        return $this->_sPayPalApiUrl;
    }

    /**
     * PayPal sandbox api url setter
     *
     * @param string $sPayPalSandboxApiUrl
     */
    public function setPayPalSandboxApiUrl($sPayPalSandboxApiUrl)
    {
        $this->_sPayPalSandboxApiUrl = $sPayPalSandboxApiUrl;
    }

    /**
     * PayPal sandbox api url getter
     *
     * @return string
     */
    public function getPayPalSandboxApiUrl()
    {
        $sUrl = $this->_getConfig()->getConfigParam( 'sPayPalSandboxApiUrl' );
        if ( $sUrl ) {
            $this->setPayPalSandboxApiUrl( $sUrl );
        }
        return $this->_sPayPalSandboxApiUrl;
    }

    /**
     * Returns end point url
     *
     * @return string
     */
    public function getApiUrl()
    {
        if ( $this->isSandboxEnabled() ) {
            $sUrl = $this->getPayPalSandboxApiUrl();
        } else {
            $sUrl = $this->getPayPalApiUrl();
        }

        return $sUrl;
    }

    /**
     * PayPal Url Setter
     *
     * @param string $sPayPalUrl
     */
    public function setPayPalUrl($sPayPalUrl)
    {
        $this->_sPayPalUrl = $sPayPalUrl;
    }

    /**
     * PayPal sandbox url setter
     *
     * @param string $sPayPalSandboxUrl
     */
    public function setPayPalSandboxUrl($sPayPalSandboxUrl)
    {
        $this->_sPayPalSandboxUrl = $sPayPalSandboxUrl;
    }

    /**
     * PayPal sandbox url getter
     *
     * @return string
     */
    public function getPayPalUrl()
    {
        $sUrl = $this->_getConfig()->getConfigParam( 'sPayPalUrl' );
        if ( $sUrl ) {
            $this->setPayPalUrl( $sUrl );
        }

        return $this->_sPayPalUrl;
    }

    /**
     * PayPal sandbox url getter
     *
     * @return string
     */
    public function getPayPalSandboxUrl()
    {
        $sUrl = $this->_getConfig()->getConfigParam( 'sPayPalSandboxUrl' );
        if ( $sUrl ) {
            $this->setPayPalSandboxUrl( $sUrl );
        }
        return $this->_sPayPalSandboxUrl;
    }

    /**
     * Get PayPal url.
     * @return string
     */
    public function getUrl()
    {
        if ( $this->isSandboxEnabled() ) {
            $sUrl = $this->getPayPalSandboxUrl();
        } else {
            $sUrl = $this->getPayPalUrl();
        }

        return $sUrl;
    }

    /**
     * Returns module config parameter value
     *
     * @param string $sParamName parameter name
     *
     * @return mixed
     */
    public function getParameter( $sParamName )
    {
        return $this->_getConfig()->getConfigParam( $sParamName );
    }

    /**
     * Returns true if Express Checkout is ON
     *
     * @return bool
     */
    public function isExpressCheckoutEnabled()
    {
        return $this->getParameter( 'blOEPayPalExpressCheckout' );
    }

    /**
     * Returns true if Express Checkout is ON in mini basket
     *
     * @return bool
     */
    public function isExpressCheckoutInMiniBasketEnabled()
    {
        return $this->getParameter( 'blOEPayPalECheckoutInMiniBasket' );
    }

    /**
     * Returns true if Standard PayPal Checkout is ON
     *
     * @return bool
     */
    public function isStandardCheckoutEnabled()
    {
        return $this->getParameter( 'blOEPayPalStandardCheckout' );
    }

    /**
     * Returns true if logging request/response to PayPal is enabled
     *
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return $this->getParameter( 'blPayPalLoggerEnabled' );
    }

    /**
     * Returns Brand/Shop name [OXID ESHOP]
     *
     * @return string
     */
    public function getBrandName()
    {
        $sShopName = $this->getParameter( 'sOEPayPalBrandName' );

        if ( empty( $sShopName ) ) {
            $oShop = $this->_getConfig()->getActiveShop();
            $sShopName = $oShop->oxshops__oxname->value;
        }

        return $sShopName;
    }

    /**
     * Returns custom cart border color which is displayed in PayPal side
     *
     * @return string
     */
    public function getBorderColor()
    {
        return $this->getParameter( 'sOEPayPalBorderColor' );
    }

    /**
     * Returns TRUE if order finalization on PayPal side is on
     *
     * @return bool
     */
    public function finalizeOrderOnPayPalSide()
    {
        $blFinalize = $this->getParameter( 'blOEPayPalFinalizeOrderOnPayPal' );
        return $blFinalize !== null ? $blFinalize : false;
    }

    /**
     * Send order info to PayPal or not
     *
     * @return bool
     */
    public function sendOrderInfoToPayPal()
    {
        return $this->getParameter( 'blOEPayPalSendToPayPal' );
    }

    /**
     * Send order info to PayPal or not config's default value: checked or not
     *
     * @return bool
     */
    public function sendOrderInfoToPayPalDefault()
    {
        return $this->getParameter( 'blOEPayPalDefaultUserChoice' );
    }

    /**
     * Guest buy mode getter
     *
     * @return bool
     */
    public function isGuestBuyEnabled()
    {
        return $this->getParameter( 'blOEPayPalGuestBuyRole' );
    }

    /**
     * Returns true of GiroPay is ON (not implemented yet)
     *
     * @return bool
     */
    public function isGiroPayEnabled()
    {
        return false;
    }

    /**
     * Returns true of sandbox mode is ON
     *
     * @return bool
     */
    public function isSandboxEnabled()
    {
        return $this->getParameter( 'blOEPayPalSandboxMode' );
    }

    /**
     * Returns Empty Stock Level
     *
     * @return string
     */
    public function getEmptyStockLevel()
    {
        return $this->getParameter( 'sOEPayPalEmptyStockLevel' );
    }

    /**
     * Returns PayPal password
     *
     * @return string
     */
    public function getPassword()
    {
        if ( $this->isSandboxEnabled() ) {
            // sandbox password
            return $this->getParameter( 'sOEPayPalSandboxPassword' );
        }

        // password
        return $this->getParameter( 'sOEPayPalPassword' );
    }

    /**
     * Returns PayPal user name
     *
     * @return string
     */
    public function getUserName()
    {
        if ( $this->isSandboxEnabled() ) {
            // sandbox login
            return $this->getParameter( 'sOEPayPalSandboxUsername' );
        }

        // login
        return $this->getParameter( 'sOEPayPalUsername' );
    }

    /**
     * Returns PayPal user name
     *
     * @return string
     */
    public function getUserEmail()
    {
        if ( $this->isSandboxEnabled() ) {
            // sandbox login
            return $this->getParameter( 'sOEPayPalSandboxUserEmail' );
        }

        // login
        return $this->getParameter( 'sOEPayPalUserEmail' );
    }

    /**
     * Returns PayPal signature
     *
     * @return string
     */
    public function getSignature()
    {
        if ( $this->isSandboxEnabled() ) {
            // sandbox signature
            return  $this->getParameter( 'sOEPayPalSandboxSignature' );
        }

        // test sandbox signature
        return  $this->getParameter( 'sOEPayPalSignature' );
    }

    /**
     * Returns PayPal transaction mode
     *
     * @return string
     */
    public function getTransactionMode()
    {
        return $this->getParameter( 'sOEPayPalTransactionMode' );
    }



    /**
     * Returns redirect url.
     *
     * @param string $sToken token to append to redirect url.
     * @param string $sUserAction checkout button action - continue (standard checkout) or commit (express checkout)
     *
     * @return string
     */
    public function getPayPalCommunicationUrl( $sToken = null, $sUserAction = 'continue' )
    {
        return $this->getUrl() . '&cmd=_express-checkout&token=' . (string) $sToken . '&useraction=' . (string) $sUserAction;
    }


    /**
     * Get logo Url based on selected settings
     * Returns shop url, or false
     *
     * @return string|bool
     */
    public function getLogoUrl()
    {
        $sLogoUrl = false;

        $sLogoName = $this->_getLogoImageName();

        if ( !empty( $sLogoName ) ) {
            $oLogo = oxNew( 'oePayPalShopLogo' );
            $oLogo->setImageDir( $this->_getConfig()->getImageDir() );
            $oLogo->setImageDirUrl( $this->_getConfig()->getImageUrl() );
            $oLogo->setImageName( $sLogoName );
            $oLogo->setImageHandler( oxRegistry::get( 'oxUtilsPic' ) );

            $sLogoUrl = $oLogo->getShopLogoUrl();
        }

        return $sLogoUrl;
    }

    /**
     * Returns IPN callback url
     *
     * @return string
     */
    public function getIPNCallbackUrl()
    {
        return $this->getShopUrl() . 'index.php?cl=oePayPalIPNHandler&fnc=handleRequest&shp='.$this->_getShopId();
    }

    /**
     * Returns config sShopURL or sMallShopURL if secondary shop
     *
     * @param int  $iLang   language
     *
     * @return string
     */
    public function getShopUrl( $iLang = null )
    {
        return $this->_getConfig()->getCurrentShopUrl( $iLang, false );
    }

    /**
     * Wrapper to get language object from registry.
     * @return oxLang
     */
    public function getLang()
    {
        return oxRegistry::getLang();
    }

    /**
     * Wrapper to get utils object from registry.
     * @return oxUtils
     */
    public function getUtils()
    {
        return oxRegistry::getUtils();
    }

    /**
     * Gets if shop is coded with UTF.
     * Wrapper for config method isUtf().
     *
     * @return string
     */
    public function isUtf()
    {
        return $this->_getConfig()->isUtf();
    }

    /**
     * Returns shop charset
     *
     * @return string
     */
    public function getCharset()
    {
        $sCharset = 'UTF-8';
        if ( !$this->isUtf() ) {
            $sCharset = $this->getLang()->translateString( 'charset' );
        }
        return $sCharset;
    }

    /**
     * Returns Url for IPN response call to notify PayPal
     *
     * @return string
     */
    public function getIPNResponseUrl()
    {
        return $this->getUrl() . '&cmd=_notify-validate';
    }

    /**
     * Returns true if Express Checkout is in details page
     *
     * @return bool
     */
    public function isExpressCheckoutInDetailsPage()
    {
        return $this->getParameter('blOEPayPalECheckoutInDetails');
    }

    /**
     * Returns current URL
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return oxRegistry::get( 'oxUtilsUrl' )->getCurrentUrl();
    }

    /**
     * Returns max delivery amount.
     *
     * @return integer
     */
    public function getMaxPayPalDeliveryAmount()
    {
        $dMaxDeliveryAmount = $this->_getConfig()->getConfigParam( 'dMaxPayPalDeliveryAmount' );
        if ( !$dMaxDeliveryAmount ) {
            $dMaxDeliveryAmount = $this->_dMaxDeliveryAmount;
        }

        return $dMaxDeliveryAmount;
    }

    /**
     * Please do not change this place.
     * It is important to guarantee the future development of this OXID eShop extension and to keep it free of charge.
     * Thanks!
     *
     * @return string partner code.
     */
    public function getPartnerCode()
    {
        return $this->_aPartnerCodes[ $this->_getConfig()->getEdition() ];
    }

    /**
     * Detects device type
     *
     * @return bool
     */
    public function isDeviceMobile()
    {
        $oUserAgent = oxNew( 'oePayPalUserAgent' );
        return ( $oUserAgent->getDeviceType() == 'mobile' );
    }

    /**
     * Returns id of shipping assigned for EC for mobile devices
     *
     * @return string
     */
    public function getMobileECDefaultShippingId()
    {
        return $this->_getConfig()->getConfigParam( 'sOEPayPalMECDefaultShippingId' );
    }

    /**
     * Returns logo image name according to parameter
     *
     * @return mixed|string
     */
    protected function _getLogoImageName()
    {
        $sOption = $this->getParameter( 'sOEPayPalLogoImageOption' );
        switch ( $sOption ) {
            case 'shopLogo':
                $sLogo = $this->getParameter( 'sShopLogo' );
                break;
            case 'customLogo':
                $sLogo = $this->getParameter( 'sOEPayPalCustomShopLogoImage' );
                break;
            case 'noLogo':
            default:
                $sLogo = '';
                return $sLogo;
        }
        return $sLogo;
    }

    /**
     * Returns active shop id
     *
     * @return string
     */
    protected function _getShopId()
    {
        return $this->_getConfig()->getShopId();
    }

    /**
     * Returns oxConfig instance
     *
     * @return oxConfig
     */
    protected function _getConfig()
    {
        return oxRegistry::getConfig();
    }
}
