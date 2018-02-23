<?php
class Rack_Jp_Validator_Model_Customer extends Rack_Jp_Core_Model_Customer
{
    public function validate() {
        $errors = parent::validate();
        
        if($errors === true) {
            $errors = array();
        }
        
        $customerHelper = Mage::helper('validator');

        $customerHelper->validateName($this, $errors);
    	
        if (Mage::getStoreConfig('jpcore/name/usekana')) {
            $customerHelper->validateKana($this, $errors);
        }
        
        Mage::dispatchEvent('validator_customer_validate', array('customer'=>$this, 'error' => $errors));
        
        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
    
    public function getConfigData($key)
    {
        return Mage::getStoreConfig($key);
    }
    
    public function isModuleInstalled($name)
    {
            $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
            if(in_array($name, $modules)) {
                return true;
            }
            return false;
    }
}