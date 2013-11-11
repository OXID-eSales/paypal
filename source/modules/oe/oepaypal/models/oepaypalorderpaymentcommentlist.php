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
 * PayPal order payment comment list class
 */
class oePayPalOrderPaymentCommentList extends oePayPalList
{
    /**
     * Data base gateway
     *
     * @var oePayPalPayPalDbGateway
     */
    protected $_oDbGateway = null;


    protected $_sPaymentId = null;

    public function setPaymentId( $sPaymentId )
    {
        $this->_sPaymentId = $sPaymentId;
    }

    public function getPaymentId()
    {
        return $this->_sPaymentId;
    }

    protected function _getDbGateway() {
        if ( is_null( $this->_oDbGateway ) ) {
            $this->_setDbGateway( oxNew( 'oePayPalOrderPaymentCommentDbGateway' ) );
        }
        return $this->_oDbGateway;
    }

    /**
     * Set model database gateway
     *
     * @var object
     */
    protected function _setDbGateway( $oDbGateway )
    {
        $this->_oDbGateway = $oDbGateway;
    }

    /**
     * Selects and loads order payment history
     *
     * @param string $sPaymentId order id
     *
     */
    public function load( $sPaymentId )
    {
        $this->setPaymentId( $sPaymentId );

        $aComments = array();
        $aCommentsData = $this->_getDbGateway()->getList( $this->getPaymentId() ) ;
        if( is_array( $aCommentsData ) && count( $aCommentsData ) ){
            $aComments = array();
            foreach ($aCommentsData as $aData ){
                $oComment = oxNew( 'oePayPalOrderPaymentComment' );
                $oComment->setData( $aData );
                $aComments[] = $oComment;
            }
        }

        $this->setArray( $aComments );
    }

    /**
     * Add comment
     */
    public function addComment( $oComment )
    {
        $oComment->setPaymentId( $this->getPaymentId() );
        $oComment->save();

        $this->load( $this->getPaymentId() );

        return $oComment;
    }
}