<?php
/**
 * Magento
 *
 * @category    Verite
 * @package     Verite_Japanpost
 * @copyright   
 * @license 
 */

/**
 * Export button renderer
 *
 * @category    Verite
 * @package     Verite_Japanpost
 * @author
 */
class Verite_Japanpost_Block_System_Config_Form_Export
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{   
    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button');

        $params = array(
            'website' => $buttonBlock->getRequest()->getParam('website')
        );

        $data = array(
            'label'     => Mage::helper('adminhtml')->__('Export CSV'),
            'onclick'   => 'setLocation(\''.Mage::helper('adminhtml')->getUrl("japanpost/config/exportJapanpost", $params) . 'conditionName/\' + $(\'carriers_japanpost_condition_name\').value + \'/japanpost.csv\' )',
            'class'     => '',
        );

        $html = $buttonBlock->setData($data)->toHtml();

        return $html;
    }   
}
