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
 * @copyright (C) OXID eSales AG 2003-2014
 */


if (!class_exists('oePayPalOxBasket_parent')) {
    class oePayPalOxBasket_parent extends oxBasket
    {
    }
}

/**
 * Testing oePayPalSetExpressCheckoutRequestBuilder class.
 */
class Unit_oePayPal_Models_PayPalRequest_oePayPalSetExpressCheckoutRequestBuilderTest extends OxidTestCase
{
    /**
     */
    public function testAddBaseParams()
    {
        $aExpectedParams = array(
            'CALLBACKVERSION'                => '84.0',
            'LOCALECODE'                     => 'OEPAYPAL_LOCALE',
            'SOLUTIONTYPE'                   => 'Sole',
            'BRANDNAME'                      => 'ShopName',
            'CARTBORDERCOLOR'                => 'BorderColor',
            'LOGOIMG'                        => 'LogoImg',
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'TransactionMode',
            'RETURNURL'                      => null,
            'CANCELURL'                      => null,
        );

        $aConfigMethodValues = array(
            'getBrandName'      => 'ShopName',
            'getBorderColor'    => 'BorderColor',
            'getLogoUrl'        => 'LogoImg',
            'isGuestBuyEnabled' => true,
        );
        $oConfig = $this->_createStub('oePayPalConfig', $aConfigMethodValues);

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setPayPalConfig($oConfig);
        $oBuilder->setTransactionMode('TransactionMode');
        $oBuilder->addBaseParams();

        $this->_assertArraysEqual($aExpectedParams, $oBuilder->getPayPalRequest()->getData());
    }

    public function testAddBaseParams_NoLogoImg_NoLogoParamSet()
    {
        $aConfigMethodValues = array(
            'getBrandName'      => 'ShopName',
            'getBorderColor'    => 'BorderColor',
            'isGuestBuyEnabled' => true,
            'getLogoUrl'        => null,
        );
        $oConfig = $this->_createStub('oePayPalConfig', $aConfigMethodValues);

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setPayPalConfig($oConfig);
        $oBuilder->addBaseParams();

        $this->assertNotContains('LOGOIMG', $oBuilder->getPayPalRequest()->getData());
    }

    /**
     *
     * @expectedException oePayPalMissingParameterException
     */
    public function testAddBaseParams_NoConfigSet_ExceptionThrown()
    {
        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->addBaseParams();
    }

    public function testAddBaseParams_CancelUrlSet_ParameterNotNull()
    {
        $aExpectedParams = array(
            'CANCELURL' => 'cancelUrl',
        );

        $oConfig = new oePayPalConfig();

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setPayPalConfig($oConfig);
        $oBuilder->setCancelUrl('cancelUrl');
        $oBuilder->addBaseParams();

        $this->_assertArraysContains($aExpectedParams, $oBuilder->getPayPalRequest()->getData());
    }

    public function testAddBaseParams_ReturnUrlSet_ParameterNotNull()
    {
        $aExpectedParams = array(
            'RETURNURL' => 'returnUrl',
        );

        $oConfig = new oePayPalConfig();

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setPayPalConfig($oConfig);
        $oBuilder->setReturnUrl('returnUrl');
        $oBuilder->addBaseParams();

        $this->_assertArraysContains($aExpectedParams, $oBuilder->getPayPalRequest()->getData());
    }

    protected function _getBasketStub($aParams = array())
    {
        $aBasketMethodValues = array(
            'isVirtualPayPalBasket'              => true,
            'getPrice'                           => new oxPrice(),
            'getPayPalBasketVatValue'            => '88.88',
            'isCalculationModeNetto'             => true,
            'getTransactionMode'                 => '88.88',
            'getSumOfCostOfAllItemsPayPalBasket' => '77.77',
            'getDeliveryCosts'                   => '66.66',
            'getDiscountSumPayPalBasket'         => '55.55',
            'getShippingId'                      => null,
        );

        foreach ($aParams as $sKey => $sValue) {
            $aBasketMethodValues[$sKey] = $sValue;
        }

        return $this->_createStub('oxBasket', $aBasketMethodValues);
    }

    public function testSetBasket_AllParamsSet()
    {
        $aExpectedParams = array(
            'NOSHIPPING'                    => '1',
            'REQCONFIRMSHIPPING'            => '0',
            'PAYMENTREQUEST_0_AMT'          => '99.99',
            'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
            'PAYMENTREQUEST_0_TAXAMT'       => '88.88',
            'PAYMENTREQUEST_0_ITEMAMT'      => '77.77',
            'PAYMENTREQUEST_0_SHIPPINGAMT'  => '66.66',
            'PAYMENTREQUEST_0_SHIPDISCAMT'  => '-55.55',
            'L_SHIPPINGOPTIONISDEFAULT0'    => 'true',
            'L_SHIPPINGOPTIONNAME0'         => '#1',
            'L_SHIPPINGOPTIONAMOUNT0'       => '66.66',
        );

        $oPrice = $this->_createStub('oxPrice', array('getBruttoPrice' => '99.99'));

        $aBasketMethodValues = array('getPrice' => $oPrice);
        $oBasket = $this->_getBasketStub($aBasketMethodValues);

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setBasket($oBasket);
        $oBuilder->addBasketParams();

        $this->_assertArraysEqual($aExpectedParams, $oBuilder->getPayPalRequest()->getData());
    }


    /**
     */
    public function testSetBasket_OnUpdateCalledOnBasket()
    {
        $oBasket = $this->getMock('oxBasket', array('onUpdate', 'calculateBasket', 'isVirtualPayPalBasket', 'getSumOfCostOfAllItemsPayPalBasket', 'getDiscountSumPayPalBasket'));
        $oBasket->expects($this->at(0))->method('onUpdate');
        $oBasket->expects($this->at(1))->method('calculateBasket');
        $oBasket->expects($this->atLeastOnce())->method('isVirtualPayPalBasket');
        $oBasket->expects($this->atLeastOnce())->method('getSumOfCostOfAllItemsPayPalBasket');
        $oBasket->expects($this->atLeastOnce())->method('getDiscountSumPayPalBasket');

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setBasket($oBasket);
        $oBuilder->addBasketParams();

        $oPayPalService = $this->getMock("oePayPalService", array("setParameter"));
        $oPayPalService->expects($this->any())->method("setParameter");
    }

    public function testSetBasket_NotVirtualBasket_ShippingReconfirmNotSet()
    {
        $aBasketMethodValues = array(
            'isVirtualPayPalBasket' => false,
        );
        $oBasket = $this->_getBasketStub($aBasketMethodValues);

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setBasket($oBasket);
        $oBuilder->addBasketParams();

        $this->assertNotContains('REQCONFIRMSHIPPING', $oBuilder->getPayPalRequest()->getData());
    }

    public function testSetBasket_NotNettoMode_TaxAmountNotSet()
    {
        $aBasketMethodValues = array(
            'isCalculationModeNetto' => false,
        );
        $oBasket = $this->_getBasketStub($aBasketMethodValues);

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setBasket($oBasket);
        $oBuilder->addBasketParams();

        $this->assertNotContains('PAYMENTREQUEST_0_TAXAMT', $oBuilder->getPayPalRequest()->getData());
    }

    /**
     * @expectedException oePayPalMissingParameterException
     */
    public function testSetBasket_NoConfigSet_ExceptionThrown()
    {
        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->addBasketParams();
    }

    public function testSetDescription()
    {
        $aExpectedParams = array(
            'PAYMENTREQUEST_0_DESC'   => 'ShopName 99.99 EUR',
            'PAYMENTREQUEST_0_CUSTOM' => 'ShopName 99.99 EUR',
        );
        $aBasketMethodValues = array(
            'getFPrice' => '99.99',
        );
        $oBasket = $this->_createStub('oxBasket', $aBasketMethodValues);

        $aConfigMethodValues = array('getBrandName' => 'ShopName');
        $oConfig = $this->_createStub('oePayPalConfig', $aConfigMethodValues);

        $oLang = $this->_createStub('oxLang', array('translateString' => '%s %s %s'));

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setLang($oLang);
        $oBuilder->setBasket($oBasket);
        $oBuilder->setPayPalConfig($oConfig);
        $oBuilder->addDescriptionParams();

        $this->_assertArraysEqual($aExpectedParams, $oBuilder->getPayPalRequest()->getData());
    }

    /**
     * @expectedException oePayPalMissingParameterException
     */
    public function testSetDescription_NoConfigSet_ExceptionThrown()
    {
        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->addDescriptionParams();
    }

    public function testAddBasketItemParams_WithItems()
    {
        $aExpectedParams = array(
            'L_PAYMENTREQUEST_0_NAME0'    => 'BasketItemTitle',
            'L_PAYMENTREQUEST_0_AMT0'     => '99.99',
            'L_PAYMENTREQUEST_0_QTY0'     => '88',
            'L_PAYMENTREQUEST_0_ITEMURL0' => 'BasketItemUrl',
            'L_PAYMENTREQUEST_0_NUMBER0'  => 'BasketItemArtNum',
            'L_PAYMENTREQUEST_0_NAME1'    => 'BasketItemTitle',
            'L_PAYMENTREQUEST_0_AMT1'     => '99.99',
            'L_PAYMENTREQUEST_0_QTY1'     => '88',
            'L_PAYMENTREQUEST_0_ITEMURL1' => 'BasketItemUrl',
            'L_PAYMENTREQUEST_0_NUMBER1'  => 'BasketItemArtNum',
        );

        $oArticle = new oxArticle();
        $oArticle->oxarticles__oxartnum = new oxField('BasketItemArtNum');

        $aPriceMethodValues = array(
            'getPrice' => '99.99',
        );
        $oPrice = $this->_createStub('oxPrice', $aPriceMethodValues);

        $aBasketItemMethodValues = array(
            'getTitle'     => 'BasketItemTitle',
            'getUnitPrice' => $oPrice,
            'getAmount'    => '88',
            'getLink'      => 'BasketItemUrl',
            'getArticle'   => $oArticle,
        );

        $oBasketItem = $this->_createStub('oxBasketItem', $aBasketItemMethodValues);
        $aBasketItems = array($oBasketItem, $oBasketItem);

        $aBasketMethodValues = array(
            'getContents'                => $aBasketItems,
            'getPayPalPaymentCosts'      => 0,
            'getPayPalWrappingCosts'     => 0,
            'getPayPalGiftCardCosts'     => 0,
            'getPayPalTsProtectionCosts' => 0,
        );
        $oBasket = $this->_createStub('oxBasket', $aBasketMethodValues);

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setBasket($oBasket);
        $oBuilder->addBasketItemParams();

        $this->_assertArraysEqual($aExpectedParams, $oBuilder->getPayPalRequest()->getData());
    }

    public function testAddBasketItemParams_WithPayment()
    {
        $aExpectedParams = array(
            'L_PAYMENTREQUEST_0_NAME0' => 'OEPAYPAL_SURCHARGE OEPAYPAL_TYPE_OF_PAYMENT',
            'L_PAYMENTREQUEST_0_AMT0'  => '66.74',
            'L_PAYMENTREQUEST_0_QTY0'  => 1,
        );

        $aBasketMethodValues = array(
            'getContents'                => array(),
            'getPayPalPaymentCosts'      => 66.74,
            'getPayPalWrappingCosts'     => 0,
            'getPayPalGiftCardCosts'     => 0,
            'getPayPalTsProtectionCosts' => 0,
        );
        $oBasket = $this->_createStub('oxBasket', $aBasketMethodValues);

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setBasket($oBasket);
        $oBuilder->addBasketItemParams();

        $this->_assertArraysEqual($aExpectedParams, $oBuilder->getPayPalRequest()->getData());
    }

    public function testAddBasketItemParams_WithWrapping()
    {
        $aExpectedParams = array(
            'L_PAYMENTREQUEST_0_NAME0' => 'OEPAYPAL_GIFTWRAPPER',
            'L_PAYMENTREQUEST_0_AMT0'  => '100.00',
            'L_PAYMENTREQUEST_0_QTY0'  => 1,
        );

        $aBasketMethodValues = array(
            'getContents'                => array(),
            'getPayPalPaymentCosts'      => 0,
            'getPayPalWrappingCosts'     => 100,
            'getPayPalGiftCardCosts'     => 0,
            'getPayPalTsProtectionCosts' => 0,
        );
        $oBasket = $this->_createStub('oxBasket', $aBasketMethodValues);

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setBasket($oBasket);
        $oBuilder->addBasketItemParams();

        $this->_assertArraysEqual($aExpectedParams, $oBuilder->getPayPalRequest()->getData());
    }

    public function testAddBasketItemParams_WithGiftCard()
    {
        $aExpectedParams = array(
            'L_PAYMENTREQUEST_0_NAME0' => 'OEPAYPAL_GREETING_CARD',
            'L_PAYMENTREQUEST_0_AMT0'  => '100.99',
            'L_PAYMENTREQUEST_0_QTY0'  => 1,
        );

        $aBasketMethodValues = array(
            'getContents'                => array(),
            'getPayPalPaymentCosts'      => 0,
            'getPayPalWrappingCosts'     => 0,
            'getPayPalGiftCardCosts'     => 100.99,
            'getPayPalTsProtectionCosts' => 0,
        );
        $oBasket = $this->_createStub('oxBasket', $aBasketMethodValues);

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setBasket($oBasket);
        $oBuilder->addBasketItemParams();

        $this->_assertArraysEqual($aExpectedParams, $oBuilder->getPayPalRequest()->getData());
    }

    public function testAddBasketItemParams_WithTrustedShop()
    {
        $aExpectedParams = array(
            'L_PAYMENTREQUEST_0_NAME0' => 'OEPAYPAL_TRUSTED_SHOP_PROTECTION',
            'L_PAYMENTREQUEST_0_AMT0'  => '100.99',
            'L_PAYMENTREQUEST_0_QTY0'  => 1,
        );

        $aBasketMethodValues = array(
            'getContents'                => array(),
            'getPayPalPaymentCosts'      => 0,
            'getPayPalWrappingCosts'     => 0,
            'getPayPalGiftCardCosts'     => 0,
            'getPayPalTsProtectionCosts' => 100.99,
        );
        $oBasket = $this->_createStub('oxBasket', $aBasketMethodValues);

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setBasket($oBasket);
        $oBuilder->addBasketItemParams();

        $this->_assertArraysEqual($aExpectedParams, $oBuilder->getPayPalRequest()->getData());
    }

    public function testAddBasketGrandTotalParams()
    {
        $aExpectedParams = array(
            'L_PAYMENTREQUEST_0_NAME0' => 'OEPAYPAL_GRAND_TOTAL',
            'L_PAYMENTREQUEST_0_AMT0'  => '99.99',
            'L_PAYMENTREQUEST_0_QTY0'  => 1,
        );

        $aBasketMethodValues = array(
            'getSumOfCostOfAllItemsPayPalBasket' => '99.99'
        );
        $oBasket = $this->_createStub('oxBasket', $aBasketMethodValues);

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setBasket($oBasket);
        $oBuilder->addBasketGrandTotalParams();

        $this->_assertArraysEqual($aExpectedParams, $oBuilder->getPayPalRequest()->getData());
    }

    public function testAddAddressParams_SelectedAddressIdNotSet_TakeInfoFromUser()
    {
        $aExpectedParams = array(
            'EMAIL'                              => 'test@test.com',
            'PAYMENTREQUEST_0_SHIPTONAME'        => 'FirstName LastName',
            'PAYMENTREQUEST_0_SHIPTOSTREET'      => 'Street StreetNr',
            'PAYMENTREQUEST_0_SHIPTOCITY'        => 'City',
            'PAYMENTREQUEST_0_SHIPTOZIP'         => 'Zip',
            'PAYMENTREQUEST_0_SHIPTOPHONENUM'    => 'PhoneNum',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => null,
        );

        $aUserMethodValues = array(
            'getSelectedAddressId' => null,
        );
        $oUser = $this->_createStub('oxUser', $aUserMethodValues);
        $oUser->oxuser__oxusername = new oxField('test@test.com');
        $oUser->oxuser__oxfname = new oxField('FirstName');
        $oUser->oxuser__oxlname = new oxField('LastName');
        $oUser->oxuser__oxstreet = new oxField('Street');
        $oUser->oxuser__oxstreetnr = new oxField('StreetNr');
        $oUser->oxuser__oxcity = new oxField('City');
        $oUser->oxuser__oxzip = new oxField('Zip');
        $oUser->oxuser__oxfon = new oxField('PhoneNum');
        $oUser->oxuser__oxcity = new oxField('City');

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setUser($oUser);
        $oBuilder->addAddressParams();

        $this->_assertArraysEqual($aExpectedParams, $oBuilder->getPayPalRequest()->getData());
    }

    /**
     * @return oePayPalCheckoutRequestBuilder
     */
    protected function _getPayPalRequestBuilder()
    {
        $oLang = $this->getMock('oxLang', array('translateString'));
        $oLang->expects($this->any())
            ->method('translateString')
            ->will($this->returnArgument(0));

        $oBuilder = new oePayPalSetExpressCheckoutRequestBuilder();
        $oBuilder->setLang($oLang);

        return $oBuilder;
    }

    /**
     * Checks whether array length are equal and array keys and values are equal independent on keys position
     *
     * @param $aExpected
     * @param $aResult
     */
    protected function _assertArraysEqual($aExpected, $aResult)
    {
        $this->_assertArraysContains($aExpected, $aResult);
        $this->assertEquals(count($aExpected), count($aResult));
    }

    /**
     * Checks whether array array keys and values are equal independent on keys position
     *
     * @param $aExpected
     * @param $aResult
     */
    protected function _assertArraysContains($aExpected, $aResult)
    {
        foreach ($aExpected as $sKey => $sValue) {
            $this->assertArrayHasKey($sKey, $aResult, "Key not found: $sKey");
            $this->assertEquals($sValue, $aResult[$sKey], "Key '$sKey' value is not equal to '$sValue'");
        }
    }

    public function testGetMaxDeliveryAmount_notSet_0()
    {
        $oBuilder = new oePayPalSetExpressCheckoutRequestBuilder();
        $this->assertEquals(0, $oBuilder->getMaxDeliveryAmount());
    }

    public function testGetMaxDeliveryAmount_setValue_setValue()
    {
        $oBuilder = new oePayPalSetExpressCheckoutRequestBuilder();
        $oBuilder->setMaxDeliveryAmount(13);
        $this->assertEquals(13, $oBuilder->getMaxDeliveryAmount());
    }
}