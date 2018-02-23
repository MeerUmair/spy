<?php

class Rack_Jp_Core_Block_Adminhtml_Directory_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('jpcore_form', array('legend' => Mage::helper('jpcore')->__('Currency Information')));

        $fieldset->addField('enable', 'select', array(
            'label' => Mage::helper('jpcore')->__('Enable'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'enable',
            'options' => array(0 => $this->__('No'), 1 => $this->__('Yes'))
        ));
        
        $fieldset->addField('fee', 'text', array(
            'label' => Mage::helper('jpcore')->__('Fee'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'fee',
        ));
        
        $fieldset->addField('default_price', 'label', array(
                'bold'=>true,
                'value'=> '[' . Mage::app()->getStore()->getBaseCurrencyCode() . ']'
        ));
        
        $labels = array();
        if (Mage::getSingleton('adminhtml/session')->getGiftWrappingData()) {
            $data = Mage::getSingleton('adminhtml/session')->getGiftwrappingData();
            $form->setValues($data);
            $labels = isset($data['store_labels']) ? $data['store_labels'] : array();
            Mage::getSingleton('adminhtml/session')->setGiftwrappingData(null);
        } elseif (Mage::registry('jpcore_data')) {
            $data = Mage::registry('jpcore_data')->getData();
            $form->setValues($data);
            $labels = isset($data['store_labels']) ? $data['store_labels'] : array();
        }
        
        $fieldset->addField('store_default_label', 'text', array(
            'name'      => 'store_labels[0]',
            'required'  => true,
            'label'     => Mage::helper('salesrule')->__('Default Label for All Store Views'),
            'value'     => isset($labels[0]) ? $labels[0] : '',
        ));

        $fieldset = $form->addFieldset('store_labels_fieldset', array(
            'legend'       => Mage::helper('salesrule')->__('Store View Specific Labels'),
            'table_class'  => 'form-list stores-tree',
        ));
        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("w_{$website->getId()}_label", 'note', array(
                'label'    => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("sg_{$group->getId()}_label", 'note', array(
                    'label'    => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField("s_{$store->getId()}", 'text', array(
                        'name'      => 'store_labels['.$store->getId().']',
                        'required'  => false,
                        'label'     => $store->getName(),
                        'value'     => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                        'fieldset_html_class' => 'store',
                    ));
                }
            }
        }
        
        return parent::_prepareForm();
    }

}