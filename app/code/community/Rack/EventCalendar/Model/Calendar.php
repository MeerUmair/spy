<?php
class Rack_EventCalendar_Model_Calendar extends Mage_Core_Model_Abstract {
    public function _construct(){
        parent::_construct();
        $this->_init('calendar/calendar');
    }

    protected function _formatDate($date) {
        $array = explode("-", $date);

        return Mage::helper("calendar")->__("%s/%s", (int)$array[1], $array[2]);
    }
    
    public function getHolidays($year, $month) {
        $condition1 = array(
                            "gteq" => date(sprintf('Y-%02d-01', $month), mktime(0,0,0,$month,1,$year)),
                           );
        $condition2 = array(
                            "lteq" => date(sprintf('Y-%02d-t', $month),mktime(0,0,0,$month,1,$year))
                           );

        $collection = $this->getCollection()
                            ->addFieldToFilter('main_table.status', 1)
                            ->addFieldToFilter('main_table.is_holiday', 1)
                            ->addFieldToFilter('main_table.day', $condition1)
                            ->addFieldToFilter('main_table.day', $condition2)
                            ->setOrder('main_table.day', 'asc')
                            ;
        $days = array();
        
        foreach ($collection as $item) {
            $days[$item->getDay()] = $item->getDay();
        }
        
        return $days;
    }
}
