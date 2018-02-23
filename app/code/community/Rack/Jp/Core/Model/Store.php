<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@principle-works.jp so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize it for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Localize
 * @package    Rack_Jp_Core
 * @copyright  Copyright (c) 2015 Veriteworks Inc. (http://principle-works.jp/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Rack_Jp_Core_Model_Store extends Mage_Core_Model_Store
{
    /**
     * rounding price
     *
     * @param mixed $price
     * @return float
     */
    public function roundPrice($price)
    {
        if(Mage::app()->getStore()->isAdmin() && $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote()) {
            $base = $quote->getBaseCurrencyCode();
            if($currency_id = Mage::app()->getRequest()->getParam('currency_id', null)) {
                $current = $currency_id;
            } else {
                $current = $quote->getQuoteCurrencyCode();
            }

        } else {
            $base = $this->getBaseCurrencyCode();
            $current = $this->getCurrentCurrencyCode();
        }

        $helper = Mage::helper('jpcore');
        if($helper->canRemoveDecimal($base) && $helper->canRemoveDecimal($current)) {
            if(//!Mage::helper('tax')->priceIncludesTax($this->getWebsiteId()) &&
                (Mage::helper('tax')->getCalculationAgorithm($this->getWebsiteId()) != Mage_Tax_Model_Calculation::CALC_ROW_BASE)) {

                $method = Mage::getStoreConfig('tax/calculation/round');
                if($base === $current && $method != 'round') {
                    $price = sprintf('%0.4f',$price);
                    return $method($price);
                } else {
                    return parent::roundPrice($price);
                }
            }
//
        }
        return parent::roundPrice($price);
    }

    /**
     * format price
     *
     * @param float $price
     * @param bool $includeContainer
     * @return float|string
     */
    public function formatPrice($price, $includeContainer = true)
    {
        if(Mage::helper('jpcore')->canRemoveDecimal($this->getCurrentCurrencyCode())) {
            $price = floor($price);
        }
        if ($this->getCurrentCurrency()) {
            return $this->getCurrentCurrency()->format($price, array(), $includeContainer);
        }
        return $price;
    }

}