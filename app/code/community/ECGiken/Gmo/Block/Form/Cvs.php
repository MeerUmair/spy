<?php
class ECGiken_Gmo_Block_Form_Cvs extends Mage_Payment_Block_Form {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('ecggmo/form/cvs.phtml');
    }

    public function getCvsAvailableTypes() {
        $arrTypes = array(
            '00001'=>$this->__('LAWSON'),
            '00002'=>$this->__('FamilyMart'),
            '00003'=>$this->__('Circle K Sunkus'),
            '00005'=>$this->__('MINISTOP'),
            '00006'=>$this->__('DailyYAMAZAKI'),
            '00007'=>$this->__('Seven-Eleven'),
        );
        $arrCvs = array();
        $configval = explode(',', Mage::getStoreConfig('payment/ecggmo_cvs/cvstypes'));
        foreach($configval as $val) {
            $arrCvs[$arrTypes[$val]] = $val;
        }
        return $arrCvs;
    }

    public function limitDate() {
        $limit = Mage::getStoreConfig('payment/ecggmo_cvs/payment_term');
        return date('Y/m/d 23:59', strtotime('+'.$limit.'day'));
    }
/*
    protected function _toHtml() {
        Mage::dispatchEvent('payment_form_block_to_html_before', array(
            'block'     => $this
        ));
        return parent::_toHtml();
    }*/
}
