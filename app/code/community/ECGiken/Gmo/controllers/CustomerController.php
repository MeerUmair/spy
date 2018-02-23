<?php
include_once("Mage/Adminhtml/controllers/CustomerController.php");
class ECGiken_Gmo_CustomerController extends Mage_Adminhtml_CustomerController
{

    public function deleteAction() {
        if(!Mage::getStoreConfig('payment/ecggmo_cc/delete_card')) {
            parent::deleteAction();
        }else{
            $gmo = Mage::helper('ecggmo/gmo');
            $gmo->_debugLog(ECGiken_Gmo_Helper_Gmo::CODE_CC, '- Delete Customer -');
            $this->_initCustomer();
            $customer = Mage::registry('current_customer');
            if ($customer->getId()) {
                try {
                    $customer->load($customer->getId());
                    if(!$ret = $gmo->isMemberAlready($customer)) Mage::throwException(Mage::helper('ecggmo')->__('GMO: Delete card error.'));
                    if($ret == ECGiken_Gmo_Helper_Gmo::MEMBER_ALREADY) {
                        $cards = $gmo->searchCard($customer);
                        foreach($cards as $card) {
                            if($card['DeleteFlag'] == 0) {
                                $gmo->_debugLog(ECGiken_Gmo_Helper_Gmo::CODE_CC, 'Delete Seq='.$card['CardSeq']);
                                $gmo->_debugLog(ECGiken_Gmo_Helper_Gmo::CODE_CC, '       CardNo='.$card['CardNo']);
                                $gmo->_debugLog(ECGiken_Gmo_Helper_Gmo::CODE_CC, '       Expire='.$card['Expire']);
                                if(!$gmo->deleteCard($customer, $card['CardSeq'])) {
                                    Mage::throwException(Mage::helper('ecggmo')->__('GMO: Delete card error.'));
                                }
                            }
                        }
                    }
                    $customer->delete();
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The customer has been deleted.'));
                }
                catch (Exception $e){
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
            $this->_redirect('*/customer');
        }
    }
}

