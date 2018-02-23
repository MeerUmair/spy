<?php
require_once( 'com/gmo_pg/client/input/SaveMemberInput.php');
require_once( 'com/gmo_pg/client/tran/SaveMember.php');
require_once( 'com/gmo_pg/client/input/SearchMemberInput.php');
require_once( 'com/gmo_pg/client/tran/SearchMember.php');
require_once( 'com/gmo_pg/client/input/SearchCardInput.php');
require_once( 'com/gmo_pg/client/tran/SearchCard.php');
require_once( 'com/gmo_pg/client/input/SaveCardInput.php');
require_once( 'com/gmo_pg/client/tran/SaveCard.php');
require_once( 'com/gmo_pg/client/input/DeleteCardInput.php');
require_once( 'com/gmo_pg/client/tran/DeleteCard.php');
require_once( 'com/gmo_pg/client/input/EntryTranInput.php');
require_once( 'com/gmo_pg/client/input/ExecTranInput.php');
require_once( 'com/gmo_pg/client/input/EntryExecTranInput.php');
require_once( 'com/gmo_pg/client/tran/EntryExecTran.php');
require_once( 'com/gmo_pg/client/input/AlterTranInput.php');
require_once( 'com/gmo_pg/client/tran/AlterTran.php');
require_once( 'com/gmo_pg/client/input/EntryTranCvsInput.php');
require_once( 'com/gmo_pg/client/input/ExecTranCvsInput.php');
require_once( 'com/gmo_pg/client/input/EntryExecTranCvsInput.php');
require_once( 'com/gmo_pg/client/tran/EntryExecTranCvs.php');
require_once( 'com/gmo_pg/client/input/SearchTradeMultiInput.php');
require_once( 'com/gmo_pg/client/tran/SearchTradeMulti.php');
require_once( 'com/gmo_pg/client/input/ChangeTranInput.php');
require_once( 'com/gmo_pg/client/tran/ChangeTran.php');

class ECGiken_Gmo_Helper_Gmo extends Mage_Core_Helper_Abstract {
    // カード番号モード
    const CC_MODE_USE_CC_NO = 1;
    const CC_MODE_USE_CARD_ID = 2;
    // カード決済処理種別
    const CC_JOBCD_AUTH = 'AUTH';
    const CC_JOBCD_CAPTURE = 'CAPTURE';
    const CC_JOBCD_SALES = 'SALES';
    const CC_JOBCD_VOID = 'VOID';
    const CC_JOBCD_RETURN = 'RETURN';
    // コンビニ種別
    const CVS_TYPE_LAWSON = '00001';
    const CVS_TYPE_FAMILY_MART = '00002';
    const CVS_TYPE_CIRCLE_K_SUNKS = '00003';
    const CVS_TYPE_MINISTOP = '00005';
    const CVS_TYPE_DAILY_YAMAZAKI = '00006';
    const CVS_TYPE_SEVEN_ELEVEN = '00007';
    // 決済方法
    const PAY_TYPE_CC = 0;
    const PAY_TYPE_CVS = 3;
    // 取引ステータス
    const STATUS_PAYSUCCESS = 'PAYSUCCESS';

    const MAX_CARD_COUNT = 5;
    const LOG_HEADER = '[Helper_GMO] ';
    const CODE_CC = 'ecggmo_cc';
    const CODE_CVS = 'ecggmo_cvs';
    const ERROR_LOG_FILE = 'ECGiken_GMO_Payment_Error.log';
    const MEMBER_NOT_FOUND = 'not found';
    const MEMBER_ALREADY = 'already';

    // コンビニコードからコンビニ名を返す
    public function getCvsName($code) {
        $helper = Mage::helper('ecggmo');
        $arrTypes = array(
            '00001'=>$helper->__('LAWSON'),
            '00002'=>$helper->__('FamilyMart'),
            '00003'=>$helper->__('Circle K Sunkus'),
            '00005'=>$helper->__('MINISTOP'),
            '00006'=>$helper->__('DailyYAMAZAKI'),
            '00007'=>$helper->__('Seven-Eleven'),
        );
        return $arrTypes[$code];
    }

    public function getReceiptNoText($code) {
        $helper = Mage::helper('ecggmo');
        $arrNames = array(
            '00001'=>$helper->__('Customrs No.'),
            '00002'=>$helper->__('Customrs No.'),
            '00003'=>$helper->__('Online Payment No.'),
            '00005'=>$helper->__('Online Payment No.'),
            '00006'=>$helper->__('Online Payment No.'),
            '00007'=>$helper->__('Payment coupon No.'),
        );
        return $arrNames[$code];
    }

    // カード決済
    public function entryExecTran($jobcd, $amount, $gmoOrderId, $arrCcInfo=array()) {
        $this->_debugLog(self::CODE_CC, '- entryExecTran -');
        if(!$output = $this->ifEntryExecTran($jobcd, $amount, $gmoOrderId, $arrCcInfo)) {
            return false;
        }else{
            if( $output->isErrorOccurred() ){
                $errorList = $this->_getEntryExecErorInfo($output);
                $this->_debugLog(self::CODE_CC, '--> Request return Error');
                $this->putGmoErrorLog($errorList, 'entryExecTran');
                return false;
            }
        }
        return $output;
    }

    private function _getEntryExecErorInfo($output) {
        if( $output->isEntryErrorOccurred()  ){
            $errorList = $output->getEntryErrList() ;
        }else{
            $errorList = $output->getExecErrList(); 
        }
        return $errorList;
    }

    // カード決済実行部分
    public function ifEntryExecTran($jobcd, $amount, $gmoOrderId, $arrCcInfo) {
        $comm_helper = Mage::helper('ecggmo');
        $entryInput = new EntryTranInput();
        $entryInput->setShopId($this->_getCommonConfigData('shop_id'));
        $entryInput->setShopPass($this->_getCommonConfigData('shop_pass'));
        $entryInput->setJobCd($jobcd);
        $entryInput->setOrderId($gmoOrderId);
        $entryInput->setAmount($comm_helper->getPriceText($amount));
        $entryInput->setTdFlag('0');

        $execInput = new ExecTranInput();
        $execInput->setOrderId($gmoOrderId);
        $execInput->setMethod('1');
        if($arrCcInfo['mode'] == self::CC_MODE_USE_CC_NO) {
            $execInput->setCardNo($arrCcInfo['ccNo']);
            $execInput->setExpire($arrCcInfo['expire']);
            $execInput->setSecurityCode($arrCcInfo['ccCid']);
        }else{
            $execInput->setSiteId($this->_getCommonConfigData('site_id'));
            $execInput->setSitePass($this->_getCommonConfigData('site_pass'));
            $execInput->setMemberId($this->gmoMemberID($arrCcInfo['memberId']));
            $execInput->setCardSeq($arrCcInfo['cardId']);
            $execInput->setSeqMode(1);
        }

        if($this->_isDebug(self::CODE_CC)) {
            $this->_debugLog(self::CODE_CC, 'gmoOrderId: '.$gmoOrderId);
            $this->_debugLog(self::CODE_CC, 'shop_id: '.$this->_getCommonConfigData('shop_id'));
            $this->_debugLog(self::CODE_CC, 'shop_pass: '.$this->_getCommonConfigData('shop_pass'));
            if($arrCcInfo['mode'] == self::CC_MODE_USE_CC_NO) {
                $this->_debugLog(self::CODE_CC, 'ccNum: '.$arrCcInfo['ccNo']);
                $this->_debugLog(self::CODE_CC, 'expDate: '.$arrCcInfo['expire']);
                $this->_debugLog(self::CODE_CC, 'ccCid: '.$arrCcInfo['ccCid']);
            }else{
                $this->_debugLog(self::CODE_CC, 'memberId: '.$this->gmoMemberID($arrCcInfo['memberId']));
                $this->_debugLog(self::CODE_CC, 'cardId: '.$arrCcInfo['cardId']);
            }
            $this->_debugLog(self::CODE_CC, 'Amount: '.$comm_helper->getPriceText($amount));
        }

        $input = new EntryExecTranInput();
        $input->setEntryTranInput( $entryInput );
        $input->setExecTranInput( $execInput );

        $exe = new EntryExecTran();
        $output = $exe->exec( $input );
        if( $exe->isExceptionOccured() ){
            $this->_debugLog(self::CODE_CC, '--> Exception Error');
            Mage::log('GMO Exception.', null, self::ERROR_LOG_FILE);
            return false;
        }
        return $output;
    }

    // 決済操作
    public function alterTran($jobcd, $gmoAccessId, $gmoAccessPass, $amount=0) {
        $this->_debugLog(self::CODE_CC, '- alterTran -');
        if(!$output = $this->ifAlterTran($jobcd, $gmoAccessId, $gmoAccessPass, $amount)) {
            return false;
        }else{
            if( $output->isErrorOccurred() ){
                $errorList = $output->getErrList();
                $this->_debugLog(self::CODE_CC, '--> Request return Error');
                $this->putGmoErrorLog($errorList, 'alterTran');
                return false;
            }
        }
        return $output;
    }

    // 決済操作実行部分
    public function ifAlterTran($jobcd, $gmoAccessId, $gmoAccessPass, $amount) {
        $comm_helper = Mage::helper('ecggmo');
        $input = new AlterTranInput();
        $input->setShopId($this->_getCommonConfigData('shop_id'));
        $input->setShopPass($this->_getCommonConfigData('shop_pass'));
        $input->setAccessId($gmoAccessId);
        $input->setAccessPass($gmoAccessPass);
        $input->setJobCd($jobcd);
        if($jobcd == self::CC_JOBCD_SALES) {
            $input->setAmount($comm_helper->getPriceText($amount));
            $input->setMethod('1');
        }

        if($this->_isDebug(self::CODE_CC)) {
            $this->_debugLog(self::CODE_CC, 'jobcd: '.$jobcd);
            //$this->_debugLog(self::CODE_CC, 'gmoOrderId: '.$info->getGmoOrderId());
            $this->_debugLog(self::CODE_CC, 'shop_id: '.$this->_getCommonConfigData('shop_id'));
            $this->_debugLog(self::CODE_CC, 'shop_pass: '.$this->_getCommonConfigData('shop_pass'));
            $this->_debugLog(self::CODE_CC, 'AccessId: '.$gmoAccessId);
            $this->_debugLog(self::CODE_CC, 'AccessPass: '.$gmoAccessPass);
            $this->_debugLog(self::CODE_CC, 'Amount: '.$comm_helper->getPriceText($amount));
        }

        $exe = new AlterTran();
        $output = $exe->exec($input);
        if($exe->isExceptionOccured()){
            $this->_debugLog(self::CODE_CC, '--> Exception Error');
            Mage::log('GMO Exception.', null, self::ERROR_LOG_FILE);
            return false;
        }
        return $output;
    }

    // 金額変更
    public function changeTran($accessID, $accessPass, $amount) {
        $this->_debugLog(self::CODE_CC, '- changeTran -');
        if(!$output = $this->ifChangeTran($accessID, $accessPass, $amount)) {
            return false;
        }else{
            if($output->isErrorOccurred()){
                $errorList = $output->getErrList();
                $this->_debugLog(self::CODE_CC, '--> Request return Error');
                $this->putGmoErrorLog($errorList, 'changeTran');
                return false;
            }
        }
        $this->_debugLog(self::CODE_CC, '--> Change Tran OK');
        return $output;
    }

    // 金額変更実行部分
    public function ifChangeTran($accessID, $accessPass, $amount) {
        $input = new ChangeTranInput();
        $input->setShopId($this->_getCommonConfigData('shop_id'));
        $input->setShopPass($this->_getCommonConfigData('shop_pass'));
        $input->setAccessId($accessID);
        $input->setAccessPass($accessPass);
        $input->setJobCd(self::CC_JOBCD_CAPTURE);
        $input->setAmount($amount);
        $exe = new ChangeTran();
        $output = $exe->exec($input);
        if($exe->isExceptionOccured()){
            $this->_debugLog(self::CODE_CC, '--> Exception Error');
            Mage::log('GMO Exception.', null, self::ERROR_LOG_FILE);
            return false;
        }
        return $output;
    }

    // 会員登録
    public function saveMember($customer) {
        $this->_debugLog(self::CODE_CC, '- saveMember -');
        if(!$output = $this->ifSaveMember($customer)) {
            return false;
        }else{
            if($output->isErrorOccurred()){
                $errorList = $output->getErrList();
                $this->_debugLog(self::CODE_CC, '--> Request return Error');
                $this->putGmoErrorLog($errorList, 'SaveMember');
                return false;
            }
        }
        $this->_debugLog(self::CODE_CC, '--> Member Save OK');
        return $output;
    }

    // 会員登録実行部分
    public function ifSaveMember($customer) {
        $input = new SaveMemberInput();
        $input->setSiteId($this->_getCommonConfigData('site_id'));
        $input->setSitePass($this->_getCommonConfigData('site_pass'));
        $input->setMemberId($this->gmoMemberID($customer->getId()));
        $input->setMemberName(mb_convert_encoding($customer->getName(), 'SJIS', 'UTF-8'));
        $exe = new SaveMember();
        $output = $exe->exec($input);
        if($exe->isExceptionOccured()){
            $this->_debugLog(self::CODE_CC, '--> Exception Error');
            Mage::log('GMO Exception.', null, self::ERROR_LOG_FILE);
            return false;
        }
        return $output;
    }

    // 会員登録されているかチェック
    public function isMemberAlready($customer) {        
        $this->_debugLog(self::CODE_CC, '- isMemberAlready -');
        if(!$output = $this->ifSearchMember($customer)) {
            return false;
        }else{
            if($output->isErrorOccurred()){
                $errorList = $output->getErrList();
                if($this->_chkErrorInfo($errorList, 'E01390002')) {
                    return self::MEMBER_NOT_FOUND;
                }else{
                    $this->_debugLog(self::CODE_CC, '--> Request return Error');
                    $this->putGmoErrorLog($errorList, 'isMemberAlready');
                    return false;
                }
            }
        }
        return self::MEMBER_ALREADY;
    }

    // 会員検索実行部分
    public function ifSearchMember($customer) {
        $input = new SearchMemberInput();
        $input->setSiteId($this->_getCommonConfigData('site_id'));
        $input->setSitePass($this->_getCommonConfigData('site_pass'));
        $input->setMemberId($this->gmoMemberID($customer->getId()));
        $exe = new SearchMember();
        $output = $exe->exec($input);
        if( $exe->isExceptionOccured() ){
            $this->_debugLog(self::CODE_CC, '--> Exception Error');
            Mage::log('GMO Exception.', null, self::ERROR_LOG_FILE);
            return false;
        }
        return $output;
    }

    // カード情報取得
    public function searchCard($customer) {
        $this->_debugLog(self::CODE_CC, '- searchCard -');
        if(!$output = $this->ifSearchCard($customer)) {
            return false;
        }else{
            if($output->isErrorOccurred()){
                $errorList = $output->getErrList();
                if(!$this->_chkErrorInfo($errorList, 'E01240002')) {
                    $this->_debugLog(self::CODE_CC, '--> Request return Error');
                    $this->putGmoErrorLog($errorList, 'searchCard');
                    return false;
                }else{
                    return array();
                }
            }
        }
        $cardList = $output->getCardList();
        if($this->_isDebug(self::CODE_CC)) {
            $this->_debugLog(self::CODE_CC, '--> Card Search OK');
            foreach($cardList as $card) {
                $this->_debugLog(self::CODE_CC, '------------------------------');
                $this->_debugLog(self::CODE_CC, 'Card Seq='.$card['CardSeq']);
                $this->_debugLog(self::CODE_CC, 'Card No='.$card['CardNo']);
                $this->_debugLog(self::CODE_CC, 'Expire='.$card['Expire']);
                $this->_debugLog(self::CODE_CC, 'CardName='.$card['CardName']);
                $this->_debugLog(self::CODE_CC, 'DeleteFlag='.$card['DeleteFlag']);
            }
        }
        return $cardList;
    }

    // カード情報取得実行部分
    public function ifSearchCard($customer) {
        $input = new SearchCardInput();
        $input->setSiteId($this->_getCommonConfigData('site_id'));
        $input->setSitePass($this->_getCommonConfigData('site_pass'));
        $input->setMemberId($this->gmoMemberID($customer->getId()));
        $input->setSeqMode(1);
        $exe = new SearchCard();
        $output = $exe->exec($input);
        if($exe->isExceptionOccured()){
            $this->_debugLog(self::CODE_CC, '--> Exception Error');
            Mage::log('GMO Exception.', null, self::ERROR_LOG_FILE);
            return false;
        }
        return $output;
    }

    // カード情報登録
    public function saveCard($customer, $ccNo, $expire, $cardName) {
        $this->_debugLog(self::CODE_CC, '- saveCard -');
        if(!$output = $this->ifSaveCard($customer, $ccNo, $expire, $cardName)) {
            return false;
        }else{
            if($output->isErrorOccurred()){
                $errorList = $output->getErrList();
                $this->_debugLog(self::CODE_CC, '--> Request return Error');
                $this->putGmoErrorLog($errorList, 'SaveCard');
                return false;
            }
        }
        $this->_debugLog(self::CODE_CC, '--> Card Save OK');
        $this->_debugLog(self::CODE_CC, 'Card Seq='.$output->getCardSeq());
        $this->_debugLog(self::CODE_CC, 'Card No='.$output->getCardNo());
        $this->_debugLog(self::CODE_CC, 'Forward='.$output->getForward());
        return $output;
    }

    // カード情報登録実行部分
    public function ifSaveCard($customer, $ccNo, $expire, $cardName) {
        $input = new SaveCardInput();
        $input->setSiteId($this->_getCommonConfigData('site_id'));
        $input->setSitePass($this->_getCommonConfigData('site_pass'));
        $input->setMemberId($this->gmoMemberID($customer->getId()));
        $input->setCardNo($ccNo);
        $input->setExpire($expire);
        $input->setCardName($cardName);
        $input->setSeqMode(0);
        $exe = new SaveCard();
        $output = $exe->exec($input);
        if($exe->isExceptionOccured()){
            $this->_debugLog(self::CODE_CC, '--> Exception Error');
            Mage::log('GMO Exception.', null, self::ERROR_LOG_FILE);
            return false;
        }
        return $output;
    }

    // カード情報削除
    public function deleteCard($customer, $cardId) {
        $this->_debugLog(self::CODE_CC, '- deleteCard -');
        if(!$output = $this->ifDeleteCard($customer, $cardId)) {
            return false;
        }else{
            if($output->isErrorOccurred()){
                $errorList = $output->getErrList();
                $this->_debugLog(self::CODE_CC, '--> Request return Error');
                $this->putGmoErrorLog($errorList, 'DeleteCard');
                return false;
            }
        }
        $this->_debugLog(self::CODE_CC, '--> Card Delete OK');
        return true;
    }

    // カード情報削除実行部分
    public function ifDeleteCard($customer, $cardId) {
        $input = new DeleteCardInput();
        $input->setSiteId($this->_getCommonConfigData('site_id'));
        $input->setSitePass($this->_getCommonConfigData('site_pass'));
        $input->setMemberId($this->gmoMemberID($customer->getId()));
        $input->setCardSeq($cardId);
        $input->setSeqMode(1);
        $exe = new DeleteCard();
        $output = $exe->exec($input);
        if($exe->isExceptionOccured()){
            $this->_debugLog(self::CODE_CC, '--> Exception Error');
            Mage::log('GMO Exception.', null, self::ERROR_LOG_FILE);
            return false;
        }
        return $output;
    }

    // コンビニ支払
    public function entryExecTranCvs($orderId, $amount, $cvsType, $customer) {
        $this->_debugLog(self::CODE_CVS, '- entryExecTranCvs -');
        if(!$output = $this->ifEntryExecTranCvs($orderId, $amount, $cvsType, $customer)) {
            return false;
        }else{
            if($output->isErrorOccurred()){
                $errorList = $this->_getEntryExecErorInfo($output);
                $this->_debugLog(self::CODE_CVS, '--> Request return Error');
                $this->putGmoErrorLog($errorList, 'entryExecTranCvs');
                return false;
            }
        }
        $this->_debugLog(self::CODE_CVS, '--> entryExecTranCvs OK');
        return $output;
    }

    // コンビニ支払実行部分
    public function ifEntryExecTranCvs($orderId, $amount, $cvsType, $customer) {
        $comm_helper = Mage::helper('ecggmo');
        $entryInput = new EntryTranCvsInput();
        $entryInput->setShopId($this->_getCommonConfigData('shop_id'));
        $entryInput->setShopPass($this->_getCommonConfigData('shop_pass'));
        $entryInput->setOrderId($orderId);
        $entryInput->setAmount($comm_helper->getPriceText($amount));
        $execInput = new ExecTranCvsInput();
        $execInput->setOrderId($orderId);
        $execInput->setConvenience($cvsType);
        $execInput->setCustomerName(mb_convert_encoding($customer->getName(), 'SJIS', 'UTF-8'));
        $execInput->setCustomerKana(mb_convert_encoding('　', 'SJIS', 'UTF-8'));
        $execInput->setTelNo(preg_replace('/[\-\s]+/', '', $customer->getTelephone()));
        $execInput->setMailAddress($customer->getEmail());
        $execInput->setReceiptsDisp11(mb_convert_encoding(
            $this->_getPaymentMethodsConfig(self::CODE_CVS, 'inquiries'), 'SJIS', 'UTF-8'));
        $execInput->setReceiptsDisp12($this->_getPaymentMethodsConfig(self::CODE_CVS, 'contact_phone_number'));
        $execInput->setReceiptsDisp13($this->_getPaymentMethodsConfig(self::CODE_CVS, 'reception_hours'));
        $execInput->setPaymentTermDay($this->_getPaymentMethodsConfig(self::CODE_CVS, 'payment_term'));

        $this->_debugLog(self::CODE_CVS, 'Order ID:'.$orderId);
        $this->_debugLog(self::CODE_CVS, 'Amount:'.$comm_helper->getPriceText($amount));
        $this->_debugLog(self::CODE_CVS, 'Convenience:'.$cvsType);
        $this->_debugLog(self::CODE_CVS, 'CustomerName:'.$customer->getName());
        $this->_debugLog(self::CODE_CVS, 'TelNo:'.$customer->getTelephone());
        $this->_debugLog(self::CODE_CVS, 'MailAddress:'.$customer->getEmail());
        $this->_debugLog(self::CODE_CVS, 'ReceiptsDisp11:'.$this->_getPaymentMethodsConfig(self::CODE_CVS, 'inquiries'));
        $this->_debugLog(self::CODE_CVS, 'ReceiptsDisp12:'.$this->_getPaymentMethodsConfig(self::CODE_CVS, 'contact_phone_number'));
        $this->_debugLog(self::CODE_CVS, 'ReceiptsDisp13:'.$this->_getPaymentMethodsConfig(self::CODE_CVS, 'reception_hours'));

        $input = new EntryExecTranCvsInput();
        $input->setEntryTranCvsInput($entryInput);
        $input->setExecTranCvsInput($execInput);
        $exe = new EntryExecTranCvs();
        $output = $exe->exec($input);
        if($exe->isExceptionOccured()){
            $this->_debugLog(self::CODE_CVS, '--> Exception Error');
            Mage::log('GMO Exception.', null, self::ERROR_LOG_FILE);
            return false;
        }
        return $output;
    }

    // 取引紹介
    public function searchTradeMulti($orderId, $payType, $kind) {
        $this->_debugLog($kind, '- searchTradeMulti -');
        if(!$output = $this->ifSearchTradeMulti($orderId, $payType, $kind)) {
            return false;
        }else{
            if($output->isErrorOccurred()){
                $errorList = $output->getErrList();
                $this->_debugLog($kind, '--> Request return Error');
                $this->putGmoErrorLog($errorList, 'searchTradeMulti');
                return false;
            }
        }
        $this->_debugLog($kind, '--> searchTradeMulti OK');
        return $output;
    }

    // 取引紹介実行部分
    public function ifSearchTradeMulti($orderId, $payType, $kind) {
        $input = new SearchTradeMultiInput();
        $input->setShopId($this->_getCommonConfigData('shop_id'));
        $input->setShopPass($this->_getCommonConfigData('shop_pass'));
        $input->setOrderID($orderId);
        $input->setPayType($payType);
        $exe = new SearchTradeMulti();
        $output = $exe->exec($input);
        if($exe->isExceptionOccured()){
            $this->_debugLog($kind, '--> Exception Error');
            Mage::log('GMO Exception.', null, self::ERROR_LOG_FILE);
            return false;
        }
        return $output;
    }

    /**
     * エラーの詳細コードが指定コードかどうかを調べる
     */
    private function _chkErrorInfo($errorList, $error) {
        foreach( $errorList as  $errorInfo ) {
            if($errorInfo->getErrInfo() == $error) {
                return true;
            }
        }
        return false;
    }

    public function putGmoErrorLog($errorList, $func='Payment') {
        foreach( $errorList as  $errorInfo ) {
            Mage::log('('.$func.') Error.', null, self::ERROR_LOG_FILE);
            Mage::log('-- ErrorCode: '.$errorInfo->getErrCode(), null, self::ERROR_LOG_FILE);
            Mage::log('-- ErrorInfo: '.$errorInfo->getErrInfo(), null, self::ERROR_LOG_FILE);
            Mage::log('-- ErrorHandle: '.$this->getMessage($errorInfo->getErrInfo()), null, self::ERROR_LOG_FILE);
        }
    }

    private function _getCommonConfigData($paramName) {
        $comm = Mage::helper('ecggmo');
        return $comm->getCommonConfigData($paramName);
    }

    public function _debugLog($kind, $message) {
        if($this->_isDebug($kind)) {
            Mage::log(self::LOG_HEADER.$message, null, 'payment_'.$kind.'.log');
        }
    }

    public function _debug($kind, $debugData) {
         if($this->_isDebug($kind)) {
             Mage::getModel('core/log_adapter', 'payment_'.$kind.'.log')
                 ->setFilterDataKeys($this->_debugReplacePrivateDataKeys)
                 ->log($debugData);
         }
    }

    public function gmoMemberID($id) {
        $prefix = $this->_getPaymentMethodsConfig(self::CODE_CC, 'member_prefix');
        if(empty($prefix)) {
            return $this->_getCommonConfigData('shop_id').'-'.$id;
        }else{
            return $prefix.'-'.$id;
        }
    }

    public function _isDebug($kind) {
        return $this->_getPaymentMethodsConfig($kind, 'debug');
    }

    public function _getPaymentMethodsConfig($kind, $key) {
        return Mage::getStoreConfig('payment/'.$kind.'/'.$key);
    }

    public $messageMap = array(
            'E00000000'=>'特になし',
            'E01010001'=>'ショップIDが指定されていません。',
            'E01020001'=>'ショップパスワードが指定されていません。',
            'E01030002'=>'指定されたIDとパスワードのショップが存在しません。',
            'E01040001'=>'オーダーIDが指定されていません。',
            'E01040003'=>'オーダーIDが最大文字数を超えています。',
            'E01040010'=>'既にオーダーIDが存在しています。',
            'E01040013'=>'オーダーIDに不正な文字が存在します。',
            'E01050001'=>'処理区分が指定されていません。',
            'E01050002'=>'指定された処理区分は定義されていません。',
            'E01050004'=>'指定した処理区分の処理は実行出来ません。',
            'E01060001'=>'利用金額が指定されていません。',
            'E01060005'=>'利用金額が最大桁数を超えています。',
            'E01060006'=>'利用金額に数字以外の文字が含まれています。',
            'E01070005'=>'税送料が最大桁数を超えています。',
            'E01070006'=>'税送料に数字以外の文字が含まれています。',
            'E01080007'=>'3Dセキュア使用フラグに0,1以外の値が指定されています。',
            'E01090001'=>'取引IDが指定されていません。',
            'E01100001'=>'取引パスワードが指定されていません。',
            'E01110002'=>'指定されたIDとパスワードの取引が存在しません。',
            'E01120008'=>'カード種別の書式が誤っています。',
            'E01130002'=>'指定されたカード略称が存在しません。',
            'E01140007'=>'対応支払方法に0,1以外の値が指定されています。',
            'E01140003'=>'対応支払方法が最大文字数を超えています。',
            'E01150007'=>'対応分割回数に0,1以外の値が指定されています。',
            'E01160007'=>'対応ボーナス分割回数に0,1以外の値が指定されています。',
            'E01170001'=>'カード番号が指定されていません。',
            'E01170003'=>'カード番号が最大文字数を超えています。',
            'E01170006'=>'カード番号に数字以外の文字が含まれています。',
            'E01170011'=>'カード番号が10桁～16桁の範囲ではありません。',
            'E01180001'=>'有効期限が指定されていません。',
            'E01180003'=>'有効期限が4桁ではありません。',
            'E01180006'=>'有効期限に数字以外の文字が含まれています。',
            'E01190001'=>'サイトIDが指定されていません。',
            'E01200001'=>'サイトパスワードが指定されていません。',
            'E01210002'=>'指定されたIDとパスワードのサイトが存在しません。',
            'E01220001'=>'会員IDが指定されていません。',
            'E01230001'=>'カード登録連番が指定されていません。',
            'E01230006'=>'カード登録連番に数字以外の文字が含まれています。',
            'E01230009'=>'カード登録連番が最大登録可能数を超えています。',
            'E01240002'=>'指定されたサイトIDと会員ID、カード連番のカードが存在しません。',
            'E01250010'=>'カードパスワードが一致しません。',
            'E01260001'=>'支払方法が指定されていません。',
            'E01250002'=>'指定された支払方法が存在しません。',
            'E01260010'=>'指定された支払方法はご利用できません。',
            'E01270001'=>'支払回数が指定されていません。',
            'E01270005'=>'支払回数が1～2桁ではありません。',
            'E01270006'=>'支払回数の数字以外の文字が含まれています。',
            'E01270010'=>'指定された支払回数はご利用できません。',
            'E01280012'=>'加盟店URLの値が最大バイト数を超えています。',
            'E01290001'=>'HTTP_ACCEPTが指定されていません。',
            'E01300001'=>'HTTP_USER_AGENTが指定されていません。',
            'E01310001'=>'使用端末が指定されていません。',
            'E01310007'=>'使用端末に0,1以外の値が指定されています。',
            'E01320012'=>'加盟店自由項目1の値が最大バイト数を超えています。',
            'E01330012'=>'加盟店自由項目2の値が最大バイト数を超えています。',
            'E01340012'=>'加盟店自由項目3の値が最大バイト数を超えています。',
            'E01350001'=>'MDが指定されていません。',
            'E01360001'=>'PaREsが指定されていません。',
            'E01370012'=>'3Dセキュア表示店舗名の値が最大バイト数を超えています。',
            'E01380007'=>'決済方法フラグに0,1以外の値が指定されています。',
            'E01390002'=>'指定されたサイトIDと会員IDの組み合わせが存在しません。',
            'E01390010'=>'指定されたサイトIDと会員IDの組み合わせは既に存在しています。',
            'E11010001'=>'この取引は既に決済が終了しています。',
            'E11010002'=>'この取引は決済が終了していませんので、変更する事が出来ません。',
            'E11010003'=>'この取引は指定処理区分処理を行う事が出来ません。',
            'E21010001'=>'3Dセキュア認証に失敗しました。',
            'E21020001'=>'3Dセキュア認証に失敗しました。',
            'E21020002'=>'3Dセキュア認証がキャンセルされました。',
            'E41170002'=>'入力されたカードの会社には対応していません。別のカード番号を入力して下さい。',
            'E41170099'=>'カード番号に誤りがあります。再度確認して入力して下さい。',
            'E90010001'=>'現在処理を行っているのでもうしばらくお待ち下さい。',
            '42G020000'=>'カード残高が不足しているために、決済が完了できませんでした。',
            '42G030000'=>'カード限度額を超えているために、決済が完了できませんでした。',
            '42G040000'=>'カード残高が不足しているために、決済が完了できませんでした。',
            '42G050000'=>'カード限度額を超えているために、決済が完了できませんでした。',
            '42G120000'=>'このカードでは取引をする事が出来ません。',
            '42G220000'=>'このカードでは取引をする事が出来ません。',
            '42G300000'=> '',
            '42G420000'=>'暗証番号が誤っていた為に、決済を完了する事が出来ませんでした。',
            '42G540000'=>'このカードでは取引をする事が出来ません。',
            '42G550000'=>'カード限度額を超えているために、決済が完了できませんでした。',
            '42G560000'=>'',
            '42G600000'=>'このカードでは取引をする事が出来ません。',
            '42G610000'=>'このカードでは取引をする事が出来ません。',
            '42G650000'=>'カード番号に誤りがあるために、決済を完了できませんでした。',
            '42G670000'=>'商品コードに誤りがあるために、決済を完了できませんでした。',
            '42G680000'=>'金額に誤りがあるために、決済を完了できませんでした。',
            '42G690000'=>'税送料に誤りがあるために、決済を完了できませんでした。',
            '42G700000'=>'ボーナス回数に誤りがあるために、決済を完了できませんでした。',
            '42G710000'=>'ボーナス月に誤りがあるために、決済を完了できませんでした。',
            '42G720000'=>'ボーナス額に誤りがあるために、決済を完了できませんでした。',
            '42G730000'=>'支払開始月に誤りがあるために、決済を完了できませんでした。',
            '42G740000'=>'分割回数に誤りがあるために、決済を完了できませんでした。',
            '42G750000'=>'分割金額に誤りがあるために、決済を完了できませんでした。',
            '42G760000'=>'初回金額に誤りがあるために、決済を完了できませんでした。',
            '42G770000'=>'業務区分に誤りがあるために、決済を完了できませんでした。',
            '42G780000'=>'支払区分に誤りがあるために、決済を完了できませんでした。',
            '42G790000'=>'照会区分に誤りがあるために、決済を完了できませんでした。',
            '42G800000'=>'取消区分に誤りがあるために、決済を完了できませんでした。',
            '42G810000'=>'取消取扱区分に誤りがあるために、決済を完了できませんでした。',
            '42G830000'=>'有効期限に誤りがあるために、決済を完了できませんでした。',
            '42G950000'=>'',
            '42G960000'=>'このカードでは取引をする事が出来ません。',
            '42G970000'=>'このカードでは取引をする事が出来ません。',
            '42G980000'=>'このカードでは取引をする事が出来ません。',
            '42G990000'=>'このカードでは取引をする事が出来ません。' ,       
            );

    public function getMessage($errorInfo) {
        if(array_key_exists($errorInfo, $this->messageMap)){
            return $this->messageMap[$errorInfo];
        }
        return '決済処理に失敗しました。';
    }
}

