<?php
class Rack_Getpostcode_Adminhtml_ImportPostCodeController extends Mage_Adminhtml_Controller_Action
{
    protected $_allowDelimiter = array(',', ';');
    
    protected function _initLayout()
    {
        $this->loadLayout();
    }
    
    public function indexAction()
    {
        $this->_initLayout();
        $block = $this->getLayout()->createBlock('getpostcode/adminhtml_update');
        $this->_addContent($block);
        $this->renderLayout();
    }
    
    public function updateAction()
    {
        if (!isset($_FILES['form_file'])) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('data')->__('Upload file not found.'));
            $this->_redirect('*/*/');
            return;
        }
        
        $data = $this->getRequest()->getPost();
        
        if (!in_array($data['csv_delimiter'], $this->_allowDelimiter)) {
            //Mage::log(2);
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('data')->__('CSV delimiter is not valid'));
            $this->_redirect('*/*/');
            return;
        }
        
        try {
            $model = Mage::getModel('getpostcode/postcode');
            $model->import($_FILES['form_file']['tmp_name']);
            $this->_getSession()->addSuccess(Mage::helper('getpostcode')->__('Postcode imported successfully.'));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        
        $this->_redirect('*/*/');
    }
}