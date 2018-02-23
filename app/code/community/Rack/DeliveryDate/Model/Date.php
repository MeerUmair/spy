<?php
class Rack_DeliveryDate_Model_Date
{
    const HOLIDAY_CONFIG    = 'deliverydate/date/holidays';
    const SECONDS_OF_DAY    = 86400;
    
    /**
     * Get sorted holidays
     * 
     * @return array
     */
    public function getHolidays()
    {
       $cacheKey = $this->_getCacheKey();

       if (Mage::app()->getCache()->test($cacheKey)) {
           $holidays = Mage::app()->getCache()->load($cacheKey);
       } else {
           $configData = Mage::getStoreConfig(self::HOLIDAY_CONFIG);
           $holidays = explode("\n", $configData);

           //convert date string to timestamp
           foreach ($holidays as $k => $v) {
               $holidays[$k] = strtotime($v);
           }

           Mage::app()->saveCache(serialize($holidays), $cacheKey, array(Mage_Core_Model_Config::CACHE_TAG));
       }
       
       return $holidays;
    }
    
    /**
     * Count number of holidays from specific date
     * 
     * @param string $from date string in format yyyy-MM-dd
     * @param string $to date string in format yyyy-MM-dd
     * @return int
     */
    public function countHolidaysFrom($from, $to = null)
    {
        if (is_int($from)) {
            $fromDate = $from;
        } else {
            $fromDate = strtotime($from);
        }
        if ($to != null) {
            $toDate =strtotime($to);
        } else {
            $toDate = PHP_INT_MAX;
        }
        
        $holidays = $this->getHolidays();
        $count = 0;
        for ($i = 0, $len = count($holidays); $i < $len; $i++) {
            if ($holidays[$i] >= $from) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Get all delivery dates for create select options
     *
     * @return array
     */
    public function getAvailableDeliveryDates()
    {
        $start = Mage::app()->getLocale()->date()->getTimestamp();
        $minRequireDays = $this->_getMinRequiredDays();
        $maxRequireDays = (int)Mage::getStoreConfig('deliverydate/date/maxday');
        $isExcludeSatSun = Mage::getStoreConfig('deliverydate/date/exclude_satsun');
        $isExcludeHoliday = Mage::getStoreConfig('deliverydate/date/exclude_holiday');
        $start = $start + ($minRequireDays * self::SECONDS_OF_DAY);
        $format = Mage::getStoreConfig('deliverydate/date/display_format');

        if ($isExcludeHoliday) {
            $holidays = $this->getHolidays();
        }
        $count = 0;
        $dates = array();
        while ($count < $maxRequireDays) {
            $start += self::SECONDS_OF_DAY;
            if ($isExcludeSatSun) {
                $date = date('w', $start);
                if ($date == 0 || $date == 6) {
                    continue;
                }
            }
            $exDate = strtotime(date('Y-m-d', $start));
            if ($isExcludeHoliday && in_array($exDate, $holidays)) {
                continue;
            }
            $date = date($format, $start);
            $dates[] = $date;
            $count++;
        }

        return $dates;
    }

    /**
     * Get all delivery dates for create select options
     *
     * @return array
     */
    public function getDisableDeliveryDates()
    {
        $start = Mage::app()->getLocale()->date()->getTimestamp();
        $minRequireDays = $this->_getMinRequiredDays();
        $isExcludeHoliday = Mage::getStoreConfig('deliverydate/date/exclude_holiday');

        $dates = array((int)date('Ymd'));
        if ($isExcludeHoliday) {
            $holidays = $this->getHolidays();
            foreach ($holidays as $holiday) {
                $dates[] = (int)date('Ymd', $holiday);
            }
        }
        $count = 0;

        while ($count < $minRequireDays) {
            $start += self::SECONDS_OF_DAY;
            $date = date('Ymd', $start);
            $dates[] = (int)$date;
            $count++;
        }


        return $dates;
    }

    /**
     * Get all delivery dates for create select options
     *
     * @return array
     */
    public function getAvailableDeliveryDatesInt()
    {
        $start = Mage::app()->getLocale()->date()->getTimestamp();
        $minRequireDays = $this->_getMinRequiredDays();
        $maxRequireDays = (int)Mage::getStoreConfig('deliverydate/date/maxday');
        $isExcludeSatSun = Mage::getStoreConfig('deliverydate/date/exclude_satsun');
        $isExcludeHoliday = Mage::getStoreConfig('deliverydate/date/exclude_holiday');
        $start = $start + ($minRequireDays * self::SECONDS_OF_DAY);

        if ($isExcludeHoliday) {
            $holidays = $this->getHolidays();
        }
        $count = 0;
        $dates = array();
        while ($count < $maxRequireDays) {
            $start += self::SECONDS_OF_DAY;
            if ($isExcludeSatSun) {
                $date = date('w', $start);
                if ($date == 0 || $date == 6) {
                    continue;
                }
            }
            $exDate = strtotime(date('Y-m-d', $start));
            if ($isExcludeHoliday && in_array($exDate, $holidays)) {
                continue;
            }
            $dates[] = date('Ymd', $start) * 1;
            $count++;
        }

        return $dates;
    }
    
    /**
     * Get all time segment for select options
     * 
     * @return array
     */
    public function getAvailableTimeSegment()
    {
        $segment = Mage::getStoreConfig('deliverydate/time/timesegment');
        
        return explode("\n", $segment);
    }

    /**
     * Get cache key for holiday list
     * 
     * @return string
     */
    protected function _getCacheKey()
    {
        $cacheInfo = array(
            'rack_deliverydate_holidays',
            Mage::app()->getStore()->getId()
        );
        
        return implode('_', $cacheInfo);
    }
    
    protected function _getMinRequiredDays()
    {
        $helper = Mage::helper('deliverydate');
        $type = $helper->getMindayType();
        
        if ($type == Rack_DeliveryDate_Model_Source_Minday::TYPE_FIXED) {
            return (int)Mage::getStoreConfig('deliverydate/minday/minday_fix');
        } else {
            $key = 'deliverydate/minday/' . $this->_getAmPm();
            return (int)Mage::getStoreConfig($key);
        }
    }
    
    protected function _getAmPm()
    {
        $breakTime = Mage::getStoreConfig('deliverydate/minday/break_time');
        if ($breakTime == '') {
            $breakTime = '12:00:00';
        }
        $now = Mage::app()->getLocale()->date();
        try {
            $breakTimeDate = clone $now;
            $breakTimeDate->set($breakTime, Zend_Date::TIME_SHORT);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $ampm = 'am';
        if ($now->isLater($breakTimeDate)) {
            $ampm = 'pm';
        }
        $dow = $now->get('e');
        $dayMap = array(
            1  => 'mon',
            2  => 'tue',
            3  => 'wed',
            4  => 'thu',
            5  => 'fri',
            6  => 'sat',
            0  => 'sun'
        );
        
        return $dayMap[$dow] . '_' . $ampm;
    }
}