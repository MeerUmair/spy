<?php

class Rack_Getpostcode_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Get post code data action
     */
    public function indexAction()
    {
        $postcode = $this->getRequest()->getParam('postcode');
        $postcode = str_replace('-', '', $postcode);
        $postcode = Mage::getModel('getpostcode/postcode')->load($postcode, 'post_code');
        
        echo $postcode->toJson();
    }
}