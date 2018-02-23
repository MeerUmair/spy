<?php
class ECGiken_Gmo_Block_Info_Cvs extends Mage_Payment_Block_Info
{
    protected function _construct() {
        $this->setTemplate('ecggmo/info/cvs.phtml');
    }

    public function getReceiptNoText($code) {
        $gmo = Mage::helper('ecggmo/gmo');
        return $gmo->getReceiptNoText($code);
    }

}
