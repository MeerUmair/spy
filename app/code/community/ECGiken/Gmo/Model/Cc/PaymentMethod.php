<?php
class ECGiken_Gmo_Model_Cc_PaymentMethod extends Mage_Payment_Model_Method_Cc 
{
    const PAYMENT_ERROR_TEXT = 'Credit Card Payment Error.';

    protected $_code = "ecggmo_cc";
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_canCancel = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc = false;
    protected $_formBlockType = 'ecggmo/form_cc';
    protected $_infoBlockType = 'ecggmo/info_cc';
    protected $paygent;
    protected $comm_helper;
    protected $orderPayment;
    protected $_checkout;
    protected $_customerSession;
    protected $_customer;

//    public function __construct() {
//        $this->paygent = Mage::helper('ecgpaygent/paygent');
//        $this->comm_helper = Mage::helper('ecggmo');
//    }

    public function getCheckout() {
        if(!isset($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }

    public function getCustomer() {
        if(!isset($this->_customer)) {
            if(!isset($this->_customerSession)) {
                Mage::getSingleton('core/session', array('name'=>'frontend') );
                $this->_customerSession = Mage::getSingleton('customer/session', array('name'=>'frontend') );
            }
            if($this->_customerSession->isLoggedIn()) {
                $this->_customer = $this->_customerSession->getCustomer();
            }else{
                return null;
            }
        }
        return $this->_customer;
    }

    // バックエンドで使用できるかどうかを返すメソッド（コンフィグで設定可能）
    public function canUseInternal()
    {
        if($this->getConfigData('can_use_internal')) {
            return true;
        }
        return false;
    }

    public function isAvailable($quote = null) {
        if (Mage_Payment_Model_Method_Abstract::isAvailable($quote)) {
            return true;
        }
        return false;
    }

    public function assignData($data) {
        $this->_debugLog('*** assignData');
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setCcType($data->getCcType())
             ->setCcOwner($data->getCcOwner())
             ->setCcLast4(substr($data->getCcNumber(), -4))
             ->setCcNumber($data->getCcNumber())
             ->setCcNumberEnc(Mage::helper('core')->encrypt($data->getCcNumber()))
             ->setCcExpMonth($data->getCcExpMonth())
             ->setCcExpYear($data->getCcExpYear())
             ->setCcCid($data->getCcCid());
        $saveCard = ($data->getCcSaveCard() == 1)? 1:0;
        $this->getCheckout()->setCcSaveCard($saveCard);
        list($cardSeq, $cardData) = explode('-', $data->getCcUseSaveCard());
        $this->getCheckout()->setCcUseSaveCard($cardSeq);
        $info->setGmoCcCardSeq($cardSeq);
        $info->setGmoCcCardData($cardData);
        $this->getCheckout()->setCcDeleteCard($data->getCcDeleteCard());
        $this->_debugLog('--- Save_Card='.$data->getCcSaveCard());
        $this->_debugLog('--- Store Save_Card='.$this->getCheckout()->getCcSaveCard());
        $this->_debugLog('--- Use_Save_Card='.$cardSeq);
        $this->_debugLog('--- Store Use_Save_Card='.$this->getCheckout()->getCcUseSaveCard());
        $this->_debugLog('--- Use_Save_Card_Data='.$cardData);
        $this->_debugLog('--- CardName='.$this->getGmoCardName($info->getCcType()));
        foreach($this->getCheckout()->getCcDeleteCard() as $card) {
            $this->_debugLog('--- DeleteCard='.$card);
        }
        return $this;
    }

    // テストカードを通すために、テストモードではバリデートしないようにする
    public function validate() {
        if($this->getConfigData('test')) {
            return $this;
        }
        if($this->getCheckout()->getCcUseSaveCard() == 999999) {
            return parent::validate();
        }
        return $this;
    }

    // オーソリ
    public function authorize(Varien_Object $payment, $amount) {
        $this->_debugLog('*** authorize');
        $this->_entryExecTran($payment, $amount, ECGiken_Gmo_Helper_Gmo::CC_JOBCD_AUTH);
        return $this;
    }

    // カード情報の登録
    protected function _registCard($ccNo, $expire, $cardName) {
        $this->_debugLog('_registCard: ccNo='.$ccNo.' expire='.$expire);
        $gmo = Mage::helper('ecggmo/gmo');
        // 会員登録されているかチェックしてされていなければ会員登録する
        $customer = $this->getCustomer();
        if($this->getCheckout()->getCcSaveCard() && $customer != null && $this->getCheckout()->getCcUseSaveCard() == 999999) {
            $this->_debugLog('Save Card: Customer ID='.$customer->getId());
            if(!$ret = $gmo->isMemberAlready($customer)) Mage::throwException($this->comm_helper->__(self::PAYMENT_ERROR_TEXT));
            if($ret == ECGiken_Gmo_Helper_Gmo::MEMBER_NOT_FOUND) {
                if(!$gmo->saveMember($customer)) {
                    Mage::throwException($this->comm_helper->__(self::PAYMENT_ERROR_TEXT));
                }
            }else{
                $this->_debugLog('Customer is already');
            }
            // カード登録
            if(!$gmo->saveCard($customer, $ccNo, $expire, $cardName)) {
                Mage::throwException($this->comm_helper->__(self::PAYMENT_ERROR_TEXT));
            }
        }
    }

    protected function _deleteGmoCard() {
        $this->_debugLog('_deleteGmoCard');
        $cards = $this->getCheckout()->getCcDeleteCard();
        if(!empty($cards)) {
            $customer = $this->getCustomer();
            if($customer != null) {
                $gmo = Mage::helper('ecggmo/gmo');
                foreach($cards as $card) {
                    if(!$gmo->deleteCard($customer, $card)) {
                        Mage::throwException($this->comm_helper->__(self::PAYMENT_ERROR_TEXT));
                    }
                    $this->_debugLog('Deleted Card='.$card);
                }
            }
        }
    }

    // 実際にGMOにカード登録、決済処理を実行する部分
    protected function _entryExecTran(Varien_Object $payment, $amount, $jobcd) {
        $this->_debugLog('_entryExecTran amount: '.$amount.' jobcd: '.$jobcd);
        $this->getOrderPayment($payment);
        $this->comm_helper = Mage::helper('ecggmo');
        $gmo = Mage::helper('ecggmo/gmo');
        $info = $this->getInfoInstance();
        $gmoOrderId = $this->_getMicroTimeText().'-'.$this->getOrderId($payment);
        $expire = substr($info->getCcExpYear(), -2).substr("00".$info->getCcExpMonth(), -2);

        $this->_deleteGmoCard();
        // カード情報登録
        $this->_registCard($info->getCcNumber(), $expire, $this->getGmoCardName($info->getCcType()));

        // 決済実行
        $ccParam = array();
        $customer = $this->getCustomer();
        if($this->isUseSaveCardFunction() && $this->getCheckout()->getCcUseSaveCard() != 999999 && $customer != null) {
            $ccParam['mode'] = ECGiken_Gmo_Helper_Gmo::CC_MODE_USE_CARD_ID;
            $ccParam['memberId'] = $customer->getId();
            $ccParam['cardId'] = $this->getCheckout()->getCcUseSaveCard();
        }else{
            $ccParam['mode'] = ECGiken_Gmo_Helper_Gmo::CC_MODE_USE_CC_NO;
            $ccParam['ccNo'] = $info->getCcNumber();
            $ccParam['expire'] = $expire;
            $ccParam['ccCid'] = $info->getCcCid();
        }
        if(!$output = $gmo->entryExecTran($jobcd, $amount, $gmoOrderId, $ccParam)) {
            Mage::throwException($this->comm_helper->__(self::PAYMENT_ERROR_TEXT));
        }
        $this->orderPayment->setTransactionId($output->getTranId());
        $this->orderPayment->setIsTransactionClosed(false);
        $info->setGmoAccessId($output->getAccessId());
        $info->setGmoAccessPass($output->getAccessPass());
        $info->setGmoApprove($output->getApprovalNo());
        $info->setGmoTranId($output->getTranId());
        $info->setGmoOrderId($gmoOrderId);

        if ($this->getDebugFlag()) {
            $this->_debugLog('--- EntryExecTran Response OK!');
            $this->_debugLog('--- Transaction ID: '.$output->getTranId());
            $this->_debugLog('--- AccessId ID: '.$output->getAccessId());
            $this->_debugLog('--- AccessPass ID: '.$output->getAccessPass());
            $this->_debugLog('--- ApprovalNo ID: '.$output->getApprovalNo());
        }
        return $this;
    }

    public function capture(Varien_Object $payment, $amount){
        $this->_debugLog('*** capture');
        if( $this->getConfigData('payment_action') === 'authorize_capture' ) {
            $this->_entryExecTran($payment, $amount, ECGiken_Gmo_Helper_Gmo::CC_JOBCD_CAPTURE);
        }else{
            $this->getOrderPayment($payment);
            $this->comm_helper = Mage::helper('ecggmo');
            $info = $this->getInfoInstance();

            $output = $this->_alterTran($payment, ECGiken_Gmo_Helper_Gmo::CC_JOBCD_SALES, $amount);

            $info->setGmoAccessId($output->getAccessId());
            $info->setGmoAccessPass($output->getAccessPass());
            $info->setGmoApprove($output->getApprovalNo());
            $info->setGmoTranId($output->getTranId());
            
            if ($this->getDebugFlag()) {
                $this->_debugLog('--- Transaction ID: '.$output->getTranId());
                $this->_debugLog('--- AccessId ID: '.$output->getAccessId());
                $this->_debugLog('--- AccessPass ID: '.$output->getAccessPass());
                $this->_debugLog('--- ApprovalNo ID: '.$output->getApprovalNo());
            }
        }
        return $this;
    }

    protected function _alterTran(Varien_Object $payment, $jobcd, $amount=0){
        $this->_debugLog('_alterTran jobcd: '.$jobcd);
        $this->getOrderPayment($payment);
        $this->comm_helper = Mage::helper('ecggmo');
        $info = $this->getInfoInstance();
        $gmo = Mage::helper('ecggmo/gmo');

        if(!$output = $gmo->alterTran($jobcd, $info->getGmoAccessId(), $info->getGmoAccessPass(), $amount)) {
            Mage::throwException($this->comm_helper->__(self::PAYMENT_ERROR_TEXT));
        }
        $this->orderPayment->setIsTransactionClosed(true);
        $this->_debugLog('--- AlterTran Response OK!');
        return $output;
    }
    
    public function void(Varien_Object $payment){
        $this->_debugLog('*** void');
        $this->cancel($payment);
        return $this;
    }

    public function cancel(Varien_Object $payment){
        $this->_debugLog('*** cancel');
        $this->_alterTran($payment, ECGiken_Gmo_Helper_Gmo::CC_JOBCD_VOID);
        return $this;
    }

    protected function _changeTran(Varien_Object $payment, $amount){
        $this->_debugLog('_cangeTran amount='.$amount);
        $this->comm_helper = Mage::helper('ecggmo');
        $info = $this->getInfoInstance();
        $gmo = Mage::helper('ecggmo/gmo');

        if(!$output = $gmo->changeTran($info->getGmoAccessId(), $info->getGmoAccessPass(), $amount)) {
            Mage::throwException($this->comm_helper->__(self::PAYMENT_ERROR_TEXT));
        }
        $this->_debugLog('--- changeTran Response OK!');
        return $output;
    }

    public function refund(Varien_Object $payment, $amount){
        $this->_debugLog('*** refund: amount='.$amount);
        $this->comm_helper = Mage::helper('ecggmo');
        $newAmount = $this->comm_helper->getPriceText($amount);
        $nowAmount = $this->comm_helper->getPriceText($payment->getOrder()->getGrandTotal());
        $this->_debugLog('Original Amount='.$nowAmount);
        $this->_debugLog('New Amount='.$newAmount);
        $difference = $nowAmount - $newAmount;
        $this->_debugLog('Difference='.$difference);
        if($difference > 0) {
            $this->_changeTran($payment, $difference);
        }elseif($difference == 0) {
            $this->_alterTran($payment, ECGiken_Gmo_Helper_Gmo::CC_JOBCD_RETURN, $newAmount);
        }else{
            Mage::throwException($this->comm_helper->__('It is not possible to specify in excess of the original amount.'));
        }
        return $this;
    }

    protected function getOrderPayment(Varien_Object $payment) {
        $this->orderPayment = $payment->getOrder()->getPayment();
    }

/*    protected function getPaymentId(Varien_Object $payment) {
        if( empty($this->orderPayment) ){
            $this->getOrderPayment($payment);
        }
        $payment_id = $this->orderPayment->getParentTransactionId();
        return $payment_id;
    }*/

    protected function getOrderId($payment) {
        $order = $payment->getOrder();
        return $order->getIncrementId();
    }

    public function getGmoCardName($ccType) {
        $arrCardName = array(
                'VI' =>'Visa',
                'MC' =>'MasterCard',
                'AE' =>'Amex',
                'DI' =>'Discover',
                'JCB'=>'JCB',
                );
        return $arrCardName[$ccType];
    }

    public function getVerificationRegEx()
    {
        $verificationExpList = array(
                'VI' => '/^[0-9]{3}$/', // Visa
                'MC' => '/^[0-9]{3}$/',       // Master Card
                'AE' => '/^[0-9]{4}$/',        // American Express
                'DI' => '/^[0-9]{3}$/',          // Discovery
                'SS' => '/^[0-9]{3,4}$/',
                'SM' => '/^[0-9]{3,4}$/', // Switch or Maestro
                'SO' => '/^[0-9]{3,4}$/', // Solo
                'OT' => '/^[0-9]{3,4}$/',
                'JCB' => '/^[0-9]{3,4}$/' //JCB
                );
        return $verificationExpList;
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

    public function isUseSaveCardFunction() {
        return $this->getConfigData('save_card');
    }
}

