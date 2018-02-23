<?php
class Rack_DeliveryDate_Model_Source_Minday
{
    const TYPE_FIXED    = 1; 
    const TYPE_DBD      = 2; //day by day;
    
    public function toOptionArray()
    {
        $options =  array(
            self::TYPE_FIXED    => Mage::helper('deliverydate')->__('Type fixed'),
            self::TYPE_DBD      => Mage::helper('deliverydate')->__('Type day by day')
        );

        return $options;
    }
}