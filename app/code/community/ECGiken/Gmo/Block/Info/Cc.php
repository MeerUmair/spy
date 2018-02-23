<?php
class ECGiken_Gmo_Block_Info_Cc extends Mage_Payment_Block_Info_Cc
{
    protected function _construct() {
        $this->setTemplate('ecggmo/info/cc.phtml');
    }

}
