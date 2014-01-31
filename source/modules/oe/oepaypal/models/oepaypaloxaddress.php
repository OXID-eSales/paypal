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
 * PayPal oxAddress class
 */
class oePayPalOxAddress extends oePayPalOxAddress_parent
{
    /**
     * Creates user shipping address from PayPal data and set to session
     *
     * @param oePayPalResponseGetExpressCheckoutDetails $oDetails - PayPal data
     * @param string $sUserId 	 - user id
     *
     */
    public function createPayPalAddress( $oDetails, $sUserId )
    {
        $aAddressData = $this->_prepareDataPayPalAddress( $oDetails );

        if ( $sAddressId = $this->_existPayPalAddress( $aAddressData ) ){
            oxRegistry::getSession()->setVariable( "deladrid", $sAddressId );
        } else {
            $this->oxaddress__oxuserid 		= new oxField( $sUserId );
            $this->oxaddress__oxfname 		= new oxField( $aAddressData['oxfname'] );
            $this->oxaddress__oxlname 		= new oxField( $aAddressData['oxlname'] );
            $this->oxaddress__oxstreet 		= new oxField( $aAddressData['oxstreet'] );
            $this->oxaddress__oxstreetnr 	= new oxField( $aAddressData['oxstreetnr'] );
            $this->oxaddress__oxaddinfo 	= new oxField( $aAddressData['oxaddinfo'] );
            $this->oxaddress__oxcity 		= new oxField( $aAddressData['oxcity'] );
            $this->oxaddress__oxcountryid 	= new oxField( $aAddressData['oxcountryid'] );
            $this->oxaddress__oxstateid 	= new oxField( $aAddressData['oxstateid'] );
            $this->oxaddress__oxzip 		= new oxField( $aAddressData['oxzip'] );
            $this->oxaddress__oxfon 		= new oxField( $aAddressData['oxfon'] );
            $this->save();

            oxRegistry::getSession()->setVariable( "deladrid", $this->getId() );
        }
    }

     /**
     * Prepare address data array from PayPal response data
     *
     * @param oePayPalResponseGetExpressCheckoutDetails $oDetails - PayPal data
     *
     * @return array
     */
    protected function _prepareDataPayPalAddress( $oDetails )
    {
        $aAddressData = array();

        $oFullName = oxNew( 'oePayPalFullName', $oDetails->getShipToName() );

        $aAddressData['oxfname'] = $oFullName->getFirstName();
        $aAddressData['oxlname'] = $oFullName->getLastName();

        $aStreet = $this->splitShipToStreetPayPalAddress( $oDetails->getShipToStreet() );
        $aAddressData['oxstreet'] = $aStreet['street'];
        $aAddressData['oxstreetnr'] = $aStreet['streetnr'];

        $aAddressData['oxcity'] = $oDetails->getShipToCity();

        $oCountry = oxNew( 'oxCountry' );
        $sCountryId = $oCountry->getIdByCode( $oDetails->getShipToCountryCode() );
        $aAddressData['oxcountryid'] = $sCountryId;

        if ( $oDetails->getShipToState() ) {
            $oState = oxNew( 'oxState' );
            $sStateId = $oState->getIdByCode( $oDetails->getShipToState(), $sCountryId );
        }
        $aAddressData['oxstateid'] = $sStateId;

        $aAddressData['oxzip'] = $oDetails->getShipToZip();
        $aAddressData['oxfon'] = $oDetails->getShipToPhoneNumber();
        $aAddressData['oxaddinfo'] = $oDetails->getShipToStreet2();

        return $aAddressData;
    }

    /**
     * Check required fields
     *
     * @param array  $aAddressData - PayPal data
     *
     * @return bool
     */
    protected function _checkRequiredFieldsPayPalAddress( $aAddressData )
    {
        $aReqFields = $this->getConfig()->getConfigParam( 'aMustFillFields' );

        $blResult = true;

        foreach ($aReqFields as $sField)
        {
            if( strpos( $sField, 'oxaddress__' ) === 0 && empty( $aAddressData[str_replace('oxaddress__', '', $sField)] ) ){
               return false;
            }
        }

        return $blResult;
    }

    protected function _existPayPalAddress( $aAddressData )
    {
        $oDb = oxDb::getDb();

        $sQ = "SELECT `oxid` FROM `oxaddress` WHERE 1 ";
        $sQ .= " AND `oxfname` = " 		. $oDb->quote( $aAddressData['oxfname'] );
        $sQ .= " AND `oxlname` = " 		. $oDb->quote( $aAddressData['oxlname'] );
        $sQ .= " AND `oxstreet` = " 	. $oDb->quote( $aAddressData['oxstreet'] );
        $sQ .= " AND `oxstreetnr` = " 	. $oDb->quote( $aAddressData['oxstreetnr'] );
        $sQ .= " AND `oxcity` = " 		. $oDb->quote( $aAddressData['oxcity'] );
        $sQ .= " AND `oxcountryid` = " 	. $oDb->quote( $aAddressData['oxcountryid'] );
        $sQ .= " AND `oxstateid` = " 	. $oDb->quote( $aAddressData['oxstateid'] );
        $sQ .= " AND `oxzip` = " 		. $oDb->quote( $aAddressData['oxzip'] );
        $sQ .= " AND `oxfon` = "	    . $oDb->quote( $aAddressData['oxfon'] );

        if ( $sAddressId = $oDb->getOne( $sQ ) ){
            return $sAddressId;
        }
        return false;
    }

    /**
     *  Split street nr from address
     *
     * @param string $sShipToStreet address string
     *
     * @return array
     */
    public function splitShipToStreetPayPalAddress( $sShipToStreet )
    {
        $aAddress = array();
        $sShipToStreet = trim( $sShipToStreet );

        // checking if street number is at the end of the address
        preg_match( "/(.*\S)\s+(\d+\s*\S*)$/", $sShipToStreet, $aAddress);

        // checking if street name and number was found
        if ( !empty($aAddress[1]) && $aAddress[2] ) {
            $aAddress['street']   = $aAddress[1];
            $aAddress['streetnr'] = $aAddress[2];

            return $aAddress;
        }

        // checking if street number is at the begining of the address
        preg_match( "/(\d+\S*)\s+(.*)$/", $sShipToStreet, $aAddress);

        // checking if street name and number was found
        if ( !empty($aAddress[1]) && $aAddress[2] ) {
            $aAddress['street']   = $aAddress[2];
            $aAddress['streetnr'] = $aAddress[1];

            return $aAddress;
        }

        // it is not possible to resolve address, so assign it without any parsing
        $aAddress['street']   = $sShipToStreet;
        $aAddress['streetnr'] = "";

        return $aAddress;
    }
}