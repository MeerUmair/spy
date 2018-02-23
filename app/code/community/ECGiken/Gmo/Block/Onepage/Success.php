<?php
class ECGiken_Gmo_Block_Onepage_Success extends Mage_Checkout_Block_Onepage_Success
{

    protected $_order;
    protected $_payment;

    protected function _construct() {
        $this->_order = $this->getOrder();
        $this->_payment = $this->getPayment();
        $payment_code = $this->_payment->getMethodInstance()->getCode();
        if($payment_code == 'ecggmo_cvs') {
            $this->setTemplate('ecggmo/success_cvs.phtml');
        }
    }

    protected function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }

    protected function getOrder() {
        return Mage::getModel('sales/order')->load($this->getCheckout()->getLastOrderId());
    }

    protected function getPayment() {
        return $this->_order->getPayment();
    }
}
