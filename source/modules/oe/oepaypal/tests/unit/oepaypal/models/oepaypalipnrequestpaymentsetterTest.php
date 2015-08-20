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
            'capture' => array(
                array(
                    'payment_status' => 'Completed',
                    'txn_id'         => 'a2s12as1d2',
                    'receiver_email' => 'test@oxid-esales.com',
                    'mc_gross'       => 15.66,
                    'mc_currency'    => 'EUR',
                    'ipn_track_id'   => 'corrxxx',
                    'payment_date'   => '00:54:36 Jun 03, 2015 PDT',
                    'memo'           => ''
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
                    'memo'           => ''
                ),
                'refund'
            ),
        );
    }

    /**
     * Test case for oePayPalIPNRequestPaymentSetter::getRequestOrderPayment
     * Test case for oePayPalIPNRequestPaymentSetter::setRequestOrderPayment
     * Test case for oePayPalIPNRequestPaymentSetter::_prepareOrderPayment
     * Test case for oePayPalIPNRequestPaymentSetter::getRequest
     * Test case for oePayPalIPNRequestPaymentSetter::getAction
     * Test case for oePayPalIPNRequestPaymentSetter::getAmount
     * Test case for oePayPalIPNRequestPaymentSetter::addRequestPaymentComment
     *
     * @param array  $aParams        parameters for POST imitating PayPal.
     * @param string $expectedAction Expected action for resulting payment.
     *
     * @dataProvider providerGetRequestOrderPayment
     */
    public function testGetRequestOrderPayment($aParams, $expectedAction)
    {
        $oPayPalExpectedPayment = new oePayPalOrderPayment();
        if (!empty($aParams)) {
            $oPayPalExpectedPayment->setStatus($aParams['payment_status']);
            $oPayPalExpectedPayment->setTransactionId($aParams['txn_id']);
            $oPayPalExpectedPayment->setCurrency($aParams['mc_currency']);
            $oPayPalExpectedPayment->setAmount(abs($aParams['mc_gross']));
            $oPayPalExpectedPayment->setAction($expectedAction);

            $correlationId = empty($aParams['correlation_id']) ? $aParams['ipn_track_id'] :$aParams['correlation_id'];
            $oPayPalExpectedPayment->setCorrelationId($correlationId);
            $oPayPalExpectedPayment->setDate(date('Y-m-d H:i:s', strtotime($aParams['payment_date'])));

            if (!empty($aParams['memo'])) {
                $comment = oxNew('oePayPalOrderPaymentComment');
                $comment->setComment($aParams['memo']);
                $oPayPalExpectedPayment->addComment($comment);
            }

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
        $oRequest = new oePayPalRequest();
        $oPayPalPayment = new oePayPalOrderPayment();

        $oPayPalIPNRequestSetter = new oePayPalIPNRequestPaymentSetter();
        $oPayPalIPNRequestSetter->setRequest($oRequest);
        $oPayPalIPNRequestSetter->setRequestOrderPayment($oPayPalPayment);
        $oRequestOrderPayment = $oPayPalIPNRequestSetter->getRequestOrderPayment();
        $oRequestOrderPayment = $oPayPalIPNRequestSetter->addRequestPaymentComment($oRequestOrderPayment);

        $this->assertEquals($oPayPalExpectedPayment, $oRequestOrderPayment, 'Payment object do not have request parameters.');
    }


    public function providerGetRequestOrderPaymentComment()
    {
        return array(
            'refund'  => array(
                array(
                    'payment_status' => 'Refunded',
                    'txn_id'         => 'a2s12as1dxxx',
                    'receiver_email' => 'test@oxid-esales.com',
                    'mc_gross'       => -6.66,
                    'mc_currency'    => 'EUR',
                    'correlation_id' => 'corryyy',
                    'payment_date'   => '00:54:36 Jun 03, 2015 PDT',
                    'memo'           => 'transaction comment for capture'
                )
            ),
        );
    }

    /**
     *
     * Test case for oePayPalIPNRequestPaymentSetter::getRequestPaymentComment
     *
     * @param array  $aParams        parameters for POST imitating PayPal.
     * @param string $expectedAction Expected action for resulting payment.
     *
     * @dataProvider providerGetRequestOrderPaymentComment
     */
    public function testGetRequestOrderPaymentComment($aParams)
    {
        $_POST = $aParams;
        $oRequest = new oePayPalRequest();
        $oPayPalPayment = new oePayPalOrderPayment();

        $oPayPalIPNRequestSetter = new oePayPalIPNRequestPaymentSetter();
        $oPayPalIPNRequestSetter->setRequest($oRequest);
        $oPayPalIPNRequestSetter->setRequestOrderPayment($oPayPalPayment);
        $oRequestOrderPayment = $oPayPalIPNRequestSetter->getRequestOrderPayment();
        $oRequestOrderPayment = $oPayPalIPNRequestSetter->addRequestPaymentComment($oRequestOrderPayment);

        $commentList = $oRequestOrderPayment->getCommentList();
        $comment = $commentList->current();
        $this->assertTrue(is_a($comment, 'oePayPalOrderPaymentComment'));
        $this->assertEquals($aParams['memo'], $comment->getComment(), 'Payment object comment not as expected.');
    }

}
