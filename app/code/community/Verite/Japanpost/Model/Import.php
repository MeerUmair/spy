<?php

class Verite_Japanpost_Model_Import
{
    const IMPORT_LOCK_FLAG = 'japanpost_shipping_import_lock_flag';
    const LOCK_LIFETIME = '1800'; //0.5 hour

    protected $_hasError = '';
    protected $_file = '';
    protected $_sendShippingEmail = false;
    protected $_sendInvoiceEmail  = false;

    /**
     * Create shippment and sent notify email to customer
     *
     * @param string $orderId
     * @param array $data
     * @return boolean|array
     */
    public function createShippemnt($orderId, $data)
    {
        /**
         * @var $order Mage_Sales_Model_Order
         */
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        if (!$order->getId()) {
            $this->_writeLog(Mage::helper('japanpost')->__('Order #%s not found.', $orderId), Zend_Log::ERR, $this->_file);
            return false;
        }

        if ($order->getState() == 'holded') {
            $this->_writeLog(Mage::helper('japanpost')->__('Order #%s is on holded.', $orderId), Zend_Log::NOTICE, $this->_file);
            return false;
        }

        if (!$order->canShip()) {
            $this->_writeLog(Mage::helper('japanpost')->__('Can not ship order #%s.', $orderId), Zend_Log::ERR, $this->_file);
            return false;
        }

        $transactionSave = Mage::getModel('core/resource_transaction');

        $trackData = array('number' => $data['tracking_number'], 'carrier_code' => 'japanpost', 'title' => $data['title']);
        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment();

        $track = Mage::getModel('sales/order_shipment_track')->addData($trackData);
        $shipment->addTrack($track);
        $shipment->register();
        $shipment->setEmailSent(true);
        $shipment->getOrder()->setCustomerNoteNotify(true);
        $shipment->getOrder()->setIsInProcess(true);
        //$createdDate = $this->_parseDate($data['created_at']);
        //$shipment->setCreatedAt($createdDate);

        $transactionSave->addObject($shipment);

        $invoice = $this->_initInvoice($order);
        if ($invoice) {
            $invoice->register();
            $invoice->setEmailSent(false);

            $invoice->getOrder()->setCustomerNoteNotify(true);
            $invoice->getOrder()->setIsInProcess(true);

            $transactionSave->addObject($invoice);
        }

        $transactionSave->addObject($order);
        $transactionSave->save();

        try {
            if ($invoice && $this->_sendInvoiceEmail) {
                $invoice->sendEmail(true, '');
            }
            if ($this->_sendShippingEmail) {
                $shipment->sendEmail(true, '');
            }
        } catch (Exception $e) {
            $this->_writeLog(Mage::helper('japanpost')->__('Unable to send email due to error: ') . $e->getMessage(), Zend_Log::ERR, $this->_file);
            Mage::logException($e);
        }
    }

    /**
     * Init invoice
     *
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Invoice|boolean
     */
    protected function _initInvoice(Mage_Sales_Model_Order $order)
    {
        if (!$order->canInvoice()) {
            return false;
        }

        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
        if ($invoice->getTotalQty() == 0) {
            return false;
        }

        $paymentMethod = $order->getPayment()->getMethod();
        $available = explode(',', Mage::getStoreConfig('carriers/sagawa/paymentmethods'));
        if (in_array($paymentMethod, $available)) {
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
        }

        return $invoice;
    }

    /**
     * Convert YYYY/MM/DD to YYYY-MM-DD H:m:s
     * @param $date
     * @return string
     */
    protected function _parseDate($date)
    {
        //validate source data
        if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date)) {
            return str_replace('/', '-', $date) . ' 00:00:00';
        }

        return null;
    }

    public function import()
    {
        $filename = $this->_file;
        if ($this->_isRunning()) {
            $this->_writeLog('Other process is running.', Zend_Log::WARN, basename($filename));
            return;
        }
        if (!file_exists($filename)) {
            Mage::throwException('File not found:' . $filename);
        }
        $this->_lock();
        try {
            $data = $this->_loadData($filename);

            if (is_array($data)) {
                $logFile = basename($filename);
                foreach ($data as $orderId => $shippmentData) {
                    try {
                        $this->createShippemnt($orderId, $shippmentData);
                    } catch (Exception $e) {
                        $this->_writeLog($e->getMessage(), Zend_Log::ERR, $logFile);
                        $this->setHasError(true);
                    }
                }
            }
        } catch (Exception $e) {
            $this->_unlock();
            Mage::throwException($e);
        }
        $this->_unlock();
        //do moving processed file

        if ($this->getHasError() == true) {
            $newFile = $filename . '.error';
            @rename($filename, $newFile);

            return false;
        } else {
            $newFile = $filename . '.completed';
            @rename($filename, $newFile);

            return true;
        }
    }

    protected function _loadData($file)
    {
        $data = array();
        $_reader = new Verite_Japanpost_Model_Csv_MbCsvReader($file, ',', 'SJIS', true);
        $_reader->pass();
        while ($_reader->next()) {
            $row = $_reader->current();
            if (empty($row)) {
                continue;
            }
            $data[$row['ご注文番号']] = array(
                'tracking_number' => $row['追跡番号'],
                'carrier_code'    => 'japanpost',
                'title'           => Mage::helper('japanpost')->__('Yuupack'),
            );
        }

        return $data;
    }

    public function getHasError()
    {
        return $this->_hasError;
    }

    public function setHasError($hasError)
    {
        $this->_hasError = $hasError;
    }

    /**
     * Make sure only one process is running
     */
    protected function _lock()
    {
        Mage::app()->getCache()->save('running', self::IMPORT_LOCK_FLAG, array(), self::LOCK_LIFETIME);
    }

    /**
     * Unlock process
     */
    protected function _unlock()
    {
        Mage::app()->getCache()->remove(self::IMPORT_LOCK_FLAG);
    }

    /**
     * Check if process is running
     *
     * @return boolean
     */
    protected function _isRunning()
    {
        return Mage::app()->getCache()->test(self::IMPORT_LOCK_FLAG);
    }

    public function setFile($file)
    {
        $this->_file = $file;
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function setSendInvoiceEmail($sendInvoiceEmail)
    {
        $this->_sendInvoiceEmail = $sendInvoiceEmail;
    }

    public function setSendShippingEmail($sendShippingEmail)
    {
        $this->_sendShippingEmail = $sendShippingEmail;
    }

    protected function _writeLog($message, $level = Zend_Log::INFO, $filename = '')
    {
        Verite_Japanpost_Model_Log::writeLog($message, Verite_Japanpost_Model_Log::LOG_TYPE_IMPORT, $level, $filename);
    }
}
