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

namespace OxidEsales\PayPalModule\Model\DbGateways;

/**
 * Order payment db gateway class
 */
class OrderPaymentDbGateway extends \OxidEsales\PayPalModule\Core\ModelDbGateway
{
    /**
     * Save PayPal order payment data to database.
     *
     * @param array $data
     *
     * @return int
     */
    public function save($data)
    {
        $db = $this->getDb();

        foreach ($data as $field => $value) {
            $fields[] = '`' . $field . '` = ' . $db->quote($value);
        }

        $query = 'INSERT INTO `oepaypal_orderpayments` SET ';
        $query .= implode(', ', $fields);
        $query .= ' ON DUPLICATE KEY UPDATE ';
        $query .= ' `oepaypal_paymentid`=LAST_INSERT_ID(`oepaypal_paymentid`), ';
        $query .= implode(', ', $fields);
        $db->execute($query);

        $id = $data['oepaypal_paymentid'];
        if (empty($id)) {
            $id = $db->getOne('SELECT LAST_INSERT_ID()');
        }

        return $id;
    }

    /**
     * Load PayPal order payment data from Db.
     *
     * @param string $paymentId Order id.
     *
     * @return array
     */
    public function load($paymentId)
    {
        $db = $this->getDb();
        $data = $db->getRow('SELECT * FROM `oepaypal_orderpayments` WHERE `oepaypal_paymentid` = ' . $db->quote($paymentId));

        return $data;
    }

    /**
     * Load PayPal order payment data from Db.
     *
     * @param string $transactionId Order id.
     *
     * @return array
     */
    public function loadByTransactionId($transactionId)
    {
        $db = $this->getDb();
        $data = $db->getRow('SELECT * FROM `oepaypal_orderpayments` WHERE `oepaypal_transactionid` = ' . $db->quote($transactionId));

        return $data;
    }

    /**
     * Delete PayPal order payment data from database.
     *
     * @param string $paymentId Order id.
     *
     * @return bool
     */
    public function delete($paymentId)
    {
        $db = $this->getDb();
        $db->startTransaction();

        $deleteResult = $db->execute('DELETE FROM `oepaypal_orderpayments` WHERE `oepaypal_paymentid` = ' . $db->quote($paymentId));
        $deleteCommentResult = $db->execute('DELETE FROM `oepaypal_orderpaymentcomments` WHERE `oepaypal_paymentid` = ' . $db->quote($paymentId));

        $result = ($deleteResult !== false) || ($deleteCommentResult !== false);

        if ($result) {
            $db->commitTransaction();
        } else {
            $db->rollbackTransaction();
        }

        return $result;
    }


    /**
     * Load PayPal order payment data from Db.
     *
     * @param string $orderId Order id.
     *
     * @return array
     */
    public function getList($orderId)
    {
        $db = $this->getDb();
        $data = $db->getAll('SELECT * FROM `oepaypal_orderpayments` WHERE `oepaypal_orderid` = ' . $db->quote($orderId) . ' ORDER BY `oepaypal_date` DESC');

        return $data;
    }
}
