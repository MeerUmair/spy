<?php

class Verite_Japanpost_Model_Log extends Mage_Core_Model_Abstract
{
    const LOG_TYPE_IMPORT = 'import';
    const LOG_TYPE_EXPORT = 'export';

    protected function _construct()
    {
        $this->_init('japanpost/log');
    }

    /**
     * Helper method to write log data
     *
     * @param String $message
     * @param String $type
     * @param int $level
     */
    public static function writeLog($message, $type, $level = Zend_Log::INFO)
    {
        $date = new Zend_Date();
        $log = new self();
        $log->setData(array(
            'type'      => $type,
            'level'     => $level,
            'message'   => $message,
            'date'      => $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
        ));

        $log->save();
    }
}