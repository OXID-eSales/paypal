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
 * Abstract model class
 */
abstract class oePayPalModel
{
    /**
     * Data base gateway
     *
     * @var oePayPalPayPalDbGateway
     */
    protected $_oDbGateway = null;

    /**
     * Model data
     *
     * @var array
     */
    protected $_aData = null;

    /**
     * Was object information found in database
     *
     * @var bool
     */
    protected $_blIsLoaded = false;

    /**
     * Set response data
     *
     * @param array $aData model data
     */
    public function setData( $aData )
    {
        $aData = array_change_key_case( $aData, CASE_LOWER );
        $this->_aData = $aData;
    }

    /**
     * Return response data
     *
     * @return array
     */
    public function getData()
    {
        return $this->_aData;
    }

    /**
     * Return value from data by given key
     *
     * @param string $sKey key of data value
     *
     * @return string
     */
    protected function _getValue( $sKey )
    {
        $aData = $this->getData();
        return $aData[$sKey];
    }

    /**
     * Return value from data by given key
     *
     * @param string $sKey key of data value
     * @param string $sValue data value
     *
     * @return string
     */
    protected function _setValue( $sKey, $sValue )
    {
        $this->_aData[ $sKey ] = $sValue;
    }

    /**
     * Returns model database gateway
     *
     * @var object
     */
    abstract protected function _getDbGateway();

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
     * Method for model saving (insert and update data)
     *
     * @return int|false
     */
    public function save()
    {
        $mId = $this->_getDbGateway()->save( $this->getData() );
        $this->setId( $mId );
        return $mId;
    }

    /**
     * Delete model data from db
     *
     * @param string $sId model id
     *
     * @return bool
     */
    public function delete( $sId = null )
    {
        if ( !is_null( $sId ) ) {
            $this->setId( $sId );
        }

        return $this->_getDbGateway()->delete( $this->getId() );
    }

    /**
     * Method for loading model, if loaded returns true
     *
     * @param string $sId model id
     *
     * @return bool
     */
    public function load( $sId = null )
    {
        if ( !is_null( $sId ) ) {
            $this->setId( $sId );
        }

        $this->_blIsLoaded = false;
        $aData = $this->_getDbGateway()->load( $this->getId() );
        if ( $aData ) {
            $this->setData( $aData );
            $this->_blIsLoaded = true;
        }

        return $this->isLoaded();
    }

    /**
     * Returns whether object information found in database
     */
    public function isLoaded()
    {
        return $this->_blIsLoaded;
    }

    /**
     * Abstract method for delete model
     *
     * @param string $sId model id
     */
    abstract public function setId( $sId );

    /**
     * Abstract method for getting id
     */
    abstract public function getId();
}