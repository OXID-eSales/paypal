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

namespace OxidEsales\PayPalModule\Component\Widget;

/**
 * Article box widget
 *
 * @mixin \OxidEsales\Eshop\Application\Component\Widget\ArticleDetails
 */
class ArticleDetails extends ArticleDetails_parent
{
    /**
     * Returns products amount to .tpl pages.
     *
     * @return int
     */
    public function oePayPalGetArticleAmount()
    {
        $article = $this->oePayPalGetECSArticle();

        return isset($article['am']) ? (int) $article['am'] : 1;
    }

    /**
     * Returns persistent parameter.
     *
     * @return string
     */
    public function oePayPalGetPersistentParam()
    {
        $article = $this->oePayPalGetECSArticle();

        return $article['persparam']['details'];
    }

    /**
     * Returns selections array.
     *
     * @return array
     */
    public function oePayPalGetSelection()
    {
        $article = $this->oePayPalGetECSArticle();

        return $article['sel'];
    }

    /**
     * Checks if showECSPopup parameter was passed.
     *
     * @return bool
     */
    public function oePayPalShowECSPopup()
    {
        return $this->getComponent('oxcmp_basket')->shopECSPopUp();
    }

    /**
     * Checks if showECSPopup parameter was passed.
     *
     * @return bool
     */
    public function oePayPalGetCancelUrl()
    {
        return $this->getComponent('oxcmp_basket')->getPayPalCancelURL();
    }

    /**
     * Checks if displayCartInPayPal parameter was passed.
     *
     * @return bool
     */
    public function oePayPalDisplayCartInPayPal()
    {
        $displayCartInPayPal = false;
        if ($this->oePayPalGetRequest()->getPostParameter('displayCartInPayPal')) {
            $displayCartInPayPal = true;
        }

        return $displayCartInPayPal;
    }

    /**
     * Method returns request object.
     *
     * @return \OxidEsales\PayPalModule\Core\Request
     */
    protected function oePayPalGetRequest()
    {
        return oxNew(\OxidEsales\PayPalModule\Core\Request::class);
    }

    /**
     * Gets ECSArticle, unserializes and returns it.
     *
     * @return array
     */
    protected function oePayPalGetECSArticle()
    {
        $products = $this->getComponent('oxcmp_basket')->getCurrentArticleInfo();

        return $products;
    }
}
