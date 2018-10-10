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
 * @copyright (C) OXID eSales AG 2003-2018
 */

namespace OxidEsales\PayPalModule\Tests\Unit\Model\PayPalRequest;

/**
 * Testing \OxidEsales\PayPalModule\Model\PayPalRequest\SetExpressCheckoutRequestBuilder class.
 */
class SetExpressCheckoutRequestBuilderTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     */
    public function testAddBaseParams()
    {
        $expectedParams = array(
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

        $configMethodValues = array(
            'getBrandName'      => 'ShopName',
            'getBorderColor'    => 'BorderColor',
            'getLogoUrl'        => 'LogoImg',
            'isGuestBuyEnabled' => true,
        );
        $config = $this->_createStub(\OxidEsales\PayPalModule\Core\Config::class, $configMethodValues);

        $builder = $this->getPayPalRequestBuilder();
        $builder->setPayPalConfig($config);
        $builder->setTransactionMode('TransactionMode');
        $builder->addBaseParams();

        $this->assertArraysEqual($expectedParams, $builder->getPayPalRequest()->getData());
    }

    public function testAddBaseParams_NoLogoImg_NoLogoParamSet()
    {
        $configMethodValues = array(
            'getBrandName'      => 'ShopName',
            'getBorderColor'    => 'BorderColor',
            'isGuestBuyEnabled' => true,
            'getLogoUrl'        => null,
        );
        $config = $this->_createStub(\OxidEsales\PayPalModule\Core\Config::class, $configMethodValues);

        $builder = $this->getPayPalRequestBuilder();
        $builder->setPayPalConfig($config);
        $builder->addBaseParams();

        $this->assertNotContains('LOGOIMG', $builder->getPayPalRequest()->getData());
    }

    /**
     * @expectedException \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException
     */
    public function testAddBaseParams_NoConfigSet_ExceptionThrown()
    {
        $builder = $this->getPayPalRequestBuilder();
        $builder->addBaseParams();
    }

    public function testAddBaseParams_CancelUrlSet_ParameterNotNull()
    {
        $expectedParams = array(
            'CANCELURL' => 'cancelUrl',
        );

        $config = new \OxidEsales\PayPalModule\Core\Config();

        $builder = $this->getPayPalRequestBuilder();
        $builder->setPayPalConfig($config);
        $builder->setCancelUrl('cancelUrl');
        $builder->addBaseParams();

        $this->assertArraysContains($expectedParams, $builder->getPayPalRequest()->getData());
    }

    public function testAddBaseParams_ReturnUrlSet_ParameterNotNull()
    {
        $expectedParams = array(
            'RETURNURL' => 'returnUrl',
        );

        $config = new \OxidEsales\PayPalModule\Core\Config();

        $builder = $this->getPayPalRequestBuilder();
        $builder->setPayPalConfig($config);
        $builder->setReturnUrl('returnUrl');
        $builder->addBaseParams();

        $this->assertArraysContains($expectedParams, $builder->getPayPalRequest()->getData());
    }

    protected function getBasketStub($params = array())
    {
        $basketMethodValues = array(
            'isVirtualPayPalBasket'              => true,
            'getPrice'                           => oxNew(\OxidEsales\Eshop\Core\Price::class),
            'getPayPalBasketVatValue'            => '88.88',
            'isCalculationModeNetto'             => true,
            'getTransactionMode'                 => '88.88',
            'getSumOfCostOfAllItemsPayPalBasket' => '77.77',
            'getDeliveryCosts'                   => '66.66',
            'getDiscountSumPayPalBasket'         => '55.55',
            'getShippingId'                      => null,
        );

        foreach ($params as $key => $value) {
            $basketMethodValues[$key] = $value;
        }

        return $this->_createStub(\OxidEsales\Eshop\Application\Model\Basket::class, $basketMethodValues);
    }

    public function testSetBasket_AllParamsSet()
    {
        $expectedParams = array(
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

        $price = $this->_createStub(\OxidEsales\Eshop\Core\Price::class, array('getBruttoPrice' => '99.99'));

        $basketMethodValues = array('getPrice' => $price);
        $basket = $this->getBasketStub($basketMethodValues);

        $builder = $this->getPayPalRequestBuilder();
        $builder->setBasket($basket);
        $builder->addBasketParams();

        $this->assertArraysEqual($expectedParams, $builder->getPayPalRequest()->getData());
    }


    /**
     */
    public function testSetBasket_OnUpdateCalledOnBasket()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Basket::class);
        $mockBuilder->setMethods(
            ['onUpdate',
             'calculateBasket',
             'isVirtualPayPalBasket',
             'getSumOfCostOfAllItemsPayPalBasket',
             'getDiscountSumPayPalBasket']
        );
        $basket= $mockBuilder->getMock();
        $basket->expects($this->at(0))->method('onUpdate');
        $basket->expects($this->at(1))->method('calculateBasket');
        $basket->expects($this->atLeastOnce())->method('isVirtualPayPalBasket');
        $basket->expects($this->atLeastOnce())->method('getSumOfCostOfAllItemsPayPalBasket');
        $basket->expects($this->atLeastOnce())->method('getDiscountSumPayPalBasket');

        $builder = $this->getPayPalRequestBuilder();
        $builder->setBasket($basket);
        $builder->addBasketParams();

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['setParameter']);
        $payPalService = $mockBuilder->getMock();
        $payPalService->expects($this->any())->method("setParameter");
    }

    public function testSetBasket_NotVirtualBasket_ShippingReconfirmNotSet()
    {
        $basketMethodValues = array(
            'isVirtualPayPalBasket' => false,
        );
        $basket = $this->getBasketStub($basketMethodValues);

        $builder = $this->getPayPalRequestBuilder();
        $builder->setBasket($basket);
        $builder->addBasketParams();

        $this->assertNotContains('REQCONFIRMSHIPPING', $builder->getPayPalRequest()->getData());
    }

    public function testSetBasket_NotNettoMode_TaxAmountNotSet()
    {
        $basketMethodValues = array(
            'isCalculationModeNetto' => false,
        );
        $basket = $this->getBasketStub($basketMethodValues);

        $builder = $this->getPayPalRequestBuilder();
        $builder->setBasket($basket);
        $builder->addBasketParams();

        $this->assertNotContains('PAYMENTREQUEST_0_TAXAMT', $builder->getPayPalRequest()->getData());
    }

    /**
     * @expectedException \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException
     */
    public function testSetBasket_NoConfigSet_ExceptionThrown()
    {
        $builder = $this->getPayPalRequestBuilder();
        $builder->addBasketParams();
    }

    public function testSetDescription()
    {
        $expectedParams = array(
            'PAYMENTREQUEST_0_DESC'   => 'ShopName 99.99 EUR',
            'PAYMENTREQUEST_0_CUSTOM' => 'ShopName 99.99 EUR',
        );
        $basketMethodValues = array(
            'getFPrice' => '99.99',
        );
        $basket = $this->_createStub(\OxidEsales\Eshop\Application\Model\Basket::class, $basketMethodValues);

        $configMethodValues = array('getBrandName' => 'ShopName');
        $config = $this->_createStub(\OxidEsales\PayPalModule\Core\Config::class, $configMethodValues);

        $lang = $this->_createStub(\OxidEsales\Eshop\Core\Language::class, array('translateString' => '%s %s %s'));

        $builder = $this->getPayPalRequestBuilder();
        $builder->setLang($lang);
        $builder->setBasket($basket);
        $builder->setPayPalConfig($config);
        $builder->addDescriptionParams();

        $this->assertArraysEqual($expectedParams, $builder->getPayPalRequest()->getData());
    }

    /**
     * @expectedException \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException
     */
    public function testSetDescription_NoConfigSet_ExceptionThrown()
    {
        $builder = $this->getPayPalRequestBuilder();
        $builder->addDescriptionParams();
    }

    public function testAddBasketItemParams_WithItems()
    {
        $expectedParams = array(
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

        $article = oxNew(\OxidEsales\Eshop\Application\Model\Article::class);
        $article->oxarticles__oxartnum = new \OxidEsales\Eshop\Core\Field('BasketItemArtNum');

        $priceMethodValues = array(
            'getPrice' => '99.99',
        );
        $price = $this->_createStub(\OxidEsales\Eshop\Core\Price::class, $priceMethodValues);

        $basketItemMethodValues = array(
            'getTitle'     => 'BasketItemTitle',
            'getUnitPrice' => $price,
            'getAmount'    => '88',
            'getLink'      => 'BasketItemUrl',
            'getArticle'   => $article,
        );

        $basketItem = $this->_createStub(\OxidEsales\Eshop\Application\Model\BasketItem::class, $basketItemMethodValues);
        $basketItems = array($basketItem, $basketItem);

        $basketMethodValues = array(
            'getContents'                => $basketItems,
            'getPayPalPaymentCosts'      => 0,
            'getPayPalWrappingCosts'     => 0,
            'getPayPalGiftCardCosts'     => 0
        );
        $basket = $this->_createStub(\OxidEsales\Eshop\Application\Model\Basket::class, $basketMethodValues);

        $builder = $this->getPayPalRequestBuilder();
        $builder->setBasket($basket);
        $builder->addBasketItemParams();

        $this->assertArraysEqual($expectedParams, $builder->getPayPalRequest()->getData());
    }

    public function testAddBasketItemParams_WithPayment()
    {
        $expectedParams = array(
            'L_PAYMENTREQUEST_0_NAME0' => 'OEPAYPAL_SURCHARGE OEPAYPAL_TYPE_OF_PAYMENT',
            'L_PAYMENTREQUEST_0_AMT0'  => '66.74',
            'L_PAYMENTREQUEST_0_QTY0'  => 1,
        );

        $basketMethodValues = array(
            'getContents'                => array(),
            'getPayPalPaymentCosts'      => 66.74,
            'getPayPalWrappingCosts'     => 0,
            'getPayPalGiftCardCosts'     => 0
        );
        $basket = $this->_createStub(\OxidEsales\Eshop\Application\Model\Basket::class, $basketMethodValues);

        $builder = $this->getPayPalRequestBuilder();
        $builder->setBasket($basket);
        $builder->addBasketItemParams();

        $this->assertArraysEqual($expectedParams, $builder->getPayPalRequest()->getData());
    }

    public function testAddBasketItemParams_WithWrapping()
    {
        $expectedParams = array(
            'L_PAYMENTREQUEST_0_NAME0' => 'OEPAYPAL_GIFTWRAPPER',
            'L_PAYMENTREQUEST_0_AMT0'  => '100.00',
            'L_PAYMENTREQUEST_0_QTY0'  => 1,
        );

        $basketMethodValues = array(
            'getContents'                => array(),
            'getPayPalPaymentCosts'      => 0,
            'getPayPalWrappingCosts'     => 100,
            'getPayPalGiftCardCosts'     => 0
        );
        $basket = $this->_createStub(\OxidEsales\Eshop\Application\Model\Basket::class, $basketMethodValues);

        $builder = $this->getPayPalRequestBuilder();
        $builder->setBasket($basket);
        $builder->addBasketItemParams();

        $this->assertArraysEqual($expectedParams, $builder->getPayPalRequest()->getData());
    }

    public function testAddBasketItemParams_WithGiftCard()
    {
        $expectedParams = array(
            'L_PAYMENTREQUEST_0_NAME0' => 'OEPAYPAL_GREETING_CARD',
            'L_PAYMENTREQUEST_0_AMT0'  => '100.99',
            'L_PAYMENTREQUEST_0_QTY0'  => 1,
        );

        $basketMethodValues = array(
            'getContents'                => array(),
            'getPayPalPaymentCosts'      => 0,
            'getPayPalWrappingCosts'     => 0,
            'getPayPalGiftCardCosts'     => 100.99
        );
        $basket = $this->_createStub(\OxidEsales\Eshop\Application\Model\Basket::class, $basketMethodValues);

        $builder = $this->getPayPalRequestBuilder();
        $builder->setBasket($basket);
        $builder->addBasketItemParams();

        $this->assertArraysEqual($expectedParams, $builder->getPayPalRequest()->getData());
    }

    public function testAddBasketGrandTotalParams()
    {
        $expectedParams = array(
            'L_PAYMENTREQUEST_0_NAME0' => 'OEPAYPAL_GRAND_TOTAL',
            'L_PAYMENTREQUEST_0_AMT0'  => '99.99',
            'L_PAYMENTREQUEST_0_QTY0'  => 1,
        );

        $basketMethodValues = array(
            'getSumOfCostOfAllItemsPayPalBasket' => '99.99'
        );
        $basket = $this->_createStub(\OxidEsales\Eshop\Application\Model\Basket::class, $basketMethodValues);

        $builder = $this->getPayPalRequestBuilder();
        $builder->setBasket($basket);
        $builder->addBasketGrandTotalParams();

        $this->assertArraysEqual($expectedParams, $builder->getPayPalRequest()->getData());
    }

    public function testAddAddressParams_SelectedAddressIdNotSet_TakeInfoFromUser()
    {
        $expectedParams = array(
            'EMAIL'                              => 'test@test.com',
            'PAYMENTREQUEST_0_SHIPTONAME'        => 'FirstName LastName',
            'PAYMENTREQUEST_0_SHIPTOSTREET'      => 'Street StreetNr',
            'PAYMENTREQUEST_0_SHIPTOCITY'        => 'City',
            'PAYMENTREQUEST_0_SHIPTOZIP'         => 'Zip',
            'PAYMENTREQUEST_0_SHIPTOPHONENUM'    => 'PhoneNum',
            'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => null,
        );

        $userMethodValues = array(
            'getSelectedAddressId' => null,
        );
        $user = $this->_createStub(\OxidEsales\Eshop\Application\Model\User::class, $userMethodValues);
        $user->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field('test@test.com');
        $user->oxuser__oxfname = new \OxidEsales\Eshop\Core\Field('FirstName');
        $user->oxuser__oxlname = new \OxidEsales\Eshop\Core\Field('LastName');
        $user->oxuser__oxstreet = new \OxidEsales\Eshop\Core\Field('Street');
        $user->oxuser__oxstreetnr = new \OxidEsales\Eshop\Core\Field('StreetNr');
        $user->oxuser__oxcity = new \OxidEsales\Eshop\Core\Field('City');
        $user->oxuser__oxzip = new \OxidEsales\Eshop\Core\Field('Zip');
        $user->oxuser__oxfon = new \OxidEsales\Eshop\Core\Field('PhoneNum');
        $user->oxuser__oxcity = new \OxidEsales\Eshop\Core\Field('City');

        $builder = $this->getPayPalRequestBuilder();
        $builder->setUser($user);
        $builder->addAddressParams();

        $this->assertArraysEqual($expectedParams, $builder->getPayPalRequest()->getData());
    }

    /**
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\SetExpressCheckoutRequestBuilder
     */
    protected function getPayPalRequestBuilder()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\Language::class);
        $mockBuilder->setMethods(['translateString']);
        $lang = $mockBuilder->getMock();
        $lang->expects($this->any())
            ->method('translateString')
            ->will($this->returnArgument(0));

        $builder = new \OxidEsales\PayPalModule\Model\PayPalRequest\SetExpressCheckoutRequestBuilder();
        $builder->setLang($lang);

        return $builder;
    }

    /**
     * Checks whether array length are equal and array keys and values are equal independent on keys position
     *
     * @param $expected
     * @param $result
     */
    protected function assertArraysEqual($expected, $result)
    {
        $this->assertArraysContains($expected, $result);
        $this->assertEquals(count($expected), count($result));
    }

    /**
     * Checks whether array array keys and values are equal independent on keys position
     *
     * @param $expected
     * @param $result
     */
    protected function assertArraysContains($expected, $result)
    {
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $result, "Key not found: $key");
            $this->assertEquals($value, $result[$key], "Key '$key' value is not equal to '$value'");
        }
    }

    public function testGetMaxDeliveryAmount_notSet_0()
    {
        $builder = new \OxidEsales\PayPalModule\Model\PayPalRequest\SetExpressCheckoutRequestBuilder();
        $this->assertEquals(0, $builder->getMaxDeliveryAmount());
    }

    public function testGetMaxDeliveryAmount_setValue_setValue()
    {
        $builder = new \OxidEsales\PayPalModule\Model\PayPalRequest\SetExpressCheckoutRequestBuilder();
        $builder->setMaxDeliveryAmount(13);
        $this->assertEquals(13, $builder->getMaxDeliveryAmount());
    }
}
