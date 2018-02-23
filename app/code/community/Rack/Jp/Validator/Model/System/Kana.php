<?php
class Rack_Jp_Validator_Model_System_Kana
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '1', 'label'=>Mage::helper('adminhtml')->__('Hiragana')),
            array('value' => '0', 'label'=>Mage::helper('adminhtml')->__('Katakana')),
        );
    }

}