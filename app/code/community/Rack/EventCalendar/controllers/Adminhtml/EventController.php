<?php
class Rack_EventCalendar_Adminhtml_EventController extends Mage_Adminhtml_Controller_Action
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

    public function newAction()
    {
        $this->_forward('edit');
    } 

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('calendar/calendar');
            $model->setData($data)
                  ->setId($this->getRequest()->getParam('id'));
        }

        $format = Mage::app()->getLocale()->getDateFormat(
                        Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
                    );
        if (!empty($data['day'])) {
            $date = Mage::app()->getLocale()->date($data['day'], $format);
            $time = $date->getTimestamp();
            $model->setDate(
                            Mage::getSingleton('core/date')->date(null, $time)
                            );
        }

        try {
            $model->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('calendar')->__('Day was successfully saved'));
            Mage::getSingleton('adminhtml/session')->setFormData(false);

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }

            $this->_redirect('*/*/');
            return;
            } 
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }

            $this->_redirect('*/*/');

    }

    public function initAction() {
        Mage::getModel('calendar/observer')->deleteAll();
        Mage::getModel('calendar/observer')->install();
        $this->_redirect('*/*/');
    }
    
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getPost('calendar');
        if ($ids && is_array($ids)) {
            try {
                $model = Mage::getModel('calendar/calendar');
                foreach ($ids as $id) {
                    $model->setId($id);
                    $model->delete();
                }
                
                $this->_getSession()->addSuccess($this->__('Days were successfully deleted.'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                Mage::logException($e);
            }
        }
        
        $this->_redirect('*/*');
    }
    
    public function massStatusAction()
    {
        $ids = $this->getRequest()->getPost('calendar');
        if ($ids && is_array($ids)) {
            $status = $this->getRequest()->getPost('status');
            try {
                $model = Mage::getModel('calendar/calendar');
                foreach ($ids as $id) {
                    $model->load($id);
                    $model->setStatus($status);
                    $model->save();
                }
                
                $this->_getSession()->addSuccess($this->__('Days status were successfully changed.'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                Mage::logException($e);
            }
        }
        
        $this->_redirect('*/*');
    }
}
