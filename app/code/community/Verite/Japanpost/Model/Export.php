<?php

class Verite_Japanpost_Model_Export extends Varien_Object
{
    const LOCK_FLAG = 'yamamo_export_lock';

    private $_rowTemplate = array(
        '郵便番号' => '',
        '住所1' => '',
        '住所2' => '',
        '受取人の名前' => '',
        'TEL' => '',
        '配達日指定' => '',
        '時間帯指定' => '',
        '代金引換金額' => '',
        'メール' => '',
        '備考' => '',
        '支払方法' => '',
        'ご注文番号' => '',
        '購入日' => '',
        '状態' => '',
        '請求先名' => '',
        '商品' => '',

    );

    /**
     * Perform export data.
     *
     * @param $ids (Optional) list of order id
     * @return boolean|String Return exported file path or false if error.
     */
    public function export($ids = array())
    {
        if ($this->isLocking()) {
            $this->_writeLog(Mage::helper('japanpost')->__('Other export process is running.'), Zend_Log::WARN);
            return false;
        }

        $this->lock();

        $_orders = $this->_getExportOrders($ids);
        /* @var $_exportBuffer Verite_Japanpost_Model_Export_Buffer */
        $_exportBuffer = Mage::getModel('japanpost/export_buffer');
        $_exportBuffer->getResource()->truncate();

        $headers = array_keys($this->_rowTemplate);
        $_exportBuffer->writeRow($this->_strPutCsv($headers));

        $this->_writeLog(Mage::helper('japanpost')->__('Starting export %s order(s).',$_orders->count()));
        $counter = 0;
        foreach ($_orders as $order) {
            //By pass virtual order.
            if ($order->getIsVirtual()) {
                $this->_writeLog(Mage::helper('japanpost')->__('Order #%s is virtual.', $order->getIncrementId()), Zend_Log::WARN);
                continue;
            }
            $row = $this->_fillData($this->_convertOrder($order));
            $data = $this->_strPutCsv($row);

            $_exportBuffer->writeRow($data, $order->getId());
            $counter++;
        }

        try {
            $data = $_exportBuffer->getDataAsString();

            $this->_writeLog(Mage::helper('japanpost')->__('Export completed. %s order(s) are exported.', $counter));
            $this->unlock();

            return $data;
        } catch (Exception $e) {
            $this->_writeLog($e->getMessage(), Zend_Log::ERR);
            return false;
        }

        return $filePath;
    }

    /**
     * Return orders that will be exported.
     *
     * @param $ids (Optional) list of order id
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getExportOrders($ids)
    {
        /* @var $orders Mage_Sales_Model_Resource_Order_Collection */
        $orders = Mage::getResourceModel('sales/order_collection');
        $orders->addFieldToSelect('*');
        if (count($ids) > 0) {
            $orders->addAttributeToFilter('entity_id', array('in' => $ids));
            //$orders->addAttributeToFilter('shipping_method', array('eq' => 'japanpost_japanpost'));
        } else {
            $orders->addAttributeToFilter('is_japanpost_exported', 0);
            //$orders->addAttributeToFilter('shipping_method', array('eq' => 'japanpost_japanpost'));
        }

        return $orders;
    }

    /**
     * Convert order to exportable array
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function _convertOrder(Mage_Sales_Model_Order $order)
    {
        $rows = array();
        $row = array();
        $row['ご注文番号'] = $order->getIncrementId();
        if ($date = $order->getDeliveryDate()) {
            $format = Mage::getStoreConfig('deliverydate/date/display_format');
            if ($format) {
                $date = str_replace(array('年','月','-'), '/', $date);
                $date = str_replace(array('日'), '', $date);
                if($date == '指定しない'){
                    $date = '';
                }
                $row['配達日指定'] = $date;

            }
        }

        if ($time = $order->getDeliveryTime()) {
            $row['時間帯指定'] = $time;
        }

        //***** SHIPPING ADDRESS *****//
        $shippingAddress = $order->getShippingAddress();
        $row['TEL'] = $shippingAddress->getTelephone();
        $row['郵便番号'] = $shippingAddress->getPostcode();
        $row['住所1'] = $shippingAddress->getRegion().$shippingAddress->getCity().$shippingAddress->getStreet1();
        $row['住所2'] = $shippingAddress->getStreet2();

        //if ($shippingAddress->getCompany()) {
        //    $row['お届け先会社・部門名１'] = $shippingAddress->getCompany();
        //}

        $row['受取人の名前'] = $shippingAddress->getName();
        //$row['お届け先名略称カナ'] = mb_convert_kana($shippingAddress->getLastnamekana() . $shippingAddress->getFirstnamekana(), 'khrnas');

        //***** BILLING ADDRESS *****//
        $billingAddress = $order->getBillingAddress();
        $row['請求先名'] = $billingAddress->getName();


        if ($order->getPayment()->getMethod() == 'cashondelivery' || $order->getPayment()->getMethod() == 'phoenix_cashondelivery') {
            $row['代金引換金額'] = number_format($order->getGrandTotal());
        }
        $row['支払方法'] = $order->getPayment()->getMethodInstance()->getTitle();

        if($order->getGiftMessageId()) {
            $message = Mage::getSingleton('giftmessage/message')->load($order->getGiftMessageId())->getMessage();
            $row['備考'] = $message;
        } else {
            $row['備考'] = '';
        }

        $orderItems = $order->getAllVisibleItems();
        $i = 1;
        foreach ($orderItems as $item) {
            if ($i == 1) {
                $row['商品'] = $item->getSku() . " " . $item->getName();
            }

        }
        $row['メール'] = $order->getCustomerEmail();
        $row['状態'] = $order->getStatus();
        $row['購入日']= $order->getCreatedAtStoreDate();



        return $row;
    }

    /**
     * compare billing & shipping address
     * @param $billing Billing Address
     * @param $shipping Shipping Address
     * @return boolean
     */
    protected function _compareAddress($billing, $shipping)
    {
        if($billing->getFormated('text') == $shipping->getFormated('text')){
            return true;
        }

        return false;
    }

    /**
     * Make full row to export
     * @param $row
     * @return array
     */
    protected function _fillData($row) {
        $newRow = $this->_rowTemplate;

        foreach ($row as $k => $v) {
            $newRow[$k] = $v;
        }

        return $newRow;
    }

    /**
     * Writing array to string in csv format.
     *
     * @param array $data Data to write
     * @param string $delimiter CSV delimter
     * @param string $enclosure CSV enclosure
     * @return mixed String
     */
    protected function _strPutCsv($data, $delimiter = ",", $enclosure = '"')
    {
        $stream = fopen('php://memory', 'r+');
        fputcsv($stream, $data, $delimiter, $enclosure);
        rewind($stream);
        $rowData = fread($stream, 9999);
        $rowData =  str_replace(array("\r","\n"), "", $rowData);

        return $rowData;
    }

    /**
     * Return export file path
     * @return string
     */
    public function getFilePath($filename = null)
    {
        $filePath = Mage::getBaseDir('var') . DS . 'export' . DS . 'japanpost' . DS . 'shipping';
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }
        if ($filename === null) {
            $filePath .= DS . 'japanpost_shipping_' . date('Ymdhi') . '.csv';
        } elseif ($filename === false) {
            return $filePath;
        } else {
            $filePath .= DS . $filename;
        }

        return $filePath;
    }

    /**
     * Concurrent lock.
     * To make sure that only one process run at same time.
     */
    protected function lock()
    {
        Mage::app()->saveCache(1, self::LOCK_FLAG, array(), 3600); //cache 1 hour
    }

    /**
     * Check if export process is running.
     * @return false|int
     */
    protected function isLocking()
    {
        return Mage::app()->getCache()->test(self::LOCK_FLAG);
    }

    /**
     * Remove lock
     */
    protected function unlock()
    {
        Mage::app()->getCache()->remove(self::LOCK_FLAG);
    }

    /**
     * Write log
     *
     * @param string $message
     * @param int $level
     */
    protected function _writeLog($message, $level = Zend_Log::INFO)
    {
        Verite_Japanpost_Model_Log::writeLog($message, Verite_Japanpost_Model_Log::LOG_TYPE_EXPORT, $level);
    }
}
