<?php
/**
 * Japanpost table rate edit form block
 * 
 * @category   Verite
 * @package    Verite_Japanpost
 * @author     
 */
class Verite_Japanpost_Block_Adminhtml_Tablerate_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Retrieve admin session model
     *
     * @return Mage_Admin_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('admin/session');
    }
    /**
     * Retrieve shipping table rate helper
     *
     * @return Verite_Japanpost_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('japanpost');
    }
	/**
     * Retrieve registered shipping table rate model
     *
     * @return Verite_Japanpost_Model_Tablerate
     */
    protected function getModel()
    {
        return Mage::registry('japanpost_tablerate');
    }
	/**
     * Check is allowed action
     * 
     * @param   string $action
     * @return  bool
     */
    protected function isAllowedAction($action)
    {
        return $this->getSession()->isAllowed('sales/shipping/tablerates/'.$action);
    }
    /**
     * Get destination country values
     * 
     * @return array
     */
    protected function getDestCountryValues()
    {
        $countries = Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(false);
        if (isset($countries[0])) $countries[0]['label'] = '*';
        return $countries;
    }
    /**
     * Get destination region values
     * 
     * @return array
     */
    protected function getDestRegionValues()
    {
        $regions = array(array('value' => '', 'label' => '*'));
        $model = $this->getModel();
        $destCountryId = $model->getDestCountryId();
        if ($destCountryId) {
            $regionCollection = Mage::getModel('directory/region')->getCollection()
                ->addCountryFilter($destCountryId);
            $regions = $regionCollection->toOptionArray();
            if (isset($regions[0])) $regions[0]['label'] = '*';
        }
        return $regions;
    }
    /**
     * Get condition name values
     * 
     * @return array
     */
    protected function getConditionNameValues()
    {
        return Mage::getModel('adminhtml/system_config_source_shipping_tablerate')->toOptionArray();
    }
    /**
     * Get destination zip value
     * 
     * @return string
     */
    protected function getDestZipValue()
    {
        $model = $this->getModel();
        $destZip = $model->getDestZip();
        return (($destZip == '*') || ($destZip == '')) ? '*' : $destZip;
    }
    /**
     * Prepare form before rendering HTML
     *
     * @return Verite_Japanpost_Block_Adminhtml_Tablerate_Edit_Form
     */
    protected function _prepareForm()
    {
        $helper = $this->_getHelper();
        $model = $this->getModel();
        if ($this->isAllowedAction('save')) $isElementDisabled = false; else $isElementDisabled = true;
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));
        $form->setHtmlIdPrefix('japanpost_tablerate_');
        $fieldset = $form->addFieldset('main_fieldset', array('legend' => $helper->__('Rate Information')));
        if ($model->getId()) {
            $fieldset->addField('pk', 'hidden', array(
                'name'      => 'pk', 
                'value'     => $model->getId(), 
            ));
        }
        $fieldset->addField('website_id', 'hidden', array(
            'name'       => 'website_id', 
            'value'      => $model->getWebsiteId(), 
        ));
        $fieldset->addField('dest_country_id', 'select', array(
            'name'       => 'dest_country_id', 
            'label'      => $helper->__('Dest Country'), 
            'title'      => $helper->__('Dest Country'), 
            'required'   => false, 
            'value'		 => $model->getDestCountryId(), 
            'values'     => $this->getDestCountryValues(), 
            'disabled'   => $isElementDisabled, 
        ));
        $fieldset->addField('dest_region_id', 'select', array(
            'name'       => 'dest_region_id', 
            'label'      => $helper->__('Dest Region/State'), 
            'title'      => $helper->__('Dest Region/State'), 
            'required'   => false, 
            'value'		 => $model->getDestRegionId(), 
            'values'     => $this->getDestRegionValues(), 
            'disabled'   => $isElementDisabled, 
        ));
        $fieldset->addField('dest_zip', 'text', array(
            'name'       => 'dest_zip', 
            'label'      => $helper->__('Dest Zip/Postal Code'), 
            'title'      => $helper->__('Dest Zip/Postal Code'), 
            'note'       => $helper->__('\'*\' or blank - matches any'), 
            'required'   => false, 
            'value'		 => $this->getDestZipValue(), 
            'disabled'   => $isElementDisabled, 
        ));
        $fieldset->addField('condition_name', 'select', array(
            'name'       => 'condition_name', 
            'label'      => $helper->__('Condition Name'), 
            'title'      => $helper->__('Condition Name'), 
            'required'   => true, 
            'value'		 => $model->getConditionName(), 
            'values'     => $this->getConditionNameValues(), 
            'disabled'   => $isElementDisabled, 
        ));
        $fieldset->addField('condition_value', 'text', array(
            'name'       => 'condition_value', 
            'label'      => $helper->__('Condition Value'), 
            'title'      => $helper->__('Condition Value'), 
            'required'   => true, 
            'value'		 => floatval($model->getConditionValue()), 
            'disabled'   => $isElementDisabled, 
        ));
        $fieldset->addField('price', 'text', array(
            'name'       => 'price', 
            'label'      => $helper->__('Price'), 
            'title'      => $helper->__('Price'), 
            'required'   => true, 
            'value'		 => floatval($model->getPrice()), 
            'disabled'   => $isElementDisabled, 
        ));
        $fieldset->addField('cost', 'text', array(
            'name'       => 'cost', 
            'label'      => $helper->__('Cost'), 
            'title'      => $helper->__('Cost'), 
            'required'   => true, 
            'value'		 => floatval($model->getCost()), 
            'disabled'   => $isElementDisabled, 
        ));
        Mage::dispatchEvent('japanpost_adminhtml_tablerate_edit_prepare_form', array('form' => $form));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}