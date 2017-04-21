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
 * Testing oePayPalIPNRequest class.
 */
class IPNRequestPaymentSetterTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function providerGetRequestOrderPayment()
    {
        return array(
            'capture' => array(
                array(
                    'payment_status' => 'Completed',
                    'txn_id'         => 'a2s12as1d2',
                    'receiver_email' => 'test@oxid-esales.com',
                    'mc_gross'       => 15.66,
                    'mc_currency'    => 'EUR',
                    'ipn_track_id'   => 'corrxxx',
                    'payment_date'   => '00:54:36 Jun 03, 2015 PDT',
                ),
                'capture'
            ),
            'nothing' => array(null, ''),
            'refund'  => array(
                array(
                    'payment_status' => 'Refunded',
                    'txn_id'         => 'a2s12as1dxxx',
                    'receiver_email' => 'test@oxid-esales.com',
                    'mc_gross'       => -6.66,
                    'mc_currency'    => 'EUR',
                    'correlation_id' => 'corryyy',
                    'payment_date'   => '00:54:36 Jun 03, 2015 PDT',
                ),
                'refund'
            ),
        );
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter::getRequestOrderPayment
     * Test case for \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter::setRequestOrderPayment
     * Test case for \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter::_prepareOrderPayment
     * Test case for \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter::getRequest
     * Test case for \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter::getAction
     * Test case for \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter::getAmount
     *
     * @param array  $aParams        parameters for POST imitating PayPal.
     * @param string $expectedAction Expected action for resulting payment.
     *
     * @dataProvider providerGetRequestOrderPayment
     */
    public function testGetRequestOrderPayment($aParams, $expectedAction)
    {
        $oPayPalExpectedPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        if (!empty($aParams)) {
            $oPayPalExpectedPayment->setStatus($aParams['payment_status']);
            $oPayPalExpectedPayment->setTransactionId($aParams['txn_id']);
            $oPayPalExpectedPayment->setCurrency($aParams['mc_currency']);
            $oPayPalExpectedPayment->setAmount(abs($aParams['mc_gross']));
            $oPayPalExpectedPayment->setAction($expectedAction);

            $correlationId = empty($aParams['correlation_id']) ? $aParams['ipn_track_id'] :$aParams['correlation_id'];
            $oPayPalExpectedPayment->setCorrelationId($correlationId);
            $oPayPalExpectedPayment->setDate(date('Y-m-d H:i:s', strtotime($aParams['payment_date'])));

        } else {
            $oPayPalExpectedPayment->setStatus(null);
            $oPayPalExpectedPayment->setTransactionId(null);
            $oPayPalExpectedPayment->setCurrency(null);
            $oPayPalExpectedPayment->setAmount(null);
            $oPayPalExpectedPayment->setCorrelationId(null);
            $oPayPalExpectedPayment->setDate(null);
            $oPayPalExpectedPayment->setAction('capture');
        }

        $_POST = $aParams;
        $oRequest = new \OxidEsales\PayPalModule\Core\Request();
        $oPayPalPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();

        $oPayPalIPNRequestSetter = new \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter();
        $oPayPalIPNRequestSetter->setRequest($oRequest);
        $oPayPalIPNRequestSetter->setRequestOrderPayment($oPayPalPayment);
        $oRequestOrderPayment = $oPayPalIPNRequestSetter->getRequestOrderPayment();

        $this->assertEquals($oPayPalExpectedPayment, $oRequestOrderPayment, 'Payment object do not have request parameters.');
    }

}
