<?php

class Verite_Japanpost_Model_Observer
{
    public function addExtraItems(Varien_Event_Observer $ob)
    {
        /**
         * @var $block Mage_Adminhtml_Block_Template
         */
        $block = $ob->getBlock();
        $name = $block->getNameInLayout();

        if ($name == 'sales_order.grid') {
            $block->getMassactionBlock()->addItem('japanpost_export', array(
                'label'=> Mage::helper('japanpost')->__('Export Selected To Japanpost'),
                'url'  =>  Mage::helper('adminhtml')->getUrl('*/japanpost_export/export'),
            ));
        }
    }
}