<?php
class ECGiken_Gmo_Model_Entity_Attribute_Source_Block
{
    public function toOptionArray() {
        $options = array(
            array(
                'label' => Mage::helper('ecggmo')->__("Not display message"),
                'value' => ""
            )
        );
        $collection = Mage::getModel('cms/block')->getCollection();
        foreach ($collection as $block) {
            $options[] = array(
                'label' => $block->getTitle(),
                'value' => $block->getIdentifier()
            );
        }
        return $options;
    }
}
