<?php
class ECGiken_Gmo_Model_System_Config_Backend_Phone extends Mage_Core_Model_Config_Data {
    protected function _beforeSave() {
        $gmo = Mage::helper('ecggmo/gmo');
        $gmo->_debugLog(ECGiken_Gmo_Helper_Gmo::CODE_CVS, '+ Config contact phone number +');
        parent::_beforeSave();
        $tel = $this->getValue();
        $gmo->_debugLog(ECGiken_Gmo_Helper_Gmo::CODE_CVS, 'contact phone number='.$tel);
        if(!preg_match('/^[0-9]+$/', $tel)) {
            Mage::throwException(Mage::helper('ecggmo')->__('Contact phone number: There is a non-numeric characters.'));
        }
        if(strlen($tel) > 12) {
            Mage::throwException(Mage::helper('ecggmo')->__('Contact phone number: 12 Characters or less.'));
        }
        return $this;
    }
}
