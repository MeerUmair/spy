<?php

class Rack_Jp_Core_Block_Adminhtml_Directory_Grid extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_grid';
        $this->_blockGroup = 'directory';
        $this->_headerText = Mage::helper('jpcore')->__('Currency List');
        $this->_addButtonLabel = Mage::helper('jpcore')->__('Add Currency Setting');
        parent::__construct();
    }
}