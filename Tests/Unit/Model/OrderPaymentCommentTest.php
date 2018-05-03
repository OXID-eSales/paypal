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
 * Testing oxAccessRightException class.
 */
class OrderPaymentCommentTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     *  Setup: Prepare data - create need tables
     */
    public function setUp()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpaymentcomments`');
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\PayPalOrder::save()
     * Tests adding / getting PayPal Order Payment history item
     */
    public function testSavePayPalPayPalOrder_insertIdSet()
    {
        $comment = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $comment->setCommentId(1);
        $comment->setDate('2013-02-03 12:12:12');
        $comment->setComment('comment');
        $comment->setPaymentId(2);
        $id = $comment->save();

        $this->assertEquals(1, $id);

        $commentLoaded = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $commentLoaded->load($comment->getCommentId());

        $this->assertEquals(1, $commentLoaded->getCommentId());
        $this->assertEquals('comment', $commentLoaded->getComment());
        $this->assertEquals(2, $commentLoaded->getPaymentId());
        $this->assertEquals('2013-02-03 12:12:12', $commentLoaded->getDate());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\PayPalOrder::save()
     * Tests adding / getting PayPal Order Payment history item
     */
    public function testSavePayPalPayPalOrder_withoutDate_dateSetNow()
    {
        $comment = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $comment->setComment('comment');
        $comment->setPaymentId(2);
        $comment->save();

        $commentLoaded = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $commentLoaded->load($comment->getCommentId());

        $this->assertEquals('comment', $commentLoaded->getComment());
        $this->assertEquals(date('Y-m-d'), substr($commentLoaded->getDate(), 0, 10));
    }


    /**
     * Test case for \OxidEsales\PayPalModule\Model\PayPalOrder::save()
     * Tests adding / getting PayPal Order Payment history item
     */
    public function testSavePayPalPayPalOrder_insertIdNotSet()
    {
        $comment = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $comment->setDate('2013-02-03 12:12:12');
        $comment->setComment('comment');
        $comment->setPaymentId(2);
        $commentId = $comment->save();

        $commentLoaded = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $commentLoaded->load($commentId);

        $this->assertEquals($commentId, $commentLoaded->getCommentId());
        $this->assertEquals('comment', $commentLoaded->getComment());
        $this->assertEquals(2, $commentLoaded->getPaymentId());
        $this->assertEquals('2013-02-03 12:12:12', $commentLoaded->getDate());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\PayPalOrder::save()
     * Tests adding / getting PayPal Order Payment history item
     */
    public function testSavePayPalPayPalOrder_update()
    {
        $comment = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $comment->setCommentId(10);
        $comment->setDate('2013-02-03 12:12:12');
        $comment->setComment('comment');
        $comment->setPaymentId(2);
        $comment->save();

        $commentLoaded = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $commentLoaded->load(10);
        $commentLoaded->setComment('comment comment');
        $id = $commentLoaded->save();

        $this->assertEquals(10, $id);

        $commentLoaded = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $commentLoaded->load(10);
        $this->assertEquals(10, $commentLoaded->getCommentId());
        $this->assertEquals('comment comment', $commentLoaded->getComment());
        $this->assertEquals(2, $commentLoaded->getPaymentId());
        $this->assertEquals('2013-02-03 12:12:12', $commentLoaded->getDate());
    }
}