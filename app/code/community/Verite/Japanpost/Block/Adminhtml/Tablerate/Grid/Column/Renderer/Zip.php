<?php
/**
 * Zip grid column renderer
 *
 * @category   Verite
 * @package    Verite_Japanpost
 * @author     
 */
class Verite_Japanpost_Block_Adminhtml_Tablerate_Grid_Column_Renderer_Zip
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Get value
     * 
     * @return string
     */
    public function _getValue(Varien_Object $row)
    {
        $value = parent::_getValue($row);
        if ($value === '') return '*';
        else return $value;
    }
}