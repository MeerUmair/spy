<?php

class Rack_DeliveryDate_Block_Form extends Mage_Core_Block_Template
{
    
    public function isShowDeliveryDate()
    {
        return (1 == Mage::getStoreConfig('deliverydate/date/enable'));
    }
    
    public function isShowDeliveryTime()
    {
        return (1 == Mage::getStoreConfig('deliverydate/time/enable'));
    }
    
    public function getDeliveryDateHtml()
    {
        $dateModel = Mage::getSingleton('deliverydate/date');

        if (true) {
            $deliveryDates = $dateModel->getAvailableDeliveryDatesInTimestamp();
            $calendar = $this->getLayout()->createBlock('deliverydate/calendar');
            $calendar->setDeliveryDates($deliveryDates);

            return $calendar->toHtml();
        } else {
            $holidays = $dateModel->getAvailableDeliveryDates();

            $select = $this->getLayout()->createBlock('core/html_select')
                           ->setName('delivery_date')
                           ->setId('delivery_date')
                           ->setValue($this->getQuote()->getDeliveryDate())
                           ->setOptions($this->_makeOption($holidays));
            return $select->getHtml();
        }
    }
    
    public function getDeliveryTimeHtml()
    {
        $dateModel = Mage::getSingleton('deliverydate/date');
        $times = $dateModel->getAvailableTimeSegment();
        
        $select = $this->getLayout()->createBlock('core/html_select')
                       ->setName('delivery_time')
                       ->setId('delivery_time')
                       ->setValue($this->getQuote()->getDeliveryTime())
                       ->setOptions($this->_makeOption($times));
        return $select->getHtml();
    }
    
    protected function _makeOption($data)
    {
        $options = array(array('label' => $this->__('Not specific'), 'value' => $this->__('Not specific')));
        foreach ($data as $value) {
            $options[] = array('label' => $value, 'value' => $value);
        }
        
        return $options;
    }
    
    public function getQuote()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        } 
        return Mage::getSingleton('checkout/session')->getQuote();      
    }

}