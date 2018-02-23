<?php

class Rack_Getpostcode_Block_Adminhtml_Getpostcode_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('getpostcode_form', array('legend' => Mage::helper('getpostcode')->__('Postcode Information')));

        $fieldset->addField('post_code', 'text', array(
            'label' => Mage::helper('getpostcode')->__('Postcode'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'post_code',
        ));

        $fieldset->addField('prefecture_name', 'text', array(
            'label' => Mage::helper('getpostcode')->__('Prefecture Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'prefecture_name',
        ));

        $fieldset->addField('prefecture_name_kana', 'text', array(
            'label' => Mage::helper('getpostcode')->__('Prefecture Name Kana'),
            'required' => false,
            'name' => 'prefecture_name_kana',
        ));

        $fieldset->addField('city_ward', 'text', array(
            'label' => Mage::helper('getpostcode')->__('City Ward'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'city_ward',
        ));

        $fieldset->addField('city_ward_kana', 'text', array(
            'label' => Mage::helper('getpostcode')->__('City Ward Kana'),
            'required' => false,
            'name' => 'city_ward_kana',
        ));

        $fieldset->addField('area', 'text', array(
            'label' => Mage::helper('getpostcode')->__('Area'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'area',
        ));

        $fieldset->addField('area_kana', 'text', array(
            'label' => Mage::helper('getpostcode')->__('Area Kana'),
            'required' => false,
            'name' => 'area_kana',
        ));

        if (Mage::getSingleton('adminhtml/session')->getGetpostcodeData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getGetpostcodeData());
            Mage::getSingleton('adminhtml/session')->setGetpostcodeData(null);
        } elseif (Mage::registry('getpostcode_data')) {
            $form->setValues(Mage::registry('getpostcode_data')->getData());
        }
        return parent::_prepareForm();
    }

}