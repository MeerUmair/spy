<?php

class Rack_DeliveryDate_Adminhtml_DeliverydateController extends Mage_Adminhtml_Controller_Action
{
    public function updateAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $res = array(
            'success' => false,
            'message' => '',
            'deliveryDate' => null
        );
        if ($orderId) {
            $deliveryDate = $this->getRequest()->getParam('delivery_date');
            $deliveryTime = $this->getRequest()->getParam('delivery_time');
            $deliveryDateIntStyle = Mage::helper('deliverydate')->convertDateToIntStyle($deliveryDate);
            try {
                $model = Mage::getModel('deliverydate/delivery');
                $result = $model->updateOrder($orderId, $deliveryDate, $deliveryTime);

                if ($result === true) {
                    $res['success'] = true;
                    $res['message'] = $this->__('Update delivery date and time successful.');
                    $res['deliveryDate'] = $deliveryDateIntStyle;
                } else {
                    $res['message'] = $result;
                }
            } catch (Exception $e) {
                $res['message'] = $e->getMessage();
            }
        } else {
            $res['message'] = $this->__('Invalid data.');
        }

        $this->getResponse()->setBody(json_encode($res));
    }
}