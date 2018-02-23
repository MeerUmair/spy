<?php

class Verite_Japanpost_Model_Export_Buffer extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('japanpost/export_buffer');
    }

    /**
     * Write export row to buffer and update exported flag of order.
     * This process is done in transactional.
     *
     * @param $orderId Order ID
     * @param $csvData
     */
    public function writeRow($csvData, $orderId = null)
    {
        $this->getResource()->writeRow($csvData, $orderId);
    }

    /**
     * Write export buffer to file
     *
     * @param $filePath
     * @throws Exception
     */
    public function writeToFile($filePath)
    {
        $list = $this->getCollection()->setOrder('id', 'asc');
        $fh = fopen($filePath, 'w');
        if (!$fh) {
            throw new Exception('Cannot open write data to file: ' . $filePath);
        }

        foreach ($list as $item) {
            fputs($fh, $item->getData('data') . "\r\n");
        }
        fclose($fh);
    }

    public function getDataAsString()
    {
        $result = '';
        $list = $this->getCollection()->setOrder('id', 'asc');
        $targetEncoding = Mage::helper('japanpost')->getExportEncoding();
        foreach ($list as $item) {
            $data = $item->getData('data');
            $data = mb_convert_encoding($data, $targetEncoding, 'UTF-8');
            $result .= $data . "\r\n";
        }

        return $result;
    }
}