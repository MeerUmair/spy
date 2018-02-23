<?php

class Verite_Japanpost_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('japanpost/log', 'log_id');
    }
}