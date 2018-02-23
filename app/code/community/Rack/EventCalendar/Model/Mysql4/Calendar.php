<?php

class Rack_EventCalendar_Model_Mysql4_Calendar extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct(){    
        $this->_init('calendar/calendar', 'day_id');
    }

    /**
     * Check if date is holiday or not
     *
     * @param $date in YYYY-MM-DD format
     * @return bool
     */
    public function isHoliday($date)
    {
        if (empty($date)) {
            return false;
        }

        $select = $this->_getReadAdapter()->select();
        $select->from($this->getTable('calendar/calendar'), array('day'))
               ->where('day = ?', $date)
               ->where('is_holiday = 1 and status = 1');
        $result = $this->_getReadAdapter()->fetchOne($select);
        if ($result && count($result)) {
            return true;
        }

        return false;
    }

    public function getPreviousWorkingDay($date)
    {
        if (empty($date)) {
            return false;
        }

        $select = $this->_getReadAdapter()->select();
        $select->from($this->getTable('calendar/calendar'), array('day'))
            ->where('day < ?', $date)
            ->where('is_holiday != 1')
            ->order('day DESC')
            ->limit(1);
        $result = $this->_getReadAdapter()->fetchOne($select);
        if ($result) {
            return $result;
        }

        return false;
    }
}
