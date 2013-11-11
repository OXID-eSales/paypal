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
 * @copyright (C) OXID eSales AG 2003-2013
 */

/**
 * Order list class wrapper for PayPal module
 */
class oePayPalOrder_List extends oePayPalOrder_List_parent
{

    /**
     * Executes parent method parent::render() and returns name of template
     * file "order_list.tpl".
     *
     * @return string
     */
    public function render()
    {
        $sTemplate = parent::render();

        $sPaymentStatus  = oxRegistry::getConfig()->getRequestParameter( "paypalpaymentstatus" );
        $sPayment  = oxRegistry::getConfig()->getRequestParameter( "paypalpayment" );

        $this->_aViewData["spaypalpaymentstatus"]   = $sPaymentStatus ? $sPaymentStatus : -1;
        $this->_aViewData["opaypalpaymentstatuslist"] = new oePayPalOrderPaymentStatusList();

        $this->_aViewData["paypalpayment"]   = $sPayment ? $sPayment : -1;

        $oPaymentList = oxNew('oxList');
        $oPaymentList->init('oxPayment');

        $this->_aViewData["oPayments"] = $oPaymentList->getList();

        return $sTemplate;
    }

    /**
     * Builds and returns SQL query string. Adds additional order check.
     *
     * @param object $oListObject list main object
     *
     * @return string
     */
    protected function _buildSelectString( $oListObject = null )
    {
        $sSql = parent::_buildSelectString( $oListObject );

        $sPaymentTable = getViewName( "oxpayments" );

        $sQ = ", `oepaypal_order`.`oepaypal_paymentstatus`, `payments`.`oxdesc` as `paymentname` from `oxorder`
        LEFT JOIN `oepaypal_order` ON `oepaypal_order`.`oepaypal_orderid` = `oxorder`.`oxid`
        LEFT JOIN `" . $sPaymentTable . "` AS `payments` on `payments`.oxid=oxorder.oxpaymenttype ";

        $sSql = str_replace( 'from oxorder', $sQ, $sSql);

        return $sSql;
    }

    /**
     * Adding folder check
     *
     * @param array  $aWhere  SQL condition array
     * @param string $sqlFull SQL query string
     *
     * @return string
     */
    protected function _prepareWhereQuery( $aWhere, $sqlFull )
    {
        $oDb = oxDb::getDb();
        $sQ = parent::_prepareWhereQuery( $aWhere, $sqlFull );

        $sPaymentStatus  = oxRegistry::getConfig()->getRequestParameter( "paypalpaymentstatus" );
        $oPaymentStatusList = new oePayPalOrderPaymentStatusList();

        if ( $sPaymentStatus && $sPaymentStatus != '-1' && in_array( $sPaymentStatus, $oPaymentStatusList->getArray() ) ) {
            $sQ .= " AND ( `oepaypal_order`.`oepaypal_paymentstatus` = " . $oDb->quote( $sPaymentStatus ) . " )";
            $sQ .= " AND ( `oepaypal_order`.`oepaypal_orderid` IS NOT NULL ) ";
        }

        $sPayment  = oxRegistry::getConfig()->getRequestParameter( "paypalpayment" );
        if ( $sPayment && $sPayment != '-1' ) {
            $sQ .= " and ( oxorder.oxpaymenttype = ".$oDb->quote( $sPayment )." )";
        }

        return $sQ;
    }
}