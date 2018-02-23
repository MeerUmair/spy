<?php
class Rack_Jp_Validator_Model_Address extends Mage_Customer_Model_Address
{
    
    
    public function validate() {
        $helper = Mage::helper('validator');
        $this->implodeStreetAddress();
        if($this->getConfigData(Rack_Jp_Validator_Helper_Data::ADDRESS_FULL_WIDTH_ONLY)) {
            $helper->convertStreetToFullWidth($this);
            $helper->convertCityToFullWidth($this);
        }
        
        $errors = parent::validate();
        
        if($errors === true) {
            $errors = array();
        } 
        
        $helper->validateName($this, $errors);
    	
        if (Mage::getStoreConfig('jpcore/name/usekana')) {
            $helper->validateKana($this, $errors);
        }
        
        $helper->validateAddress($this, $errors);
        $helper->validatePostcode($this, $errors);
        $helper->validateTel($this, $errors);
        $helper->validateFax($this, $errors);
        
        Mage::dispatchEvent('validator_address_validate', array('address'=>$this, 'error' => $errors));
        
        if (empty($errors) || $this->getShouldIgnoreValidation()) {
            return true;
        }
        return $errors;
    }
    
    
    
    public function getConfigData($key)
    {
        return Mage::getStoreConfig($key);
    }
}