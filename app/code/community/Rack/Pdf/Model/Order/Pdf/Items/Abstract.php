<?php
abstract class Rack_Pdf_Model_Order_Pdf_Items_Abstract extends Mage_Sales_Model_Order_Pdf_Items_Abstract
{

    public function getItemOptions() {
        $result = array();
        if ($options = $this->getOrderItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }
        return $result;
    }


    public function getSku($item)
    {
        if ($this->getOrderItem($item)->getProductOptionByCode('simple_sku'))
            return $this->getOrderItem($item)->getProductOptionByCode('simple_sku');
        else
            return $item->getSku();
    }
    
    protected function getOrderItem($item = null) {
    	if($item instanceof Mage_Sales_Model_Order_Item) {
    		return $item;
    	}
    	if($item !== null) {
    		return $item->getOrderItem();
    	}
    	
    	if($this->getItem() instanceof Mage_Sales_Model_Order_Item) {
    		return $this->getItem();
    	}
    	return $this->getItem()->getOrderItem();
    }
    
    protected function _drawItem(Varien_Object $item, Zend_Pdf_Page $page, Mage_Sales_Model_Order $order)
    {
        $type = $item->getProductType();
        $renderer = $this->_getRenderer($type);
        $renderer->setOrder($order);
        $renderer->setItem($item);
        $renderer->setPdf($this);
        $renderer->setPage($page);
        $renderer->setRenderedModel($this);

        $renderer->draw();
    }
    
    protected function _setFontRegular($size = 7)
    {
        $fontFile = Mage::getModel('pdf/file', Rack_Pdf_Model_File::PDF_FONT_REGULER);
        $font = Zend_Pdf_Font::fontWithPath($fontFile->getPath());
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($size = 7)
    {
        $fontFile = Mage::getModel('pdf/file', Rack_Pdf_Model_File::PDF_FONT_BOLD);
        $font = Zend_Pdf_Font::fontWithPath($fontFile->getPath());
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($size = 7)
    {
        $fontFile = Mage::getModel('pdf/file', Rack_Pdf_Model_File::PDF_FONT_ITALIC);
        $font = Zend_Pdf_Font::fontWithPath($fontFile->getPath());
        $this->getPage()->setFont($font, $size);
        return $font;
    }

}