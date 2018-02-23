<?php

class Verite_Japanpost_Model_Csv_MbCsvReader extends Verite_Japanpost_Model_Csv_CsvReader
{
    /**
     * CSV Encoding
     * @var string
     */
    protected $_encoding = 'SJIS';
    
    /**
     * Constructor
     *
     * @param $file CSV full file path
     * @param $delimiter CSV delimiter
     * @param $encode CSV encoding
     * @return void
     */
    public function __construct($file, $delimiter = ',', $encode = 'SJIS', $hasHeader = true)
    {
        mb_internal_encoding('UTF-8');
        ini_set('mbstring.language', 'japanese');
        mb_detect_order('EUC-JP,UTF-8,SJIS,JIS,ASCII');
        if ($encode != '') {
            $this->_encoding = $encode;
        }
        parent::__construct($file, $delimiter, $hasHeader);
        if ($hasHeader) {
            foreach ($this->_header as $key => $value) {
                $this->_header[$key] = mb_convert_encoding($value, 'UTF-8', $this->_encoding);
            }
        }
    }
    
    public function current()
    {
        $data = $this->_fgetcsv($this->_handle, self::MAX_SIZE, $this->_delimiter);
        if (is_array($data)) {
            $this->_current = array();
            while (list($index, $value) = each($data)) {
                if ($this->hasHeader()) {
                    $this->_current[$this->_header[$index]] = mb_convert_encoding($value, 'UTF-8', $this->_encoding);
                } else {
                    $this->_current['col_' . $index] = mb_convert_encoding($value, 'UTF-8', $this->_encoding);
                }
            }
            $this->_row++;            
            return $this->_current;
        }
        return array();
    }
}