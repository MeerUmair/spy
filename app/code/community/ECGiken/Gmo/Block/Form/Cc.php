<?php
class ECGiken_Gmo_Block_Form_Cc extends Mage_Payment_Block_Form_Cc {
    protected $session;
    protected $customerCards;

    protected function _construct() {
        Mage_Payment_Block_Form::_construct();
        $this->setTemplate('ecggmo/form/cc.phtml');
    }

    public function getCustomerCards() {
        if(empty($this->customerCards)) {
            $session = $this->getCustomerSession();
            if($session->isLoggedIn()) {
                $customer = $session->getCustomer();
                $gmo = Mage::helper('ecggmo/gmo');
                $this->customerCards = $gmo->searchCard($customer);
            }
        }
        return $this->customerCards;
    }

    public function customerCardCount() {
        $cards = $this->getCustomerCards();
        $count = 0;
        foreach($cards as $card) if($card['DeleteFlag'] == 0) $count++;
        return $count;
    }

    public function getCustomerCard() {
        $htmlText = '<input type="hidden" name="payment[cc_use_save_card]" value="999999">';
        $session = $this->getCustomerSession();
        if($this->isSaveCard() && $session->isLoggedIn()) {
            $cards = $this->getCustomerCards();
            if(!empty($cards)) {
                $cc = array();
                $htmlText = '<label>'.$this->__('I use the registration card.').'</label><br />';
                $htmlText .= '<ul>';
                foreach($cards as $card) {
                    //$chk = $card['CardNo'].'-'.$card['Expire'];
                    //if(array_key_exists($chk, $cc)) continue;
                    //$cc[$chk] = true;
                    if($card['DeleteFlag'] == 0) {
                        $htmlText .= '<li>';
                        $htmlText .= '<input type="radio" class="radio" name="payment[cc_use_save_card]" value="'.$card['CardSeq'].'-'.$card['CardName'].' / '.$card['CardNo'].' / '.$card['Expire'].'">';
                        $htmlText .= $card['CardName'].' / '.$card['CardNo'].' / '.$card['Expire'];
                        $htmlText .= '&nbsp;&nbsp;<input type="checkbox"  name="payment[cc_delete_card][]" value="'.$card['CardSeq'].'">';
                        $htmlText .= '&nbsp;'.$this->__('Delete');
                        $htmlText .= '</li>';
                    }
                }
                $htmlText .= '<li>';
                $htmlText .= $this->__('Removed check card to delete registration after purchase.');
                $htmlText .= '</li>';
                $htmlText .= '<li>';
                $htmlText .= '<br /><input type="radio" class="radio" name="payment[cc_use_save_card]" value="999999" checked>';
                $htmlText .= $this->__('I use a different card.');
                $htmlText .= '</li>';
                $htmlText .= '</ul>';
            }
        }
        return $htmlText;
    }

    public function getCustomerSession() {
        if(!isset($this->session)) {
            Mage::getSingleton('core/session', array('name'=>'frontend') );
            $this->session = Mage::getSingleton('customer/session', array('name'=>'frontend') );
        }
        return $this->session;
    }

    public function isLoggedIn() {
        return $this->getCustomerSession()->isLoggedIn();
    }

    public function isTest() {
        return Mage::getStoreConfig('payment/ecggmo_cc/test');
    }

    public function isSaveCard() {
        return Mage::getStoreConfig('payment/ecggmo_cc/save_card');
    }
}
