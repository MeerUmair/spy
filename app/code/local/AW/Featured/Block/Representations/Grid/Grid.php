<?php

// /var/www/html/shop.spy-online.jp/app/code/local/AW/Featured/Block/Representations/Grid

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Featured
 * @version    3.6.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Featured_Block_Representations_Grid_Grid extends AW_Featured_Block_Representations_Common
{
    protected function _beforeToHtml()
    {
        $AFPBlock = $this->getAFPBlock();
    	$block_id = $AFPBlock->_origData['id'];
    	$autoposition = $AFPBlock->_origData['autoposition'];
    	$automation_type = $AFPBlock->_origData['automation_type'];
    	
    	$rank_ids = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,19);
    	
        /*if(isset($_GET['test'])){
        	echo '<!-- getGridId <pre>';
        	print_r($block_id);
        	print_r($autoposition);
        	print_r($automation_type);
        	echo '</pre>-->';
        }*/
        
        //$this->setTemplate('aw_featured/grid/grid.phtml');
        if(in_array($block_id, $rank_ids)){
        	$this->setTemplate('aw_featured/grid/grid_ranking.phtml');
        }else{
        	$this->setTemplate('aw_featured/grid/grid.phtml');
        }
        
        return parent::_beforeToHtml();
    }

    public function getContainerStyleString()
    {
        $res = sprintf('width: %s;', $this->getWidth());
        if ($this->getHeight()) {
            $res .= sprintf('height: %spx;', $this->getHeight());
        }
        if ($this->getBackgroundColor()) {
            $res .= sprintf('background-color: %s;', $this->getBackgroundColor());
        }
        if ($this->getBorder()) {
            $res .= sprintf('border: %s;', $this->getBorder());
        }
        if ($this->getBorderRadius()) {
            $res .= sprintf('border-radius: %spx;', $this->getBorderRadius());
        }
        return $res;
    }

    public function getContainerItemStyleString()
    {
        $res = sprintf('width: %s;', $this->getWidth());
        if ($this->getHeight()) {
            $res .= sprintf('height: %spx;', $this->getHeight());
        }
        return $res;
    }

    public function getItemsPerRow()
    {
        $_ppr = 1;
        if ($this->getAFPBlockTypeData('productsinrow') && $this->getAFPBlockTypeData('productsinrow') > 0) {
            $_ppr = $this->getAFPBlockTypeData('productsinrow');
        }
        return $_ppr;
    }

    public function getItemWidth()
    {
        return max(array(floor((100-$this->getItemsPerRow())/$this->getItemsPerRow()), 10));
    }

    public function getShowProductName()
    {
        return (bool)$this->getAFPBlockTypeData('showproductname');
    }

    public function getShowDetails()
    {
        return (bool)$this->getAFPBlockTypeData('showdetails');
    }

    public function getDetailsFromProduct(Mage_Catalog_Model_Product $product)
    {
        $shortDescriptionAttribute = Mage::getSingleton('eav/config')->getAttribute(
            'catalog_product', 'short_description'
        );
        if (null === $shortDescriptionAttribute->getData('is_html_allowed_on_front')
            || null === $shortDescriptionAttribute->getData('is_wysiwyg_enabled')) {
            Mage::getSingleton('eav/config')->clear();
        }
        $shortDescription = Mage::helper('catalog/output')->productAttribute(
            $product, nl2br($product->getShortDescription()), 'short_description'
        );
        return $shortDescription;
    }

    public function getShowPrice()
    {
        return (bool)$this->getAFPBlockTypeData('showprice');
    }

    public function getShowAddToCartButton()
    {
        return (bool)$this->getAFPBlockTypeData('showaddtocart');
    }

    public function getShowRating()
    {
        return (bool)$this->getAFPBlockTypeData('showrating');
    }

    public function getWidth()
    {
        return $this->getAFPBlockTypeData('width') ? $this->getAFPBlockTypeData('width').'px' : 'auto';
    }

    public function getHeight()
    {
        return $this->getAFPBlockTypeData('height');
    }

    public function getBackgroundColor()
    {
        return $this->getAFPBlockTypeData('background_color');
    }

    public function getBorder()
    {
        return $this->getAFPBlockTypeData('border');
    }

    public function getBorderRadius()
    {
        return $this->getAFPBlockTypeData('border_radius');
    }
}
