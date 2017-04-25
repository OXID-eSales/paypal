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
 * @copyright (C) OXID eSales AG 2003-2017
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
     */
    public function testIsValid($blIsValidExpected, $sValidationMessageExpected
        , $sCurrencyPayPal, $dPricePayPal
        , $sCurrencyPayment, $dAmountPayment)
    {
        $oOrderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $oOrderPayment->setCurrency($sCurrencyPayment);
        $oOrderPayment->setAmount($dAmountPayment);

        $oRequestOrderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $oRequestOrderPayment->setCurrency($sCurrencyPayPal);
        $oRequestOrderPayment->setAmount($dPricePayPal);

        $sValidationMessage = '';
        $oPayPalIPNRequestValidator = new \OxidEsales\PayPalModule\Model\IPNPaymentValidator();
        $oPayPalIPNRequestValidator->setLang(\OxidEsales\Eshop\Core\Registry::getLang());
        $oPayPalIPNRequestValidator->setOrderPayment($oOrderPayment);
        $oPayPalIPNRequestValidator->setRequestOrderPayment($oRequestOrderPayment);

        $blIsValid = $oPayPalIPNRequestValidator->isValid();
        if (!$blIsValidExpected) {
            $sValidationMessage = $oPayPalIPNRequestValidator->getValidationFailureMessage();
        }

        $this->assertEquals($blIsValidExpected, $blIsValid, 'IPN request validation state is not as expected. ');
        $this->assertEquals($sValidationMessageExpected, $sValidationMessage, 'IPN request validation message is not as expected. ');
    }

    public function testSetGetOrderPayment()
    {
        $oOrderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $oOrderPayment->setCurrency('EUR');
        $oOrderPayment->setAmount('12.23');

        $oPayPalIPNRequestValidator = new \OxidEsales\PayPalModule\Model\IPNPaymentValidator();
        $oPayPalIPNRequestValidator->setOrderPayment($oOrderPayment);

        $this->assertEquals($oOrderPayment, $oPayPalIPNRequestValidator->getOrderPayment(), 'Getter should return same as set in setter.');
    }

    public function testSetGetRequestOrderPayment()
    {
        $oOrderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $oOrderPayment->setCurrency('EUR');
        $oOrderPayment->setAmount('12.23');

        $oPayPalIPNRequestValidator = new \OxidEsales\PayPalModule\Model\IPNPaymentValidator();
        $oPayPalIPNRequestValidator->setRequestOrderPayment($oOrderPayment);

        $this->assertEquals($oOrderPayment, $oPayPalIPNRequestValidator->getRequestOrderPayment(), 'Getter should return same as set in setter.');
    }

    public function testSetGetLang()
    {
        $oLang = new \OxidEsales\Eshop\Core\Language();
        $oLang->setBaseLanguage(0);

        $oPayPalIPNRequestValidator = new \OxidEsales\PayPalModule\Model\IPNPaymentValidator();
        $oPayPalIPNRequestValidator->setLang($oLang);

        $this->assertEquals($oLang, $oPayPalIPNRequestValidator->getLang(), 'Getter should return same as set in setter.');
    }
}