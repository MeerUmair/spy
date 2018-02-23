<?php

class Rack_Getpostcode_Model_Mysql4_Postcode extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('getpostcode/postcode', 'id');
    }
}