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
class Unit_oePayPal_models_oePayPalOrderPaymentCommentTest extends \OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     *  Setup: Prepare data - create need tables
     */
    public function setUp()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpaymentcomments`');
    }

    /**
     * Test case for oePayPalPayPalOrder::save()
     * Tests adding / getting PayPal Order Payment history item
     */
    public function testSavePayPalPayPalOrder_insertIdSet()
    {
        $oComment = new oePayPalOrderPaymentComment();
        $oComment->setCommentId(1);
        $oComment->setDate('2013-02-03 12:12:12');
        $oComment->setComment('comment');
        $oComment->setPaymentId(2);
        $id = $oComment->save();

        $this->assertEquals(1, $id);

        $oCommentLoaded = new oePayPalOrderPaymentComment();
        $oCommentLoaded->load($oComment->getCommentId());

        $this->assertEquals(1, $oCommentLoaded->getCommentId());
        $this->assertEquals('comment', $oCommentLoaded->getComment());
        $this->assertEquals(2, $oCommentLoaded->getPaymentId());
        $this->assertEquals('2013-02-03 12:12:12', $oCommentLoaded->getDate());
    }

    /**
     * Test case for oePayPalPayPalOrder::save()
     * Tests adding / getting PayPal Order Payment history item
     */
    public function testSavePayPalPayPalOrder_withoutDate_dateSetNow()
    {
        $oComment = new oePayPalOrderPaymentComment();
        $oComment->setComment('comment');
        $oComment->setPaymentId(2);
        $oComment->save();

        $oCommentLoaded = new oePayPalOrderPaymentComment();
        $oCommentLoaded->load($oComment->getCommentId());

        $this->assertEquals('comment', $oCommentLoaded->getComment());
        $this->assertEquals(date('Y-m-d'), substr($oCommentLoaded->getDate(), 0, 10));
    }


    /**
     * Test case for oePayPalPayPalOrder::save()
     * Tests adding / getting PayPal Order Payment history item
     */
    public function testSavePayPalPayPalOrder_insertIdNotSet()
    {
        $oComment = new oePayPalOrderPaymentComment();
        $oComment->setDate('2013-02-03 12:12:12');
        $oComment->setComment('comment');
        $oComment->setPaymentId(2);
        $iCommentId = $oComment->save();

        $oCommentLoaded = new oePayPalOrderPaymentComment();
        $oCommentLoaded->load($iCommentId);

        $this->assertEquals($iCommentId, $oCommentLoaded->getCommentId());
        $this->assertEquals('comment', $oCommentLoaded->getComment());
        $this->assertEquals(2, $oCommentLoaded->getPaymentId());
        $this->assertEquals('2013-02-03 12:12:12', $oCommentLoaded->getDate());
    }

    /**
     * Test case for oePayPalPayPalOrder::save()
     * Tests adding / getting PayPal Order Payment history item
     */
    public function testSavePayPalPayPalOrder_update()
    {
        $oComment = new oePayPalOrderPaymentComment();
        $oComment->setCommentId(10);
        $oComment->setDate('2013-02-03 12:12:12');
        $oComment->setComment('comment');
        $oComment->setPaymentId(2);
        $oComment->save();

        $oCommentLoaded = new oePayPalOrderPaymentComment();
        $oCommentLoaded->load(10);
        $oCommentLoaded->setComment('comment comment');
        $id = $oCommentLoaded->save();

        $this->assertEquals(10, $id);

        $oCommentLoaded = new oePayPalOrderPaymentComment();
        $oCommentLoaded->load(10);
        $this->assertEquals(10, $oCommentLoaded->getCommentId());
        $this->assertEquals('comment comment', $oCommentLoaded->getComment());
        $this->assertEquals(2, $oCommentLoaded->getPaymentId());
        $this->assertEquals('2013-02-03 12:12:12', $oCommentLoaded->getDate());
    }
}