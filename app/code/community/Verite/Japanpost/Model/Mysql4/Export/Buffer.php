<?php

class Verite_Japanpost_Model_Mysql4_Export_Buffer extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('japanpost/export_buffer', 'id');
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
        $writer = $this->_getWriteAdapter();
        $writer->beginTransaction();
        try {
            $query = 'INSERT INTO `' . $this->getMainTable() . '` (data) VALUES (?)';
            $writer->query($query, $csvData);

            if ($orderId != null) {
                $query = 'UPDATE `' . $this->getTable('sales/order') . '` SET is_japanpost_exported = 1 WHERE entity_id = ?';
                $writer->query($query, $orderId);
            }

            $writer->commit();
        } catch (Exception $e) {
            //@TODO: write log here
            $writer->rollBack();
        }
    }

    /**
     * Truncate buffer
     */
    public function truncate()
    {
        $query = 'TRUNCATE `' . $this->getMainTable() . '`';
        $this->_getWriteAdapter()->query($query);
    }
}