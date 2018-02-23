<?php
class ECGiken_Gmo_Model_Cron_SearchTradeMulti {
    const LOG_HEADER = '[Cron_SearchTradeMulti] ';
    const LOG_FILE_NAME = 'cron_search_trade_multi.log';
    private $gmo;

    public function run(){
        $this->gmo = Mage::helper('ecggmo/gmo');

        if(!Mage::getStoreConfig('ecggmo/ecggmo_common/exec_crontab')) {
            $this->_debugLog('--- Do not run ---');
            return;
        }

        $this->_debugLog('--- Start ---');

        $this->cvsProc();

        $this->_debugLog('--- End ---');
    }

    protected function cvsProc() {
        $orders = $this->getOrders('ecggmo_cvs');
        $payType = ECGiken_Gmo_Helper_Gmo::PAY_TYPE_CVS;
        if(count($orders) == 0) {
            $this->_debugLog('No new(or pending) CVS order');
        }
        foreach($orders as $order) {
            $output = $this->gmo->searchTradeMulti($order['gmo_order_id'], $payType, ECGiken_Gmo_Helper_Gmo::CODE_CVS);
            if(!$output) {
                $this->_debugLog('searchTradeMulti Error');
                return false;
            }
            $status = $output->getStatus();
            $this->_debugLog($order['entity_id'].':'.$order['increment_id'].':'.$order['state'].':'.$order['status'].':'.$order['gmo_order_id']);
            $this->_debugLog('--->'.$status);
            $objOrder = $this->getOrder($order['entity_id']);
            if($status == ECGiken_Gmo_Helper_Gmo::STATUS_PAYSUCCESS) {
                $this->toProcessing($objOrder, 'ecggmo_cvs');
            }else{
                $this->chkLimit($objOrder, $order['gmo_cvs_pay_limit'], 'ecggmo_cvs');
            }
        }
        return true;
    }

    protected function getOrder($id) {
        return Mage::getModel('sales/order')->load($id);
    }

    protected function getOrders($method) {
        $sql = "
           SELECT
                 o.entity_id
               , o.increment_id
               , o.state
               , o.status
               , op.gmo_order_id
               , op.gmo_cvs_pay_limit
           FROM
                 sales_flat_order as o
               , sales_flat_order_payment as op
           WHERE
               o.entity_id = op.parent_id
               AND o.state IN ('new', 'pending')
               AND op.method = '".$method."'
        ";
        //AND o.state IN ('new', 'pending', 'holded')
        $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $orders = $_read->fetchAll($sql);
        return $orders;
    }

    protected function toProcessing($order, $method) {
        $this->_debugLog("hasInvoices = ".$order->hasInvoices());
        if(!$order->hasInvoices()) {
            $order->setState('processing', 'processing');
            $this->_debugLog("make invoice");
            $invoice = $order->prepareInvoice();
            $invoice->register()->pay();
            $order->addRelatedObject($invoice);
            $order->save();
            if($this->isSendCaptureEmail($method)) {
                $this->_debugLog("send Email");
                $invoice->sendEmail ();
                $invoice->setEmailSent (true);
            }
        }
    }

    protected function isSendCaptureEmail($method) {
        switch ($method) {
            case 'ecggmo_cvs':
                if(Mage::getStoreConfig('payment/ecggmo_cvs/send_capture_email')) {
                    return true;
                }
                break;
        }
        return false;
    }

    protected function chkLimit($order, $limit, $method) {
        $limit_time = strtotime($limit);
        $now = time();
        if($now > $limit_time) {
            $order->setState('canceled', 'canceled', 'Payment is past due');
            $order->save();
        }
    }

    protected function _debugLog($message) {
        if(Mage::getStoreConfig('ecggmo/ecggmo_common/crontab_debug')) {
            Mage::log(self::LOG_HEADER.$message, null, self::LOG_FILE_NAME);
        }
    }

}
