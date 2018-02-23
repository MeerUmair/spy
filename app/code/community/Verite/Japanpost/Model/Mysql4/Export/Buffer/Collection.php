<?php

class Verite_Japanpost_Model_Mysql4_Export_Buffer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('japanpost/export_buffer');
    }
}