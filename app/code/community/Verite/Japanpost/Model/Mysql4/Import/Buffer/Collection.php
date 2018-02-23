<?php

class Verite_Japanpost_Model_Mysql4_Import_Buffer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('japanpost/import_buffer');
    }
}