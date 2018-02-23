<?php
class Rack_EventCalendar_Block_Adminhtml_Calendar_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'calendar';
        $this->_controller = 'adminhtml_calendar';
        
        $this->_updateButton('save', 'label', Mage::helper('calendar')->__('Save Date'));
        $this->_updateButton('delete', 'label', Mage::helper('calendar')->__('Delete Date'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

    }

    public function getHeaderText()
    {
        if( Mage::registry('calendar_data') && Mage::registry('calendar_data')->getId() ) {
            return Mage::helper('calendar')->__("Edit Date '%s'", $this->htmlEscape(Mage::registry('calendar_data')->getDay()));
        } else {
            return Mage::helper('calendar')->__('Add Date');
        }
    }
}
