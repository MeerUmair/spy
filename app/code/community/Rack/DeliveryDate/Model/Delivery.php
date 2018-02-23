<?php

class Rack_DeliveryDate_Model_Delivery extends Mage_Sales_Model_Order
{
    public function updateOrder($orderId, $deliveryDate, $deliveryTime)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getId()) {
            return Mage::helper('deliverydate')->__('Order does not exist.');
        }
        if (!Mage::helper('deliverydate')->canEditDeliveryDateTime($order)) {
            return Mage::helper('deliverydate')->__('Can not edit this order.');
        }
        
        $update = array(
            'delivery_date' => $deliveryDate,
            'delivery_time' => $deliveryTime
        );
        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $conn->beginTransaction();
        $conn->update($this->getResource()->getTable('sales/order'), $update, 'entity_id = ' . $order->getId());
        $conn->update($this->getResource()->getTable('sales/order_address'), $update, 'parent_id = ' . $order->getId() . ' AND address_type="shipping"');
        $conn->commit();
        
        return true;
    }

}