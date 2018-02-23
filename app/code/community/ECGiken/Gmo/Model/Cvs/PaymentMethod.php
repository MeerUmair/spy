<?php
class ECGiken_Gmo_Model_Cvs_PaymentMethod extends Mage_Payment_Model_Method_Abstract 
{
    const PAYMENT_ERROR_TEXT = 'CVS Payment Error.';

    protected $_code = "ecggmo_cvs";
    protected $_isGateway = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_isInitializeNeeded = true;
    protected $_formBlockType = 'ecggmo/form_cvs';
    protected $_infoBlockType = 'ecggmo/info_cvs';
    protected $_checkout;


    public function canUseInternal()
    {
        if($this->getConfigData('can_use_internal')) {
            return true;
        }
        return false;
    }

    public function getCheckout() {
        if (!isset($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }

    public function getQuote() {
        return $this->getCheckout()->getQuote();
    }

    public function assignData($data) {
        $this->_debugLog('assignData');
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $gmo = Mage::helper('ecggmo/gmo');
        $info = $this->getInfoInstance();
        $info->setGmoCvsCode($data->getCvsType());
        $info->setGmoCvsName($gmo->getCvsName($data->getCvsType()));
        $this->getCheckout()->setCvsType($data->getCvsType());
        $this->_debugLog('--- CvsType='. $this->getCheckout()->getCvsType());
        return $this;
    }

    public function validate() {
        $this->_debugLog('validate');
        $amount = Mage::helper('ecggmo')->getPriceText($this->_getAmount());
        $this->_debugLog('--- Amount='.$amount);
        if($this->getCheckout()->getCvsType() == ECGiken_Gmo_Helper_Gmo::CVS_TYPE_SEVEN_ELEVEN && $amount < 200) {
            Mage::throwException(Mage::helper('ecggmo')->__('You can not select Seven-Eleven payment amount is not a 200 yen or more'));
        }
        if($amount > 299999){
            Mage::throwException(Mage::helper('ecggmo')->__('Maximum amount: 299,999 yen'));
        }
        return $this;
    }

    public function initialize($action, $stateObject)
    {
        $this->_debugLog('initialize: action='.$action);
        $helper = Mage::helper('ecggmo');
        $info = $this->getInfoInstance();
        $gmo = Mage::helper('ecggmo/gmo');
        $cvsType = $this->getCheckout()->getCvsType();
        $customer = $info->getOrder()->getBillingAddress();
        $gmoOrderId = $this->_getMicroTimeText().'-'.$this->_getOrderId();
        $amount = Mage::helper('ecggmo')->getPriceText($this->_getAmount());

        if(!$output = $gmo->entryExecTranCvs($gmoOrderId, $amount, $cvsType, $customer)) {
            Mage::throwException($helper->__(self::PAYMENT_ERROR_TEXT));
        }
        $info->setGmoAccessId($output->getAccessId());
        $info->setGmoAccessPass($output->getAccessPass());
        $info->setGmoConfNo($output->getConfNo());
        $info->setGmoReceiptNo($output->getReceiptNo());
        $info->setGmoCvsCode($cvsType);
        $info->setGmoCvsName($gmo->getCvsName($cvsType));
        $info->setGmoCvsPayLimit($output->getPaymentTerm());
        $info->setGmoOrderId($gmoOrderId);

        $this->_debugLog('PaymentTerm='.$output->getPaymentTerm());
        $this->_debugLog('CvsPayLimit='.$info->getGmoCvsPayLimit());
        $this->_debugLog('ConfNo='.$info->getGmoConfNo());
        $this->_debugLog('ReceiptNo='.$info->getGmoReceiptNo());

        $stateObject->setIsNotified(false);
    }

    private function _getOrderId()
    {
        $info = $this->getInfoInstance();

        if ($this->_isPlaceOrder()) {
            return $info->getOrder()->getIncrementId();
        } else {
            if (!$info->getQuote()->getReservedOrderId()) {
                $info->getQuote()->reserveOrderId();
            }
            return $info->getQuote()->getReservedOrderId();
        }
    }

    private function _getAmount() {
        $info = $this->getInfoInstance();

        if ($this->_isPlaceOrder()) {
            return $info->getOrder()->getPayment()->getAmountOrdered();
        } else {
            return $this->getQuote()->getGrandTotal();
        }
    }

    private function _isPlaceOrder()
    {
        $info = $this->getInfoInstance();
        if ($info instanceof Mage_Sales_Model_Quote_Payment) {
            return false;
        } elseif ($info instanceof Mage_Sales_Model_Order_Payment) {
            return true;
        }
    }

    private function _getMicroTimeText() {
        $time = microtime();
        $time_list = explode(' ',$time);
        $time_micro = explode('.',$time_list[0]);
        return date('His').substr($time_micro[1],0,3);
    }

    private function _debugLog($message) {
        if ($this->getDebugFlag()) {
            Mage::log($message, null, 'payment_'.$this->getCode().'.log');
        }
    }
}
