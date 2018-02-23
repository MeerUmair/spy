<?php
error_reporting(E_ALL &~E_NOTICE);
class Rack_EventCalendar_Model_Observer {

    public function __construct() {
        set_include_path(get_include_path() . PS . BP . DS . 'app/code/local/PEAR');
        require_once("Calendar/Month.php");
        require_once("Date/Holidays.php");
    }

    public function process() {
        $this->_generateCalendar();
        $this->_removeCalendar();
    }
    
    public function install() {
        $this->_generateCalendar();
        $this->_generateCalendar(1);
    }
    
    public function deleteAll() {
        $model = Mage::getModel('calendar/calendar');
        $collection = $model->getCollection();
        foreach ($collection as $item) {
            $item->delete();
        }
    }
    
    protected function _generateCalendar($mode = 2) {
        if($mode == 1 ) {
            $_currentYear = date("Y") + 1;
        } else {
            $_currentYear = date("Y");
        }
        $_Month       = 12;

        for ($i = 1; $i <= 12; $i++) {
            $_calender = new Calendar_Month($_currentYear, $i);
            $_calender->build();

            $filename = BP . DS . 'app/code/local/PEAR/Date_Holidays_Japan/lang/Japan/ja_JP.xml';
            $dh = new Date_Holidays();
            $dh = $dh->factory('Japan', $_currentYear, 'ja_JP');
            $dh->addTranslationFile($filename, 'ja_JP');
            $holidays = array();

            foreach ($dh->getHolidays() as $h) {
                $holidays[$h->getDate()->format('%Y-%m-%d')] = $h->getTitle();
            }
            ksort($holidays);

            $default_holiday = explode(",", Mage::getStoreConfig("calendar/calendar/holiday"));

            while ($day = $_calender->fetch()) {
                $data  = array();
                $model = Mage::getModel('calendar/calendar');
                $_day  = sprintf("%d-%02d-%02d", $_currentYear, $i, $day->thisDay());
                $_date = date("w", mktime(0, 0, 0, $i, $day->thisDay(), $_currentYear));

                $data["day"]    = $_day;
                $data["status"] = 1;
                
                if (array_key_exists($_day, $holidays)) {
                    if(Mage::getStoreConfig('calendar/calendar/as_holiday')) {
                        $data["is_holiday"] = 1;
                    } else {
                        $data["is_holiday"] = 2;
                        if(in_array($_date, $default_holiday)) {
                            $data["is_holiday"] = 1;
                        }
                    }
                    $data["day_comment"] = $holidays[$_day];
                } elseif(in_array($_date, $default_holiday)) {
                    $data["is_holiday"] = 1;
                } else {
                    $data["is_holiday"] = 2;
                }
                $data["store_id"] = 1;
                $model->setData($data);
                $model->save();

            }
        }
    }

    protected function _removeCalendar() {
        $model = Mage::getModel('calendar/calendar');
        $currentDate = new Zend_Date();
        $year = date("Y") - 1;
        $condition1 = array("gteq" => $currentDate->toString($year . "-01-01"));
        $condition2 = array("lteq" => $currentDate->toString($year . "-12-31"));

        $collection = $model->getCollection()
                           ->addFieldToFilter('main_table.day', $condition1)
                           ->addFieldToFilter('main_table.day', $condition2);
        foreach ($collection as $item) {
            $item->delete();
        }
    }
}
