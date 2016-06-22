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
 * Testing oxAccessRightException class.
 */
class Unit_oePayPal_models_oePayPalOrderPaymentCommentListTest extends OxidTestCase
{

    /**
     *  Setup: Prepare data - create need tables
     */
    protected function setUp()
    {
        oxDb::getDb()->execute('TRUNCATE `oepaypal_orderpaymentcomments`');
        oxDb::getDb()->execute('TRUNCATE `oepaypal_orderpayments`');
        oxDb::getDb()->execute('TRUNCATE `oepaypal_order`');
    }

    /**
     * Test case for oePayPalOrderPayment::oePayPalOrderPaymentList()
     * Gets PayPal Order Payment history list
     *
     * @return null
     */
    public function testLoadOrderPayments()
    {
        $oComment = new oePayPalOrderPaymentComment();
        $oComment->setDate('2013-02-03 12:12:12');
        $oComment->setComment('comment1');
        $oComment->setPaymentId(2);
        $oComment->save();

        $oComment = new oePayPalOrderPaymentComment();
        $oComment->setDate('2013-02-03 12:12:12');
        $oComment->setComment('comment2');
        $oComment->setPaymentId(2);
        $oComment->save();

        $aComments = new oePayPalOrderPaymentCommentList();
        $aComments->load(2);

        $this->assertEquals(2, count($aComments));

        $i = 1;
        foreach ($aComments as $oComment) {
            $this->assertEquals('comment' . $i++, $oComment->getComment());
        }
    }


    /**
     * Test case for oePayPalOrderPayment::hasPendingPayment()
     * Checks if list has pending payments
     *
     * @return null
     */
    public function testAddComment()
    {
        $oList = new oePayPalOrderPaymentCommentList();
        $oList->load('payment');

        $this->assertEquals(0, count($oList));

        $oComment = new oePayPalOrderPaymentComment();
        $oComment->setPaymentId('payment');
        $oComment->save();

        $oList = new oePayPalOrderPaymentCommentList();
        $oList->load('payment');

        $this->assertEquals(1, count($oList));

        $oComment = new oePayPalOrderPaymentComment();
        $oComment->setComment('Comment');
        $oList->addComment($oComment);

        $oList = new oePayPalOrderPaymentCommentList();
        $oList->load('payment');

        $this->assertEquals(2, count($oList));
    }
}