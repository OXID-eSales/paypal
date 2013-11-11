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
 * PayPal Current item Article container class
 */
class oePayPalArticleToExpressCheckoutCurrentItem
{
    /**
     * Article id
     * @var string
     */
    protected $_sArticleId;

    /**
     * Select list
     * @var array
     */
    protected $_aSelectList;

    /**
     * Persistent param
     * @var array
     */
    protected $_aPersistParam;

    /**
     * Article amount
     * @var integer
     */
    protected $_iArticleAmount;

    /**
     * Method sets persistent param
     * @param array $aPersistParam
     */
    public function setPersistParam( $aPersistParam )
    {
        $this->_aPersistParam = $aPersistParam;
    }

    /**
     * Method returns persistent param
     *
     * @return array
     */
    public function getPersistParam()
    {
        return $this->_aPersistParam;
    }

    /**
     * Method sets select list
     * @param array $aSelectList
     */
    public function setSelectList( $aSelectList )
    {
        $this->_aSelectList = $aSelectList;
    }

    /**
     * Method returns select list
     *
     * @return array
     */
    public function getSelectList()
    {
        return $this->_aSelectList;
    }

    /**
     * Method sets article id
     * @param string $sArticleId
     */
    public function setArticleId( $sArticleId )
    {
        $this->_sArticleId = $sArticleId;
    }

    /**
     * Method returns article id
     *
     * @return string
     */
    public function getArticleId()
    {
        return $this->_sArticleId;
    }

    /**
     * Method sets article amount
     *
     * @param int $iArticleAmount
     */
    public function setArticleAmount( $iArticleAmount )
    {
        $this->_iArticleAmount = $iArticleAmount;
    }

    /**
     * Method returns article amount
     *
     * @return int
     */
    public function getArticleAmount()
    {
        return ( int ) $this->_iArticleAmount;
    }

}