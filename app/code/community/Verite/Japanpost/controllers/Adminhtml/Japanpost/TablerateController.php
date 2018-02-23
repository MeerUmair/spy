<?php
/**
 * Japanpost table rates controller
 * 
 * @category   Verite
 * @package    Verite_Japanpost
 * @author     
 */
class Verite_Japanpost_Adminhtml_Japanpost_TablerateController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Retrieve shipping table rate helper
     *
     * @return Verite_Japanpost_Helper_Data
     */
    protected function __getHelper()
    {
        return Mage::helper('japanpost');
    }
    /**
     * Get website id
     * 
     * @return integer
     */
    protected function _getWebsiteId()
    {
        return $this->__getHelper()->getWebsiteId();
    }
    /**
     * Set redirect into responce
     *
     * @param   string $path
     * @param   array $arguments
     */
    protected function __redirect($path, $arguments = array())
    {
        return $this->_redirect($path, array_merge(array('website' => $this->_getWebsiteId()), $arguments));
    }
    /**
     * Retrieve table rate model
     *
     * @return Verite_Japanpost_Model_Tablerate
     */
    protected function getModel()
    {
        $model = Mage::getModel('japanpost/tablerate');
        $model->setWebsiteId($this->_getWebsiteId());
        return $model;
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
	/**
     * Check is allowed action
     * 
     * @return bool
     */
    protected function _isAllowed()
    {
        $session = $this->getSession();
        switch ($this->getRequest()->getActionName()) {
            case 'new': case 'save': return $session->isAllowed('sales/shipping/tablerates/save'); break;
            case 'delete': return $session->isAllowed('sales/shipping/tablerates/delete'); break;
            default: return $session->isAllowed('sales/shipping/tablerates'); break;
        }
    }
	/**
     * Init actions
     *
     * @return Verite_Japanpost_Adminhtml_ShippingtablerateController
     */
    protected function _initAction()
    {
        $helper = $this->__getHelper();
        $this->loadLayout()->_setActiveMenu('sales/shipping/tablerates')
            ->_addBreadcrumb($helper->__('Sales'), $helper->__('Sales'))
            ->_addBreadcrumb($helper->__('Shipping'), $helper->__('Shipping'))
            ->_addBreadcrumb($helper->__('Japanpost'), $helper->__('Japanpost'));
        return $this;
    }
    
    /**
     * Index action
     */
    public function indexAction()
    {
        $helper = $this->__getHelper();
        $this->_title($helper->__('Sales'))
            ->_title($helper->__('Shipping'))
            ->_title($this->__('Japanpost'));
        $this->_initAction();
        $this->renderLayout();
    }
    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    /**
     * New action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }
    /**
     * Edit action
     */
    public function editAction()
    {
        $helper = $this->__getHelper();
        $this->_title($helper->__('Sales'))
            ->_title($helper->__('Shipping'))
            ->_title($helper->__('Japanpost'));
        $id = (int) $this->getRequest()->getParam('tablerate_id');
        $model = $this->getModel();
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->_getSession()->addError($helper->__('This rate no longer exists.'));
                $this->__redirect('*/*/');
                return;
            }
        }
        $this->_title($model->getId() ? $model->getTitle() : $helper->__('New Rate'));
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) $model->setData($data);
        Mage::register('japanpost_tablerate', $model);
        $title = $id ? $helper->__('Edit Rate') : $helper->__('New Rate');
        $this->_initAction()->_addBreadcrumb($title, $title);
        $this->renderLayout();
    }
    /**
     * Save action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $helper = $this->__getHelper();
            $model = $this->getModel();
            $session = $this->_getSession();
            if ($id = (int) $this->getRequest()->getParam('tablerate_id')) $model->load($id);
            $model->setData($data);
            Mage::dispatchEvent('japanpost_tablerate_prepare_save', array('model' => $model, 'request' => $this->getRequest()));
            try {
                $model->save();
                $session->addSuccess($helper->__('The rate has been saved.'));
                $session->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->__redirect('*/*/edit', array('tablerate_id' => $model->getId(), '_current' => true));
                    return;
                }
                $this->__redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, $helper->__('An error occurred while saving the rate.'));
            }
            $session->setFormData($data);
            $this->__redirect('*/*/edit', array('tablerate_id' => $this->getRequest()->getParam('tablerate_id')));
            return;
        }
        $this->__redirect('*/*/');
    }
    /**
     * Delete action
     */
    public function deleteAction()
    {
        $helper = $this->__getHelper();
        if ($id = (int) $this->getRequest()->getParam('tablerate_id')) {
            $title = '';
            try {
                $model = $this->getModel();
                $model->load($id);
                $title = $model->getTitle();
                $model->delete();
                $this->_getSession()->addSuccess($helper->__('The rate has been deleted.'));
                Mage::dispatchEvent('japanpost_tablerate_on_delete', array('title' => $title, 'status' => 'success'));
                $this->__redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::dispatchEvent('japanpost_tablerate_on_delete', array('title' => $title, 'status' => 'fail'));
                $this->_getSession()->addError($e->getMessage());
                $this->__redirect('*/*/edit', array('tablerate_id' => $id));
                return;
            }
        }
        $this->_getSession()->addError($helper->__('Unable to find a rate to delete.'));
        $this->__redirect('*/*/');
    }
    /**
     * Mass delete action
     */
    public function massDeleteAction()
    {
        $helper = $this->__getHelper();
        $tablerateIds = $this->getRequest()->getParam('tablerate_id');
        if (is_array($tablerateIds)) {
            if (!empty($tablerateIds)) {
                try {
                    foreach ($tablerateIds as $tablerateId) {
                        $model = $this->getModel();
                        $model->load($tablerateId);
                        $title = $model->getTitle();
                        $model->delete();
                        Mage::dispatchEvent('japanpost_tablerate_on_delete', array('title' => $title, 'status' => 'success'));
                    }
                    $this->_getSession()->addSuccess(
                        $helper->__('Total of %d record(s) have been deleted.', count($tablerateIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        } else $this->_getSession()->addError($helper->__('Please select rate(s).'));
        $this->__redirect('*/*/index');
    }
    /**
     * Export rates grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'japanpost_shipping_table_rates.csv';
        $content    = $this->getLayout()->createBlock('japanpost/adminhtml_tablerate_grid')->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    /**
     * Export rates grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'japanpost_shipping_table_rates.xml';
        $content    = $this->getLayout()->createBlock('japanpost/adminhtml_tablerate_grid')->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }
}