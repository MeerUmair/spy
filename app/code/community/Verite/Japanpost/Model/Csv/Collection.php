<?php

class Verite_Japanpost_Model_Csv_Collection extends Varien_Data_Collection
{
    /**
     * Csv Reader object
     * @var Verite_Japanpost_Model_Csv_MbCsvReader
     */
    protected $_csvReader = null;
    
    /**
     * All collection data array
     * Used for getData method
     *
     * @var array
     */
    protected $_data = null;
    
    /**
     * Current page offset
     * @var int
     */
    protected $_offset = 0;
    
    protected $_res = array();
    
    protected $_validator = null;

    /**
     * Constructor
     *
     * @param $fileName
     * @param $delimiter
     * @param $encoding
     * @param $multiByte
     * @param $hasHeader
     * @return void
     */
    public function __construct($fileName, $delimiter, $encoding, $multiByte = true, $hasHeader = false)
    {
        if ($multiByte == true) {
            $this->_csvReader = new Verite_Japanpost_Model_Csv_MbCsvReader($fileName, $delimiter, $encoding, $hasHeader);
        } else {
            $this->_csvReader = new Verite_Japanpost_Model_Csv_CsvReader($fileName, $delimiter, $hasHeader);
        }
    }
    
    /**
     * Render limit
     *
     * @return  Verite_Japanpost_Model_Csv_Collection
     */
    protected function _renderLimit()
    {
        if($this->_pageSize) {
            $this->_offset = ($this->getCurPage() * $this->_pageSize) - $this->_pageSize;
        }
        
        return $this;
    }
    
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        $this->_renderLimit();
        $data = $this->getData();
        $this->resetData();
        $seqnr = null;
        $errorCount = 0;

        if (is_array($data)) {
            if ($this->_validator != null) {
                $validator = Mage::getModel($this->_validator);
            } else {
                $validator = false;
            }
            $errorMsg = array();
            foreach ($data as $row) {
                $item = $this->getNewEmptyItem();
                $item->addData($row);
                if ($validator) {
                    $res = $validator->validate($this->_csvReader->getHeader(), $row);
                    if(count($res->getData()) == 0) {
                        $this->addItem($item);
                    } else {
                        $this->addItem($res);
                        $errorMsg[] = $res;
                        $errorCount++;
                    }
                } else {
                    $this->addItem($item);
                }
            }
        }
        
        $this->_setIsLoaded();
        return $this;
    }
    
    public function loadData($printQuery = false, $logQuery = false)
    {
        return $this->load($printQuery, $logQuery);
    }
    
    /**
     * Get all data array for collection
     *
     * @return array
     */
    public function getData()
    {
        if ($this->_data === null) {
            $this->_renderLimit();
            $this->_data = $this->_fetchData();
        }
        
        return $this->_data;
    }
    
    protected function _fetchData()
    {
        $this->_csvReader->rewind();
        if ($this->_csvReader->hasHeader()) {
            $this->_csvReader->pass(); // pass header
        }
        $currentRow = 0;
        while ($this->_csvReader->next()) {
            if ($currentRow >= $this->_offset && $currentRow < ($this->_offset + $this->_pageSize)) {
                $this->_data[] = $this->_csvReader->current();
            } else {
                $this->_csvReader->pass();
            }
            $currentRow++;
        }
       
        $this->_totalRecords = $currentRow;
        
        return $this->_data;
    }
    
    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $this->_csvReader->pass(); // pass header
            while ($this->_csvReader->next()) {
                $this->_csvReader->pass();
                $this->_totalRecords++;
            }
        }
        return $this->_totalRecords;
    }
    
    /**
     * Reset loaded for collection data array
     *
     * @return Verite_Japanpost_Model_Csv_Collection
     */
    public function resetData()
    {
        $this->_data = null;
        return $this;
    }
    
    public function getError()
    {
        return $this->_res;
    }
    
}