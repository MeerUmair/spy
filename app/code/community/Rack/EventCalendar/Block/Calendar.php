<?php 
class Rack_EventCalendar_Block_Calendar extends Mage_Core_Block_Template {

    protected $_mode;

    public function getCurrentMonthCalendar() {
        return $this->_getCalendar(date('Y-m'));
    }
    
    public function getNextMonthCalendar() {
        return $this->_getCalendar(date('Y-m', mktime(0,0,0, date('m')+1, date('d'), date('Y'))));    
    }

    public function setMode($mode) {
        if ($mode == "current") {
            $this->_mode = "Current";
        } else {
            $this->_mode = "Next";
        }
    }

    protected function _toHtml() {
        if ( $this->_mode == "Current") {
            return $this->getCurrentMonthCalendar();
        } else {
            return $this->getNextMonthCalendar();
        }
    }

    protected function _getCalendar($currentmonth) {
        set_include_path(get_include_path() . PS . BP . DS . 'app/code/local/PEAR');
        require_once 'Calendar/Month/Weekdays.php';

        list($year, $month) = explode("-", $currentmonth);

        $calMonth = new Calendar_Month_Weekdays($year, $month, null);
        $calMonth->build();

        $calendar = Mage::getModel("calendar/calendar");
        $arr_calendar = $calendar->getHolidays($year, $month);
        $html = '<div class="calendar">';
        $html .= '<span>' . $year . $this->helper('calendar')->__('year') . $month . $this->helper('calendar')->__('month\'s calendar')  . '</span>';
        $html .= '<table border="1" cellspacing="3" cellpadding="2"><thead><tr>';
        $html .= '<th>' . $this->helper('calendar')->__('Sun') . '</th><th>' . $this->helper('calendar')->__('Mon') . 
                 '</th><th>' . $this->helper('calendar')->__('Tue') . '</th><th>' . $this->helper('calendar')->__('Wed') . 
                 '</th><th>' . $this->helper('calendar')->__('Thu') . '</th><th>' . $this->helper('calendar')->__('Fri') . 
                 '</th><th>' . $this->helper('calendar')->__('Sat') .  '</th>';
        $html .= '</tr></thead><tbody>';

        while ($day = $calMonth->fetch()) {
            if ($day->isFirst()) {
                $html .= '<tr>';
            }
            if ($day->isEmpty()) {
                $html .= '<td>&nbsp;</td>';
            } else {
                if(in_array($year."-".$month."-".sprintf("%02d", $day->thisDay()), $arr_calendar)) {
                    $html .= '<td class="calendar"><font color="red">'.$day->thisDay().'</font></td>';
                } else {
                    $html .= '<td>'.$day->thisDay().'</td>';
                }
            }
            if ($day->isLast()) {
                $html .= '</tr>';
            }
        }
        $html .= '</tbody></table>';
        $html .= '</div>';

        return $html;
    }

}
