<?php
class Rack_Pdf_Model_Observer
{
    public function addOptionToSelect($observer)
    {
        if ($observer->getEvent()->getBlock()->getId() == 'sales_order_grid') {
            $massBlock = $observer->getEvent()->getBlock()->getMassactionBlock();
            if ($massBlock) {
                $massBlock->addItem('printorder', array(
                    'label' => Mage::helper('pdf')->__('Print Orders'),
                    'url' => Mage::getModel('core/url')->getUrl('pdf/order/massOrderPrint', array('print' => 1)),
                    'confirm' => Mage::helper('pdf')->__('Are you sure to print the selected sales orders?'),
                    ));
                    }
            }
    }
}