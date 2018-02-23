<?php
class Rack_EventCalendar_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isHoliday($date)
    {
        return Mage::getResourceModel('calendar/calendar')->isHoliday($date);
    }
}
