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
 * PayPal order payment comment class
 */
class oePayPalOrderPaymentComment extends oePayPalModel
{
    public function __construct()
    {
        $this->_setValue( 'oepaypal_date', date( 'Y-m-d H:i:s', oxRegistry::get("oxUtilsDate")->getTime() ) ) ;
    }


    public function setId( $sCommentId )
    {
        $this->setCommentId( $sCommentId );
    }

    public function getId()
    {
        return $this->getCommentId();
    }

    /**
     * Set PayPal order comment Id
     *
     * @param string $sCommentId
     */
    public function setCommentId( $sCommentId )
    {
        $this->_setValue( 'oepaypal_commentid', $sCommentId );
    }

    /**
     * Set PayPal comment Id
     *
     * @return string
     */
    public function getCommentId()
    {
        return $this->_getValue( 'oepaypal_commentid' );
    }

    /**
     * Set PayPal order payment Id
     *
     * @param string $sPaymentId
     */
    public function setPaymentId( $sPaymentId )
    {
        $this->_setValue( 'oepaypal_paymentid', $sPaymentId );
    }

    /**
     * Set PayPal order payment Id
     *
     * @return string
     */
    public function getPaymentId()
    {
        return $this->_getValue( 'oepaypal_paymentid' );
    }

    /**
     * Set date
     *
     * @param string $sDate
     */
    public function setDate( $sDate )
    {
        $this->_setValue( 'oepaypal_date', $sDate );
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->_getValue( 'oepaypal_date' );
    }

    /**
     * Set comment
     *
     * @param string $sComment
     */
    public function setComment( $sComment )
    {
        $this->_setValue( 'oepaypal_comment', $sComment );
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->_getValue( 'oepaypal_comment' );
    }

    /**
     * Return database gateway
     *
     * @return oePayPalOrderPaymentCommentDbGateway
     */
    protected function _getDbGateway()
    {
        if ( is_null( $this->_oDbGateway ) ) {
            $this->_setDbGateway( oxNew( 'oePayPalOrderPaymentCommentDbGateway' ) );
        }

        return $this->_oDbGateway;
    }
}