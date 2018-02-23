<?php
class Rack_EventCalendar_Block_Adminhtml_Calendar_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('calendar_form', array('legend'=>Mage::helper('calendar')->__('Event information')));
		
        $fieldset->addField('day', 'date', array(
                    'name'      => 'day',
                    'label'     => Mage::helper('calendar')->__('Date'),
                    'title'     => Mage::helper('calendar')->__('Date'),
                    'image'     => $this->getSkinUrl('images/grid-cal.gif'),
                    'format'    => Varien_Date::DATE_INTERNAL_FORMAT,
                    'required'  => true,
                    ));

        $fieldset->addField('day_comment', 'text', array(
                    'label'     => Mage::helper('calendar')->__('Day Comment'),
                    'class'     => 'required-entry',
                    'required'  => true,
                    'name'      => 'day_comment',
                    ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect', array(
                        'name'      => 'stores[]',
                        'label'     => Mage::helper('cms')->__('Store View'),
                        'title'     => Mage::helper('cms')->__('Store View'),
                        'required'  => true,
                        'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
                        ));
        }

        $fieldset->addField('status', 'select', array(
                    'label'     => Mage::helper('calendar')->__('Status'),
                    'name'      => 'status',
                    'values'    => array(
                        array(
                            'value'     => 1,
                            'label'     => Mage::helper('calendar')->__('Enabled'),
                            ),
                        array(
                            'value'     => 2,
                            'label'     => Mage::helper('calendar')->__('Disabled'),
                            ),
                        ),
                    ));

        $fieldset->addField('is_holiday', 'select', array(
                    'label'     => Mage::helper('calendar')->__('Is Holiday'),
                    'name'      => 'is_holiday',
                    'values'    => array(
                        array(
                            'value'     => 1,
                            'label'     => Mage::helper('calendar')->__('Yes'),
                            ),
                        array(
                            'value'     => 2,
                            'label'     => Mage::helper('calendar')->__('No'),
                            ),
                        ),
                    ));
        if ( Mage::getSingleton('adminhtml/session')->getCalendarData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCalendarData());
            Mage::getSingleton('adminhtml/session')->setCalendarData(null);
        } elseif ( Mage::registry('calendar_data') ) {
            $form->setValues(Mage::registry('calendar_data')->getData());
        }
        return parent::_prepareForm();
    }
}
