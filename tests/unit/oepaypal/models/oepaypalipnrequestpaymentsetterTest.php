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


/**
 * Testing oePayPalIPNRequest class.
 */
class unit_oepaypal_models_oePayPalIPNRequestPaymentSetterTest extends OxidTestCase
{
    public function providerGetRequestOrderPayment()
    {
        return array(
            array(array('payment_status' => 'Completed', 'txn_id' => 'a2s12as1d2', 'receiver_email' => 'test@oxid-esaltes.com'
                        , 'mc_gross'     => 15.66, 'mc_currency' => 'EUR')),
            array(null),
        );
    }

    /**
     * Test case for oePayPalIPNRequestPaymentSetter::getRequestOrderPayment
     * Test case for oePayPalIPNRequestPaymentSetter::setRequestOrderPayment
     * Test case for oePayPalIPNRequestPaymentSetter::_prepareOrderPayment
     * Test case for oePayPalIPNRequestPaymentSetter::getRequest
     *
     * @param array $aParams parameters for POST imitating PayPal.
     *
     * @dataProvider providerGetRequestOrderPayment
     */
    public function testGetRequestOrderPayment($aParams)
    {
        $oPayPalExpectedPayment = new oePayPalOrderPayment();
        if (!empty($aParams)) {
            $oPayPalExpectedPayment->setStatus($aParams['payment_status']);
            $oPayPalExpectedPayment->setTransactionId($aParams['txn_id']);
            $oPayPalExpectedPayment->setCurrency($aParams['mc_currency']);
            $oPayPalExpectedPayment->setAmount($aParams['mc_gross']);
        } else {
            $oPayPalExpectedPayment->setStatus(null);
            $oPayPalExpectedPayment->setTransactionId(null);
            $oPayPalExpectedPayment->setCurrency(null);
            $oPayPalExpectedPayment->setAmount(null);
        }

        $_POST = $aParams;
        $oRequest = new oePayPalRequest();
        $oPayPalPayment = new oePayPalOrderPayment();

        $oPayPalIPNRequestSetter = new oePayPalIPNRequestPaymentSetter();
        $oPayPalIPNRequestSetter->setRequest($oRequest);
        $oPayPalIPNRequestSetter->setRequestOrderPayment($oPayPalPayment);

        $this->assertEquals($oPayPalExpectedPayment, $oPayPalIPNRequestSetter->getRequestOrderPayment(), 'Payment object do not have request parameters.');
    }
}