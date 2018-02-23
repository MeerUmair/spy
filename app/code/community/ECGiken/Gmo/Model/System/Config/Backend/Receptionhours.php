<?php
class ECGiken_Gmo_Model_System_Config_Backend_Receptionhours extends Mage_Core_Model_Config_Data {
    protected function _beforeSave() {
        $gmo = Mage::helper('ecggmo/gmo');
        $gmo->_debugLog(ECGiken_Gmo_Helper_Gmo::CODE_CVS, '+ Config receptionhours +');
//        $log_header = "[ECGiken_Gmo_Model_System_Config_Backend_Execenv] ";
        parent::_beforeSave();
        $hours = $this->getValue();
        $gmo->_debugLog(ECGiken_Gmo_Helper_Gmo::CODE_CVS, 'receptionhours='.$hours);
        if(!preg_match('/^[0-9][0-9]:[0-9][0-9]-[0-9][0-9]:[0-9][0-9]$/', $hours)) {
            Mage::throwException(Mage::helper('ecggmo')->__('Reception hours: invalid format [hh:mm-hh:mm]'));
        }
        return $this;
    }
}
