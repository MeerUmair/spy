<?php

class Rack_EventCalendar_Block_Widget extends Mage_Core_Block_Template
{
    protected $_holidays = null;
    
    public function _construct()
    {
        parent::_construct();
        set_include_path(get_include_path() . PS . BP . DS . 'app/code/local/PEAR');
        if (!defined('CALENDAR_FIRST_DAY_OF_WEEK')) {
            define('CALENDAR_FIRST_DAY_OF_WEEK', 1);
        }
        if ($this->getTemplate() == '') {
            $this->setTemplate('rack_calendar/widget.phtml');
        }
    }

    public function getMonthCount()
    {
        $numberOfMonths = 2;
        if ($this->getNumberOfMonths()) {
            $numberOfMonths = $this->getNumberOfMonths();
        }

        return $numberOfMonths;
    }

    public function getStartYear()
    {
        if ($this->getYear()) {
            return (int) $this->getYear();
        }

        return (int) date('Y');
    }

    public function getStartMonth()
    {
        if ($this->getMonth()) {
            return (int) $this->getMonth();
        }

        return (int) date('n');
    }

    public function buildDays()
    {
        require_once 'Calendar/Month/Weekdays.php';
        
        $numberOfMonth = $this->getMonthCount();
        $startMonth = $this->getStartMonth();
        $startYear = $this->getStartYear();

        $days = array();
        for ($i = 0; $i < $numberOfMonth; $i++) {
            $startMonth += $i;
            $calMonth = new Calendar_Month_Weekdays($startYear, $startMonth, null);
            $calMonth->build();
            $days[$calMonth->thisMonth()] = $calMonth;
            if ($startMonth == 12) {
                $startYear += 1;
                $startMonth = -$i;
            }
        }
        
        return $days;
    }

    public function getHolidays()
    {
        if ($this->_holidays === null) {
            $calendar = Mage::getModel('calendar/calendar');

            $numberOfMonth = $this->getMonthCount();
            $startMonth = $this->getStartMonth();
            $startYear = $this->getStartYear();

            $this->_holidays = array();
            for ($i = 0; $i < $numberOfMonth; $i++) {
                $startMonth += $i;
                $this->_holidays = array_merge($this->_holidays, $calendar->getHolidays($startYear, $startMonth));
                if ($startMonth == 12) {
                    $startYear += 1;
                    $startMonth = -$i;
                }
            }
        }

        return $this->_holidays;
    }
    
    public function getDayNames()
    {
        if (CALENDAR_FIRST_DAY_OF_WEEK == 1) {
            $names = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
        } else {
            $names = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
        }
        
        foreach ($names as $key => $n) {
            $names[$key] = $this->__($n);
        }
        
        return $names;
    }
    
    public function getHolidayCssClass($d, $prepared = false)
    {
        $holidays = $this->getHolidays();
        if ($prepared == false) {
            $key = sprintf('%4d-%02d-%02d', $d->thisYear(), $d->thisMonth(), $d->thisDay());
        } else {
            $key = $d;
        }
        
        if (!array_key_exists($key, $holidays)) {
            return 'day-work';
        } else {           
            return 'day-holiday';
        }
    }
    
    public function getHolidaysWithDetail()
    {
        $date = DateTime::createFromFormat('Y-m-d', sprintf('%s-%s-01', $this->getStartYear(), $this->getStartMonth()));
        $numberOfMonth = $this->getMonthCount();
        $date->add(new DateInterval('P' . ($numberOfMonth - 1). 'M'));
        $condition1 = array(
                        "gteq" => date(sprintf('%s-%02d-01', $this->getStartYear(), $this->getStartMonth())),
                        );
        $condition2 = array(
                            "lteq" => $date->format('Y-m-t')
                        );

        $collection = Mage::getModel('calendar/calendar')->getCollection()
                            ->addFieldToSelect('*')
                            ->addFieldToFilter('main_table.status', 1)
                            ->addFieldToFilter('main_table.is_holiday', 1)
                            ->addFieldToFilter('main_table.day', $condition1)
                            ->addFieldToFilter('main_table.day', $condition2)
                            ->setOrder('main_table.day', 'asc')
                            ;
        $collection->load();
        
        return $collection;
    }

}