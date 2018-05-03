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

namespace OxidEsales\PayPalModule\Model;

/**
 * PayPal order payment comment list class
 */
class OrderPaymentCommentList extends \OxidEsales\PayPalModule\Core\PayPalList
{
    /**
     * Data base gateway
     *
     * @var oePayPalPayPalDbGateway
     */
    protected $dbGateway = null;

    /**
     * @var string|null
     */
    protected $paymentId = null;

    /**
     * Sets payment id.
     *
     * @param string $paymentId
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * Returns payment id.
     *
     * @return null|string
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * Returns DB gateway. If it's not set- creates object and sets.
     *
     * @return oePayPalPayPalDbGateway
     */
    protected function getDbGateway()
    {
        if (is_null($this->dbGateway)) {
            $this->setDbGateway(oxNew(\OxidEsales\PayPalModule\Model\DbGateways\OrderPaymentCommentDbGateway::class));
        }

        return $this->dbGateway;
    }

    /**
     * Set model database gateway.
     *
     * @param object $dbGateway
     */
    protected function setDbGateway($dbGateway)
    {
        $this->dbGateway = $dbGateway;
    }

    /**
     * Selects and loads order payment history.
     *
     * @param string $paymentId Order id.
     */
    public function load($paymentId)
    {
        $this->setPaymentId($paymentId);

        $comments = array();
        $commentsData = $this->getDbGateway()->getList($this->getPaymentId());
        if (is_array($commentsData) && count($commentsData)) {
            $comments = array();
            foreach ($commentsData as $data) {
                $comment = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentComment::class);
                $comment->setData($data);
                $comments[] = $comment;
            }
        }

        $this->setArray($comments);
    }

    /**
     * Add comment.
     *
     * @param object $comment
     *
     * @return mixed
     */
    public function addComment($comment)
    {
        $comment->setPaymentId($this->getPaymentId());
        $comment->save();

        $this->load($this->getPaymentId());

        return $comment;
    }
}
