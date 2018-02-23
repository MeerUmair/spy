<?php

class Verite_Japanpost_Model_Csv_CsvReader Implements Iterator
{
    //Maximumn row length size
    const MAX_SIZE = 9999;
    
    /**
     * File resource handle
     *
     * @var resource
     */
    protected $_handle = null;
    
    /**
     * Current row's data
     *
     * @var array
     */
    protected $_current = null;
    
    /**
     * Current row number
     *
     * @var int
     */
    protected $_row = 0;
    
    /**
     * Delimiter of CSV field.
     *
     * @var string
     */
    protected $_delimiter = ',';
    
    /**
     * Header columns.
     *
     * @var array
     */
    protected $_header = array();
    
    protected $_hasHeader = true;
    
    /**
     * Contructor
     *
     * @param string $file csv file path.
     * @param string $delimiter Delimiter of CSV field
     * @return void
     * @throw Fo_Data_Exception If file open not success.
     */
    public function __construct($file, $delimiter = ',', $hasHeader = true)
    {
        try {
            $this->_handle = fopen($file, 'r');
            $this->_delimiter = $delimiter;
            $this->_hasHeader = $hasHeader;
            if ($hasHeader == true && $this->next()) {
                $this->_header = $this->_fgetcsv($this->_handle, self::MAX_SIZE, $this->_delimiter);
                $this->rewind();
            }
        }  catch (Exception $e) {
            Mage::throwException('Can not open data file.' . $file);
        }
    }
    
    /**
     * @see Iterator::next()
     */
    public function next()
    {
        return !feof($this->_handle);
    }
    
    /**
     * @see Iterator::current()
     */
    public function current()
    {
        //$data = fgetcsv($this->_handle, self::MAX_SIZE, $this->_delimiter);
        $data = $this->_fgetcsv($this->_handle, self::MAX_SIZE, $this->_delimiter);
        if (is_array($data)) {
            while (list($index, $value) = each($data)) {
                if ($this->hasHeader()) {
                    $this->_current[$this->_header[$index]] = $value;
                } else {
                    $this->_current['col_' . $index] = $data;
                }
            }
            
            $this->_row++;
            
            return $this->_current;
        }
        return array();
    }
    
    /**
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->_row = 0;
        rewind($this->_handle);
    }
    
    /**
     * @see Iterator::valid()
     */
    public function valid()
    {
        if (feof($this->_handle)) {
            fclose($this->_handle);
            return false;
        }
        
        return true;
    }
    
    /**
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->_row;
    }
    
    /**
     * Pass to next line and do nothing
     * @return void
     */
    public function pass($e = '"')
    {
        $d = preg_quote($this->_delimiter);
        $e = preg_quote($e);
        $_line = "";
        $eof = false;
        while ($eof != true) {
            $_line .= (empty($length) ? fgets($this->_handle) : fgets($this->_handle, $length));
            $itemcnt = preg_match_all('/'.$e.'/', $_line, $dummy);
            if ($itemcnt % 2 == 0) $eof = true;
        }
    }
    
    /**
     * Check if csv has header
     * @param boolean $value set value
     * @return boolean
     */
    public function hasHeader($value = NULL)
    {
        if ($value != NULL) {
            $this->_hasHeader = $value;
        }
        
        return $this->_hasHeader;
    }
    
    /**
     * Destructor <br />
     * Close file resource.
     *
     * @return void
     */
    public function __destruct()
    {
        try {
            fclose($this->_handle);
        } catch (Exception $e) {
            //don't throw anything because this is destructor
        }
    }
    
    /**
     * PHP fgetcsv replacement.
     *
     * @param $handle
     * @param $length
     * @param $d
     * @param $e
     * @return array
     */
    protected function _fgetcsv(&$handle, $length = null, $d = ',', $e = '"') {
        $d = preg_quote($d);
        $e = preg_quote($e);
        $_line = "";
        $eof = false;
        while ($eof != true) {
            $_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
            $itemcnt = preg_match_all('/'.$e.'/', $_line, $dummy);
            if ($itemcnt % 2 == 0) $eof = true;
        }
        $_csv_line = preg_replace('/(?:\\r\\n|[\\r\\n])?$/', $d, trim($_line));
        $_csv_pattern = '/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';
        preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
        $_csv_data = $_csv_matches[1];
        for($_csv_i=0;$_csv_i<count($_csv_data);$_csv_i++){
            $_csv_data[$_csv_i]=preg_replace('/^'.$e.'(.*)'.$e.'$/s','$1',$_csv_data[$_csv_i]);
            $_csv_data[$_csv_i]=str_replace($e.$e, $e, trim($_csv_data[$_csv_i]));
        }
        return empty($_line) ? false : $_csv_data;
    }
    
    /**
     * Same as PHP 5.3+ str_getcsv
     * @param $input
     * @param $delimiter
     * @param $enclosure
     * @param $escape
     * @return array
     */
    protected function str_getcsv($input, $delimiter = ",", $enclosure = '"', $escape = "\\") {
        $fiveMBs = self::MAX_SIZE;
        $fp = fopen("php://temp/maxmemory:$fiveMBs", 'r+');
        fputs($fp, $input);
        rewind($fp);

        $data = fgetcsv($fp, 1000, $delimiter, $enclosure); //  $escape only got added in 5.3.0

        fclose($fp);
        return $data;
    }
    
    public function &getHeader() {
        return $this->_header;
    }
}