<?php

class Rack_Getpostcode_Block_Adminhtml_Getpostcode_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'getpostcode';
        $this->_controller = 'adminhtml_getpostcode';
        
        $this->_updateButton('save', 'label', Mage::helper('getpostcode')->__('Save Item'));
        $this->_removeButton('delete', 'label', Mage::helper('getpostcode')->__('Delete Item'));
        
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('getpostcode_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'getpostcode_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'getpostcode_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('getpostcode_data') && Mage::registry('getpostcode_data')->getId() ) {
            return Mage::helper('getpostcode')->__("Update postcode: %s", $this->htmlEscape(Mage::registry('getpostcode_data')->getPost_code()));
        } else {
            return Mage::helper('getpostcode')->__('Add postcode');
        }
    }
}