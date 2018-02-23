<?php
class Rack_EventCalendar_Block_Adminhtml_Calendar_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('calendar_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('calendar')->__('Calendar Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('calendar')->__('Calendar Information'),
          'title'     => Mage::helper('calendar')->__('Calendar Information'),
          'content'   => $this->getLayout()->createBlock('calendar/adminhtml_calendar_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
