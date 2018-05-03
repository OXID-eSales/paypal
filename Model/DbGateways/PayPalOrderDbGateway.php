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
 * Order db gateway class
 */
class PayPalOrderDbGateway extends \OxidEsales\PayPalModule\Core\ModelDbGateway
{
    /**
     * Save PayPal order data to database.
     *
     * @param array $data
     *
     * @return bool
     */
    public function save($data)
    {
        $db = $this->getDb();
        $fields = [];

        foreach ($data as $field => $value) {
            $fields[] = '`' . $field . '` = ' . $db->quote($value);
        }

        $query = 'INSERT INTO `oepaypal_order` SET ';
        $query .= implode(', ', $fields);
        $query .= ' ON DUPLICATE KEY UPDATE ';
        $query .= ' `oepaypal_orderid`=LAST_INSERT_ID(`oepaypal_orderid`), ';
        $query .= implode(', ', $fields);

        $db->execute($query);

        $id = $data['oepaypal_orderid'];
        if (empty($id)) {
            $id = $db->getOne('SELECT LAST_INSERT_ID()');
        }

        return $id;
    }

    /**
     * Load PayPal order data from Db.
     *
     * @param string $orderId Order id.
     *
     * @return array
     */
    public function load($orderId)
    {
        $db = $this->getDb();
        $data = $db->getRow('SELECT * FROM `oepaypal_order` WHERE `oepaypal_orderid` = ' . $db->quote($orderId));

        return $data;
    }

    /**
     * Delete PayPal order data from database.
     *
     * @param string $orderId Order id.
     *
     * @return bool
     */
    public function delete($orderId)
    {
        $db = $this->getDb();
        $db->startTransaction();

        $deleteCommentsResult = $db->execute(
            'DELETE
                `oepaypal_orderpaymentcomments`
            FROM `oepaypal_orderpaymentcomments`
                INNER JOIN `oepaypal_orderpayments` ON `oepaypal_orderpayments`.`oepaypal_paymentid` = `oepaypal_orderpaymentcomments`.`oepaypal_paymentid`
            WHERE `oepaypal_orderpayments`.`oepaypal_orderid` = ' . $db->quote($orderId)
        );
        $deleteOrderPaymentResult = $db->execute('DELETE FROM `oepaypal_orderpayments` WHERE `oepaypal_orderid` = ' . $db->quote($orderId));
        $deleteOrderResult = $db->execute('DELETE FROM `oepaypal_order` WHERE `oepaypal_orderid` = ' . $db->quote($orderId));

        $result = ($deleteOrderResult !== false) || ($deleteOrderPaymentResult !== false) || ($deleteCommentsResult !== false);

        if ($result) {
            $db->commitTransaction();
        } else {
            $db->rollbackTransaction();
        }

        return $result;
    }
}
