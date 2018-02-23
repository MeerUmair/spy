<?php
class Rack_DeliveryDate_Model_Observer extends Varien_Object
{
    /**
     * Capture checkout_controller_onepage_save_shipping_method to set delivery date/time data
     * 
     * @param Varien_Event_Observer $ob
     * @return Rack_DeliveryDate_Model_Observer 
     */
    public function saveShippingMethodHandler(Varien_Event_Observer $ob) 
    {
        $request = $ob->getEvent()->getRequest();
        $quote   = $ob->getEvent()->getQuote();
        
        $deliveryDate = $request->getParam('delivery_date');
        $deliveryTime = $request->getParam('delivery_time');
        
        if (is_array($deliveryDate)) {
            foreach ($deliveryDate as $entityId=>$delivery) {
                $entity = $quote->getAddressById($entityId);
                $entity->setDeliveryDate($delivery);
                $entity->save();
            }
        } else {
            $entity = $quote;
            $entity->setDeliveryDate($deliveryDate);
        }
        
        if (is_array($deliveryTime)) {            
            foreach ($deliveryTime as $entityId=>$delivery) {
                $entity = $quote->getAddressById($entityId);
                $entity->setDeliveryTime($delivery);
                $entity->save();
            }
        } else {
            $entity = $quote;
            $entity->setDeliveryTime($deliveryTime);
        }

        $entity->save();
        
        return $this;
    }
    
    /**
     * Capture event sales_convert_quote_to_order to set delivery date/time to order
     * 
     * @param Varien_Event_Observer $ob
     * @return Rack_DeliveryDate_Model_Observer 
     */
    public function salesEventConvertQuoteToOrder($observer)
    {
        $quote = $observer->getEvent()->getQuote();      
        $address = $quote->getShippingAddress();

        if ($quote->getDeliveryDate()) {
            $address->setDeliveryDate($quote->getDeliveryDate());
        }
        if ($quote->getDeliveryTime()) {
            $address->setDeliveryTime($quote->getDeliveryTime());
        }
        $address->save();
        return $this;
    }
    
    /**
     * Capture event sales_convert_quote_address_to_order to set delivery date/time 
     * from quote address to order. This use for multiple shipping.
     * 
     * @param Varien_Event_Observer $ob
     * @return Rack_DeliveryDate_Model_Observer 
     */
    public function salesEventConvertQuoteAddressToOrder($ob)
    {
        $address = $ob->getEvent()->getAddress();
        $order   = $ob->getEvent()->getOrder();        

        $order->setDeliveryDate($address->getDeliveryDate());
        $order->setDeliveryTime($address->getDeliveryTime());

        return $this;
    }
    
    /**
     * Rertive post data from adminhtml and save deliverydate information.
     * 
     * @param Varien_Event_Observer $ob
     * @return Rack_DeliveryDate_Model_Observer 
     */
    public function saveShippingMethodHandlerForAdmin(Varien_Event_Observer $ob) 
    {
        $request = $ob->getEvent()->getRequest();
        $quote   = $ob->getEvent()->getOrderCreateModel()->getQuote();
        
        $deliveryDate = $deliveryTime = '';
        if (isset($request['delivery_date'])) {
            $deliveryDate = $request['delivery_date'];
        }
        if (isset($request['delivery_time'])) {
            $deliveryTime = $request['delivery_time'];
        }
        
        if ($deliveryDate == '' && $deliveryTime == '') {
            return $this;
        }
        
        if (is_array($deliveryDate)) {
            foreach ($deliveryDate as $entityId=>$delivery) {
                $entity = $quote->getAddressById($entityId);
                $entity->setDeliveryDate($delivery);
                $entity->save();
            }
        } else {
            $entity = $quote;
            $entity->setDeliveryDate($deliveryDate);
        }
        
        if (is_array($deliveryTime)) {            
            foreach ($deliveryTime as $entityId=>$delivery) {
                $entity = $quote->getAddressById($entityId);
                $entity->setDeliveryTime($delivery);
                $entity->save();
            }
        } else {
            $entity = $quote;
            $entity->setDeliveryTime($deliveryTime);
        }

        $entity->save();
        
        return $this;
    }

}