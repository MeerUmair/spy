<?php

class Verite_Japanpost_Adminhtml_Japanpost_LogController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('japanpost/adminhtml_log', 'log');
        $this->_addContent($block);

        $this->renderLayout();
    }

    /**
     * Grid action
     */
    public function gridAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('japanpost/adminhtml_log_grid', 'log');

        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Authorization
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->getSession()->isAllowed('japanpost_shipping/log');
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

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('log_id');
        if (is_array($ids)) {
            $model = Mage::getModel('japanpost/log');
            try {
                $model = Mage::getModel('japanpost/log');
                foreach ($ids as $id) {
                    $model->load($id);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('japanpost')->__('Selected logs were deleted successfully.'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
}