<?php
class ECGiken_Gmo_Model_System_Config_Backend_Inquiries extends Mage_Core_Model_Config_Data {
    protected function _beforeSave() {
        $gmo = Mage::helper('ecggmo/gmo');
        $gmo->_debugLog(ECGiken_Gmo_Helper_Gmo::CODE_CVS, '+ Config inquiries +');
        parent::_beforeSave();
        $inquiries = $this->getValue();
        $gmo->_debugLog(ECGiken_Gmo_Helper_Gmo::CODE_CVS, 'inquiries='.$inquiries);
        if(strlen($inquiries) > 42) {
            Mage::throwException(Mage::helper('ecggmo')->__('Inquiries: 42 Characters or less.'));
        }
        return $this;
    }
}
