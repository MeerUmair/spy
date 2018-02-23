<?php
class ECGiken_Gmo_Model_Cvs_Source_Cvstype {
    public function toOptionArray() {
        return array(
            array('value' => '00001','label' => Mage::helper('ecggmo')->__('LAWSON')),
            array('value' => '00002','label' => Mage::helper('ecggmo')->__('FamilyMart')),
            array('value' => '00003','label' => Mage::helper('ecggmo')->__('Circle K Sunkus')),
            array('value' => '00005','label' => Mage::helper('ecggmo')->__('MINISTOP')),
            array('value' => '00006','label' => Mage::helper('ecggmo')->__('DailyYAMAZAKI')),
            array('value' => '00007','label' => Mage::helper('ecggmo')->__('Seven-Eleven')),
        );
    }
}
