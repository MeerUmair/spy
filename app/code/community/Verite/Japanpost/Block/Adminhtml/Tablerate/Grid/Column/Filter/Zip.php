<?php
/**
 * Zip grid column filter
 *
 * @category   Verite
 * @package    Verite_Japanpost
 * @author     
 */
class Verite_Japanpost_Block_Adminhtml_Tablerate_Grid_Column_Filter_Zip
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    /**
     * Get condition
     * 
     * @return array
     */
    public function getCondition()
    {
        $value = trim($this->getValue());
        if ($value == '*') return array('eq' => '');
        else return array('like'=>'%'.$this->_escapeValue($this->getValue()).'%');
    }
}