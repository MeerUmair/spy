<?php

class Rack_DeliveryDate_Helper_Data extends Mage_Core_Helper_Abstract
{    
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }
    
    public function getAddress($entityId)
    {
        return $this->getQuote()->getAddressById($entityId);
    }

    public function isShowDeliveryDate()
    {
        return (1 == Mage::getStoreConfig('deliverydate/date/enable'));
    }
    
    public function isShowDeliveryTime()
    {
        return (1 == Mage::getStoreConfig('deliverydate/time/enable'));
    }
    
    public function isDisallowSpecificShippingMethods4Date()
    {
        return (1 == Mage::getStoreConfig('deliverydate/date/disallowspecificshippingmethods'));
    }
    
    public function getDisallowShippingMethods4Date()
    {
        if ($this->isDisallowSpecificShippingMethods4Date()) {
            $shippingMethods = Mage::getStoreConfig('deliverydate/date/disallowedshippingmethods');
            return str_replace(',', ' ', $shippingMethods);
        }
    }
    
    public function isDisallowSpecificShippingMethods4Time()
    {
        return (1 == Mage::getStoreConfig('deliverydate/time/disallowspecificshippingmethods'));
    }
    
    public function getDisallowShippingMethods4Time()
    {
        if ($this->isDisallowSpecificShippingMethods4Time()) {
            $shippingMethods = Mage::getStoreConfig('deliverydate/time/disallowedshippingmethods');
            return str_replace(',', ' ', $shippingMethods);
        }
    }
    
    /**
     * Return minimum require calculation type
     * @return int
     */
    public function getMindayType()
    {
        return (int)Mage::getStoreConfig('deliverydate/minday/type');
    }
    
    /**
     * Get minimum required days for specific day of week
     * 
     * @param string $dow Day of week
     * @param string $seg am/pm
     * @return int
     */
    public function getDailyMinRequired($dow, $seg)
    {
        $key = "deliverydate/minday/{$dow}_{$seg}";
        
        return (int)Mage::getStoreConfig($key);
    }

    /**
     * Retrive inline delivery date edit form for specified entity
     *
     * @param Varien_Object $entity
     * @return string
     */
    public function getDeliveryDateInline(Varien_Object $entity = NULL)
    {                
        $dateModel = Mage::getSingleton('deliverydate/date');
        $holidays = $dateModel->getAvailableDeliveryDates();

        if (NULL === $entity) {            
            $name   = 'delivery_date';
            $id     = 'delivery_date';
            $value  = $this->getQuote()->getDeliveryDate();
        } else {
            $name   = 'delivery_date['.$entity->getId().']';
            $id     = 'delivery_date_' . $entity->getId();
            $value  = $this->getAddress($entity->getId())->getDeliveryDate();
        }

        if ($this->isUseCalendar()) {
            $deliveryDates = $dateModel->getAvailableDeliveryDatesInt();
            $calendar = $this->getLayout()->createBlock('deliverydate/calendar');
            $calendar->setDeliveryDates($deliveryDates);
            $calendar->setHtmlId($id)
                     ->setCurrentValue($value)
                     ->setHtmlFieldName($name);

            return $calendar->toHtml();
        } else {
            $select = Mage::getSingleton('core/layout')->createBlock('core/html_select')
                           ->setTitle($this->__('Delivery Date'))
                           ->setName($name)
                           ->setId($id)
                           ->setValue($value)
                           ->setOptions($this->_makeOption($holidays));

            return $select->getHtml();
        }
    }
    
    /**
     * Retrive inline delivery time edit form for specified entity
     *
     * @param Varien_Object $entity
     * @return string
     */
    public function getDeliveryTimeInline(Varien_Object $entity = NULL)
    {
        $dateModel = Mage::getSingleton('deliverydate/date');
        $times = $dateModel->getAvailableTimeSegment();

        if (NULL === $entity) {            
            $name   = 'delivery_time';
            $id     = 'delivery_time';
            $value  = $this->getQuote()->getDeliveryTime();
        } else {
            $name   = 'delivery_time['.$entity->getId().']';
            $id     = 'delivery_time_' . $entity->getId();
            $value  = $this->getAddress($entity->getId())->getDeliveryTime();
        }
        
        $select = Mage::getSingleton('core/layout')->createBlock('core/html_select')
                       ->setName($name)
                       ->setId($id)
                       ->setValue($value)
                       ->setOptions($this->_makeOption($times));
        return $select->toHtml();
    }
    
    protected function _makeOption($data)
    {
        $options = array(array('label' => $this->__('Not specific'), 'value' => $this->__('Not specific')));
        foreach ($data as $value) {
            $options[] = array('label' => $value, 'value' => $value);
        }
        
        return $options;
    }
    
    /**
     * Retrive delivery date
     *
     * @param Varien_Object $entity
     * @return string
     */
    public function getDeliveryDate(Varien_Object $entity = NULL)
    {                
        if (NULL === $entity) {            
            $value  = $this->getQuote()->getDeliveryDate();
        } else {
            $value  = $this->getAddress($entity->getId())->getDeliveryDate();
        }
        return $value;
    }
    
    /**
     * Retrive delivery time
     *
     * @param Varien_Object $entity
     * @return string
     */
    public function getDeliveryTime(Varien_Object $entity = NULL)
    {                
        if (NULL === $entity) {            
            $value  = $this->getQuote()->getDeliveryTime();
        } else {
            $value  = $this->getAddress($entity->getId())->getDeliveryTime();
        }
        return $value;
    }
    
    /**
     * Check order state for edit delivery date, time
     * If order is complete, closed, or cancel, user can not edit
     * 
     * @param Mage_Sales_Model_Order $_order 
     */
    public function canEditDeliveryDateTime($_order)
    {
        $states = array(Mage_Sales_Model_Order::STATE_CANCELED, Mage_Sales_Model_Order::STATE_CLOSED, Mage_Sales_Model_Order::STATE_COMPLETE);
        if (in_array($_order->getState(), $states)) {
            return false;
        }
        
        return true;
    }

    public function getJsDateFormat()
    {
        $format = Mage::getStoreConfig('deliverydate/date/display_format');
        $search = array('Y', 'y', 'm', 'd');
        $replace = array('%Y', '%y', '%m', '%d');

        return str_replace($search, $replace, $format);

    }

    public function isUseCalendar()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            //return false;
        }
        return (1 == Mage::getStoreConfig('deliverydate/date/use_calendar'));
    }

    public function convertDateToIntStyle($date)
    {
        if (empty($date)) {
            return false;
        }
        $format = Mage::getStoreConfig('deliverydate/date/display_format');
        $pattern = array('d' => '(\d{2})', 'y' => '(\d{2})', 'm' => '(\d{2})', 'Y' => '(\d{4})');
        $reg = $format;
        $matches = array();
        foreach ($pattern as $key => $v) {
            $pos = strpos($format, $key);
            if ($pos !== false) {
                $reg = str_replace($key, $v, $reg);
                $matches[$key] = $pos;
            }
        }
        $reg = '/' . $reg . '/';

        $results = array();
        if (preg_match($reg, $date, $results)) {
            asort($matches);
            $idx = 1;
            $order = array('Y' => '', 'y' => '', 'm' => '', 'd' => '');
            foreach ($matches as $key => $v) {
                $val = $results[$idx];
                if ($key == 'y') {
                    $val .= '20' . $val;
                }
                $order[$key] = $val;
                $idx++;
            }

            return implode($order);
        }

        return false;
    }

    public function isExcludeSatSun()
    {
        return (1 == Mage::getStoreConfig('deliverydate/date/exclude_satsun'));
    }
}