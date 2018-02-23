<?php
class Verite_Japanpost_Adminhtml_Japanpost_ImportController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function importAction()
    {
        try {
            $uploader = new Varien_File_Uploader('file');
            $uploader->setAllowedExtensions(array('csv'));
            $result = $uploader->save(Mage::getBaseDir('var') . DS . 'import' . DS . 'japanpost' . DS);
            $file = $result['path'] . $result['file'];

            /* @var $importer Verite_Japanpost_Model_Import */
            $importer = Mage::getModel('japanpost/import');
            $importer->setFile($file);
            $importer->setSendInvoiceEmail($this->getRequest()->getParam('invoice_mail'), false);
            $importer->setSendShippingEmail($this->getRequest()->getParam('shipping_mail'), false);
            $importer->import();

            $this->_getSession()->addSuccess($this->__('Import completed. Please check result log for more information.'));
            return $this->_redirect('*/japanpost_log', array('default' => 'import'));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::logException($e);
            $this->_redirect('*/*/index');
        }
    }

    protected function _isAllowed()
    {
        return $this->getSession()->isAllowed('japanpost_shipping/import');
    }

    /**
     * Retrieve admin session
     *
     * @return Mage_Admin_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('admin/session');
    }
}