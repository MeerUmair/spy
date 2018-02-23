<?php

require_once 'Mage/Adminhtml/controllers/Sales/OrderController.php';
class Rack_Pdf_OrderController extends Mage_Adminhtml_Sales_OrderController
{

    
    /**
     * Print order
     * 
     */
	public function printAction(){
        $order = $this->_initOrder();
        if (!empty($order)) {
			$order->setOrder($order);
            $pdf = Mage::getModel('pdf/order_pdf_order')->getPdf(array($order));
            return $this->_prepareDownloadResponse('order'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
    
    public function massOrderPrintAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                $order->setOrder($order);

                if (!isset($pdf)){
                    $pdf = Mage::getModel('pdf/order_pdf_order')->getPdf(array($order));
                } else {
                    $pages =Mage::getModel('pdf/order_pdf_order')->getPdf(array($order));
                    $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                }
            }
            
            $flag = true;
            
            if ($flag) {
                return $this->_prepareDownloadResponse('order'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }
    
}