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
 * PayPal Current item Article container class.
 */
class ArticleToExpressCheckoutCurrentItem
{
    /**
     * Article id
     *
     * @var string
     */
    protected $articleId;

    /**
     * Select list
     *
     * @var array
     */
    protected $selectList;

    /**
     * Persistent param
     *
     * @var array
     */
    protected $persistParam;

    /**
     * Article amount
     *
     * @var integer
     */
    protected $articleAmount;

    /**
     * Method sets persistent param.
     *
     * @param array $persistParam
     */
    public function setPersistParam($persistParam)
    {
        $this->persistParam = $persistParam;
    }

    /**
     * Method returns persistent param.
     *
     * @return array
     */
    public function getPersistParam()
    {
        return $this->persistParam;
    }

    /**
     * Method sets select list.
     *
     * @param array $selectList
     */
    public function setSelectList($selectList)
    {
        $this->selectList = $selectList;
    }

    /**
     * Method returns select list.
     *
     * @return array
     */
    public function getSelectList()
    {
        return $this->selectList;
    }

    /**
     * Method sets article id.
     *
     * @param string $articleId
     */
    public function setArticleId($articleId)
    {
        $this->articleId = $articleId;
    }

    /**
     * Method returns article id.
     *
     * @return string
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * Method sets article amount.
     *
     * @param int $articleAmount
     */
    public function setArticleAmount($articleAmount)
    {
        $this->articleAmount = $articleAmount;
    }

    /**
     * Method returns article amount.
     *
     * @return int
     */
    public function getArticleAmount()
    {
        return (int) $this->articleAmount;
    }
}
