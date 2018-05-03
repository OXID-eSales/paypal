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
 * Order payment comment db gateway class.
 */
class OrderPaymentCommentDbGateway extends \OxidEsales\PayPalModule\Core\ModelDbGateway
{
    /**
     * Save PayPal order payment comment data to database.
     *
     * @param array $data
     *
     * @return bool
     */
    public function save($data)
    {
        $db = $this->getDb();

        foreach ($data as $field => $value) {
            $fields[] = '`' . $field . '` = ' . $db->quote($value);
        }

        $query = 'INSERT INTO `oepaypal_orderpaymentcomments` SET ';
        $query .= implode(', ', $fields);
        $query .= ' ON DUPLICATE KEY UPDATE ';
        $query .= ' `oepaypal_commentid`=LAST_INSERT_ID(`oepaypal_commentid`), ';
        $query .= implode(', ', $fields);
        $db->execute($query);

        $commentId = $data['oepaypal_commentid'];
        if (empty($commentId)) {
            $commentId = $db->getOne('SELECT LAST_INSERT_ID()');
        }

        return $commentId;
    }

    /**
     * Load PayPal order payment comment data from Db.
     *
     * @param string $paymentId order id
     *
     * @return array
     */
    public function getList($paymentId)
    {
        $db = $this->getDb();
        $data = $db->getAll('SELECT * FROM `oepaypal_orderpaymentcomments` WHERE `oepaypal_paymentid` = ' . $db->quote($paymentId) . ' ORDER BY `oepaypal_date` DESC');

        return $data;
    }

    /**
     * Load PayPal order payment comment data from Db.
     *
     * @param string $commentId Order id.
     *
     * @return array
     */
    public function load($commentId)
    {
        $db = $this->getDb();
        $data = $db->getRow('SELECT * FROM `oepaypal_orderpaymentcomments` WHERE `oepaypal_commentid` = ' . $db->quote($commentId));

        return $data;
    }

    /**
     * Delete PayPal order payment comment data from database.
     *
     * @param string $commentId Order id.
     *
     * @return bool
     */
    public function delete($commentId)
    {
        $db = $this->getDb();
        $db->startTransaction();

        $deleteResult = $db->execute('DELETE FROM `oepaypal_orderpaymentcomments` WHERE `oepaypal_commentid` = ' . $db->quote($commentId));

        $result = ($deleteResult !== false);

        if ($result) {
            $db->commitTransaction();
        } else {
            $db->rollbackTransaction();
        }

        return $result;
    }
}
