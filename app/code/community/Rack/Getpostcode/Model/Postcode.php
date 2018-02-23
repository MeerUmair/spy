<?php

class Rack_Getpostcode_Model_Postcode extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('getpostcode/postcode', 'id');
    }

    public function import($filename)
    {
        if (!file_exists($filename)) {
            Mage::throwException(Mage::helper('getpostcode')->__('%s is not exists.', $filename));
        }
        ini_set('memory_limit', '2G');
        $fp = fopen($filename, 'r');
        $_model = Mage::getModel('getpostcode/postcode');
        
        $collection = $_model->getCollection();
        foreach ($collection as $item) {
            $item->delete();
        }

        while($csv_data = fgetcsv($fp, 0, ',','"')) {
            $_data = array();
            $_data['post_code'] = $csv_data[2];
            $_data['prefecture_name_kana'] = $csv_data[3];
            $_data['city_ward_kana'] = $csv_data[4];
            $_data['area_kana'] = $csv_data[5];
            $_data['prefecture_name'] = $csv_data[6];
            $_data['city_ward'] = $csv_data[7];
            $_data['area'] = $csv_data[8];
            $model = Mage::getModel('getpostcode/postcode');
            
            $model->setData($_data)->save();
            
            if (method_exists($model, 'clearInstance')) {
                $model->clearInstance();
            }
            $model = null;
            unset($model);
            unset($_data);
            unset($csv_data);
         }
    }
}