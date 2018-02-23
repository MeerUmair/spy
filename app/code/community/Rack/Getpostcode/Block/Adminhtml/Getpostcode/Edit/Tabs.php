<?php

class Rack_Getpostcode_Block_Adminhtml_Getpostcode_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('getpostcode_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('getpostcode')->__('Postcode Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('getpostcode')->__('Postcode Information'),
            'title'     => Mage::helper('getpostcode')->__('Postcode Information'),
            'content'   => $this->getLayout()->createBlock('getpostcode/adminhtml_getpostcode_edit_tab_form')->toHtml(),
        ));
     
        return parent::_beforeToHtml();
    }
}