<?php
class Rack_EventCalendar_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{

    public function preDispatch()
    {
        parent::preDispatch();
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('calendar');
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
             ->renderLayout();
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        if (!preg_match('/^[1-9][0-9]*$/', $id)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('calendar')->__('Rule does not exist'));
            $this->_redirect('*/*/');
        }
        
        $model = Mage::getModel('calendar/calendar')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('calendar_data', $model);
            $this->_initAction();
            $this->_addContent($this->getLayout()->createBlock('calendar/adminhtml_calendar_edit'))
                 ->_addLeft($this->getLayout()->createBlock('calendar/adminhtml_calendar_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('calendar')->__('Rule does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('calendar/rule');
            $model->setData($data)
                  ->setId($this->getRequest()->getParam('id'));
        }

        $this->_redirect('*/*/');
    }

}
