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

namespace OxidEsales\PayPalModule\Tests\Unit\Model;

/**
 * Testing \OxidEsales\PayPalModule\Model\IPNPaymentValidator class.
 */
class IPNPaymentValidatorTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Data provider for testIsValid()
     *
     * @return array
     */
    public function providerIsValid()
    {
        // We test with not installed module, so we check for translation constant - PAYPAL_INFORMATION.
        // If module would be installed, translation would be returned instead of constant name.
        return array(
            array(true, '', null, null, null, null),
            array(true, '', 'USD', 125.38, 'USD', 125.38),
            array(true, '', 'EUR', 0.08, 'EUR', 0.08),
            array(false, 'Bezahlinformation: 0.09 USD. PayPal-Information: 0.08 EUR.'
                  , 'EUR', 0.08, 'USD', 0.09),
            array(false, 'Bezahlinformation: 0.08 USD. PayPal-Information: 0.08 EUR.'
                  , 'EUR', 0.08, 'USD', 0.08),
            array(false, 'Bezahlinformation: 0.09 EUR. PayPal-Information: 0.08 EUR.'
                  , 'EUR', 0.08, 'EUR', 0.09),
        );
    }

    /**
     * @dataProvider providerIsValid
     *
     * @param $isValidExpected
     * @param $validationMessageExpected
     * @param $currencyPayPal
     * @param $pricePayPal
     * @param $currencyPayment
     * @param $amountPayment
     */
    public function testIsValid($isValidExpected, $validationMessageExpected, $currencyPayPal, $pricePayPal, $currencyPayment, $amountPayment)
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setCurrency($currencyPayment);
        $orderPayment->setAmount($amountPayment);

        $requestOrderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $requestOrderPayment->setCurrency($currencyPayPal);
        $requestOrderPayment->setAmount($pricePayPal);

        $validationMessage = '';
        $payPalIPNRequestValidator = new \OxidEsales\PayPalModule\Model\IPNPaymentValidator();
        $payPalIPNRequestValidator->setLang(\OxidEsales\Eshop\Core\Registry::getLang());
        $payPalIPNRequestValidator->setOrderPayment($orderPayment);
        $payPalIPNRequestValidator->setRequestOrderPayment($requestOrderPayment);

        $isValid = $payPalIPNRequestValidator->isValid();
        if (!$isValidExpected) {
            $validationMessage = $payPalIPNRequestValidator->getValidationFailureMessage();
        }

        $this->assertEquals($isValidExpected, $isValid, 'IPN request validation state is not as expected. ');
        $this->assertEquals($validationMessageExpected, $validationMessage, 'IPN request validation message is not as expected. ');
    }

    public function testSetGetOrderPayment()
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setCurrency('EUR');
        $orderPayment->setAmount('12.23');

        $payPalIPNRequestValidator = new \OxidEsales\PayPalModule\Model\IPNPaymentValidator();
        $payPalIPNRequestValidator->setOrderPayment($orderPayment);

        $this->assertEquals($orderPayment, $payPalIPNRequestValidator->getOrderPayment(), 'Getter should return same as set in setter.');
    }

    public function testSetGetRequestOrderPayment()
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setCurrency('EUR');
        $orderPayment->setAmount('12.23');

        $payPalIPNRequestValidator = new \OxidEsales\PayPalModule\Model\IPNPaymentValidator();
        $payPalIPNRequestValidator->setRequestOrderPayment($orderPayment);

        $this->assertEquals($orderPayment, $payPalIPNRequestValidator->getRequestOrderPayment(), 'Getter should return same as set in setter.');
    }

    public function testSetGetLang()
    {
        $lang = oxNew(\OxidEsales\Eshop\Core\Language::class);
        $lang->setBaseLanguage(0);

        $payPalIPNRequestValidator = new \OxidEsales\PayPalModule\Model\IPNPaymentValidator();
        $payPalIPNRequestValidator->setLang($lang);

        $this->assertEquals($lang, $payPalIPNRequestValidator->getLang(), 'Getter should return same as set in setter.');
    }
}
