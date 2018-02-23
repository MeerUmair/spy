<?php

class Rack_Getpostcode_Adminhtml_ManagePostcodeController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout();

        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $block = $this->getLayout()->createBlock('getpostcode/adminhtml_getpostcode', 'postcode_list');
        $this->_addContent($block);
        $this->renderLayout();
    }

    /**
     * Edit post code action 
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('getpostcode/postcode')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('getpostcode_data', $model);

            $this->loadLayout();

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('getpostcode/adminhtml_getpostcode_edit'))
                ->_addLeft($this->getLayout()->createBlock('getpostcode/adminhtml_getpostcode_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('getpostcode')->__('Postcode does not exists'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Create new post code action 
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Save post code action 
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {

            $model = Mage::getModel('getpostcode/postcode');
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                        ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('getpostcode')->__('Postcode was saved successfully.'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('getpostcode')->__('Unable to find postcode to save.'));
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('postcode_id');
        if (is_array($ids)) {
            $model = Mage::getModel('getpostcode/postcode');
            try {
                $model = Mage::getModel('getpostcode/postcode');
                foreach ($ids as $id) {
                    $model->load($id);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('getpostcode')->__('Postcode was delete successfully.'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function gridAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('getpostcode/adminhtml_getpostcode_grid', 'adminhtml_getpostcode_grid');

        $this->getResponse()->setBody($block->toHtml());
    }

}