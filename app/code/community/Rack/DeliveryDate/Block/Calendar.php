<?php

class Rack_DeliveryDate_Block_Calendar extends Mage_Core_Block_Template
{
    protected $_template = 'rack_dd/calendar.phtml';

    public function getQuote()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Convert selected delivery date to int style value.
     * @return string
     */
    public function getSelectedDate()
    {
        $date = $this->getQuote()->getDeliveryDate();

        return Mage::helper('deliverydate')->convertDateToIntStyle($date);
    }

    public function getDisabledDates()
    {
        $dateModel = Mage::getSingleton('deliverydate/date');

        return $dateModel->getDisableDeliveryDates();
    }
}