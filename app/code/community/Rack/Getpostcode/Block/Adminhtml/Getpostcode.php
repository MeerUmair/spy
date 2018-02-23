<?php

class Rack_Getpostcode_Block_Adminhtml_Getpostcode extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_getpostcode';
        $this->_blockGroup = 'getpostcode';
        $this->_headerText = Mage::helper('getpostcode')->__('Postcode List');
        $this->_addButtonLabel = Mage::helper('getpostcode')->__('Add Postcode');
        parent::__construct();
    }

}
