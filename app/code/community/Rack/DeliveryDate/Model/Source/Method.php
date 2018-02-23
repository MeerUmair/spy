<?php

class Rack_DeliveryDate_Model_Source_Method
{
    public function toOptionArray()
    {
        $options =  array();

        foreach (Mage::app()->getStore()->getConfig('carriers') as $code => $carrier) {            
            if (isset($carrier['title'])){
                $_code = '';
                if($code == 'tablerate') {
                    $_code = $code . '_bestway';
                } elseif($code == 'pickup') {
                    $_code = $code . '_store';
                } else {
                    $_code = $code . '_' . $code;
                }
                
                $options[] = array(
                    'value' => $_code,
                    'label' => $carrier['title']
                );
            }
        }
        $this->_getMatrixRates($options);
        //$this->_getPremiumRates($options);
        return $options;
    }
    
    protected function _getMatrixRates(&$options)
    {
        if(Mage::getStoreConfig('carriers/matrixrate/active')){
            $matrixrate = Mage::getResourceModel('matrixrate_shipping/carrier_matrixrate');
            $con = $matrixrate->getReadConnection();
            $select = $con->select()->
                      from(Mage::getSingleton('core/resource')->getTableName('matrixrate_shipping/matrixrate'))->
                      columns(array('pk', 'delivery_type'))->group('pk');
            $row = $con->fetchAll($select);
            if (!empty($row))
            {
                foreach ($row as $data) {
                    $options[] = array(
                        'value' => 'matrixrate_matrixrate_' . $data['pk'],
                        'label' => $data['delivery_type']
                    );
                }
            }
            
        }
    }
    
    protected function _getPremiumRates(&$options)
    {
        if(Mage::getStoreConfig('carriers/premiumrate/active')){
            $matrixrate = Mage::getResourceModel('premiumrate_shipping/carrier_premiumrate');
            $con = $matrixrate->getReadConnection();
            $select = $con->select()->
                      from(Mage::getSingleton('core/resource')->getTableName('premiumrate_shipping/premiumrate'))->
                      columns(array('pk', 'delivery_type'))->group('pk');
            $row = $con->fetchAll($select);
            if (!empty($row))
            {
                foreach ($row as $data) {
                    $options[] = array(
                        'value' => 'premiumrate_premiumrate_' . str_replace(' ', '_', $data['delivery_type']),
                        'label' => $data['delivery_type']
                    );
                }
            }
            
        }
    }
}