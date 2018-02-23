<?php


class Verite_Japanpost_Adminhtml_Japanpost_ExportController extends Mage_Adminhtml_Controller_Action
{
//    public function indexAction()
//    {
//        $this->loadLayout();
//        $block = $this->getLayout()->createBlock('japanpost/adminhtml_export', 'export');
//        $this->_addContent($block);
//
//        $this->renderLayout();
//    }

    /**
     * Execute export and return result to browser via json format
     */
    public function exportAction()
    {
        $exporter = new Verite_Japanpost_Model_Export();
        $ids = $this->getRequest()->getParam('order_ids', array());
        $data = $exporter->export($ids);
        if ($data !== false) {
            $filename = 'japanpost_order_' . Mage::app()->getLocale()->date()->toString('yyyyMMddHHmmss') . '.csv';
            $this->_prepareDownloadResponse($filename, $data, 'text/csv');
        } else {
            $this->_getSession()->addError($this->__('Error export failed, please check log data for detail.'));
            $this->_redirect('*/japanpost_log', array('default' => 'export'));
        }
    }

    /**
     * Download action
     *
     * @return $this|Mage_Core_Controller_Varien_Action
     */
    public function downloadAction()
    {
        $filename = $this->getRequest()->getParam('filename', false);
        if (!$filename) {
            $this->_getSession()->addError($this->__('Invalid filename.'));
            return $this->_redirect('*/*');
        }

        $exporter = new Verite_Japanpost_Model_Export();
        $filePath = $exporter->getFilePath($filename);

        if (!file_exists($filePath)) {
            $this->_getSession()->addError($this->__('Export file not found.'));
            return $this->_redirect('*/*');
        }

        $this->_prepareDownloadResponse($filename, file_get_contents($filePath), 'text/csv');
    }

    /**
     * Grid action
     */
    public function gridAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('japanpost/adminhtml_export_grid', 'export_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Authorization
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->getSession()->isAllowed('japanpost_shipping/export');
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