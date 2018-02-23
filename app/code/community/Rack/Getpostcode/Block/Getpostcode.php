<?php
class Rack_Getpostcode_Block_Getpostcode extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    /**
     * Get post code data
     */
    public function getGetpostcode()
    { 
        if (!$this->hasData('postcode')) {
            $this->setData('postcode', Mage::registry('postcode'));
        }
        return $this->getData('postcode');
    }
}