<?php

class Verite_Japanpost_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'japanpost';
        $this->_controller = 'adminhtml_log';
        $this->_headerText = $this->__('Import / Export log');

        $this->_removeButton('add');
    }
}