<?php
class Rack_EventCalendar_Block_Adminhtml_Calendar extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_calendar';
        $this->_blockGroup = 'calendar';
        $this->_headerText = Mage::helper('calendar')->__('Calendar Manager');
        $this->_addButtonLabel = Mage::helper('calendar')->__('Add Date');
        $this->_addButtonLabel = Mage::helper('calendar')->__('Init Calendar');
        parent::__construct();
        $this->setTemplate("rack_calendar/calendar.phtml");
    }

    protected function _prepareLayout()
    {
        $this->setChild('add_new_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => Mage::helper('calendar')->__('Add Date'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/new')."')",
                'class'   => 'add'
            ))
        );
        
        $this->setChild('init_calendar_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => Mage::helper('calendar')->__('Init Calendar'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/init')."')",
                'class'   => 'add'
            ))
        );
        
        if (!Mage::app()->isSingleStoreMode()) {
            $this->setChild('store_switcher',
                $this->getLayout()->createBlock('adminhtml/store_switcher')
                ->setUseConfirm(false)
                ->setSwitchUrl($this->getUrl('*/*/*', array('store'=>null)))
            );
        }
        $this->setChild('grid', $this->getLayout()->createBlock('calendar/adminhtml_calendar_grid', 'calendar.grid'));
        return parent::_prepareLayout();
    }

    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_new_button');
    }
    
    public function getInitButtonHtml()
    {
        return $this->getChildHtml('init_calendar_button');
    }

    public function getGridHtml()
    { 
        return $this->getChildHtml('grid');
    }

    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

}

