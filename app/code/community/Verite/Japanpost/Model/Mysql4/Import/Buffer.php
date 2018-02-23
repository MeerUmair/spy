<?php

class Verite_Japanpost_Model_Mysql4_Import_Buffer extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('japanpost/import_buffer', 'id');
    }

}