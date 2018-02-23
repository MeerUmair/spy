<?php

class Rack_Jp_Validator_Model_Observer extends Varien_Object {

    public function modifyPostData($observer) {
        $actionName = $observer->getControllerAction()->getRequest()->getActionName();
        $type = '';
        switch ($actionName) {
            case 'saveBilling':
                $type = 'billing';
                break;
            case 'saveShipping':
                $type = 'shipping';
                break;
            case 'formPost':
            case 'createpost':
                $type = '';
                break;
        }

        if (Mage::getStoreConfig(Rack_Jp_Validator_Helper_Data::SEPARATE_POST)) {
            $this->concatPost($observer->getControllerAction()->getRequest(), $type);
        }

        if (Mage::getStoreConfig(Rack_Jp_Validator_Helper_Data::SEPARATE_TEL)) {
            $this->concatTelFax($observer->getControllerAction()->getRequest(), $type);
        }
    }

    protected function concatPost($request, $type) {
        $_paramBase = 'postcode%s';
        $_request = null;
        if ($type) {
            $_request = $request->getParam($type);
        }

        $separator = Mage::getStoreConfig('jpcore/validator/postcodeseparator');
        $postcodes = array();
        for ($i = 1; $i <= Mage::getStoreConfig('jpcore/validator/numberofpostcodeform'); $i++) {
            $_param = sprintf($_paramBase, $i);
            if ($_request) {
                $postcodes[] = $_request[$_param];
            } else {
                $postcodes[] = $request->getParam($_param);
            }
        }

        if ($type == 'billing' || $type == 'shipping') {
            $_request['postcode'] = join($separator, $postcodes);
            $request->setPost($type, $_request);
        } else {
            $request->setPost(sprintf($_paramBase, ''), join($separator, $postcodes));
        }
    }

    protected function concatTelFax($request, $type) {
        $_paramBaseTel = 'telephone%s';
        $_paramBaseFax = 'fax%s';
        $_request = null;
        if ($type) {
            $_request = $request->getParam($type);
        }
        $separator = Mage::getStoreConfig('jpcore/validator/telseparator');

        $phones = array();
        $faxes = array();
        $_nofax = 0;
        for ($i = 1; $i <= Mage::getStoreConfig('jpcore/validator/numberoftelform'); $i++) {
            $_phone = sprintf($_paramBaseTel, $i);
            $_fax = sprintf($_paramBaseFax, $i);

            if ($_request) {
                $phones[] = $_request[$_phone];
                if (!$_request[$_fax]) {
                    $_nofax++;
                } else {
                    $faxes[] = $_request[$_fax];
                }
            } else {
                $phones[] = $request->getParam($_phone);
                if (!$request->getParam($_fax)) {
                    $_nofax++;
                } else {
                    $faxes[] = $request->getParam($_fax);
                }
            }
        }

        if ($type == 'billing' || $type == 'shipping') {
            $_request['telephone'] = join($separator, $phones);
            if ($_nofax == 0 || $_nofax == Mage::getStoreConfig('jpcore/validator/numberoftelform')) {
                $_request['fax'] = join($separator, $faxes);
            }
            $request->setPost($type, $_request);
        } else {
            $request->setPost(sprintf($_paramBaseTel, ''), join($separator, $phones));
            if ($_nofax == 0 || $_nofax == Mage::getStoreConfig('jpcore/validator/numberoftelform')) {
                $request->setPost(sprintf($_paramBaseFax, ''), join($separator, $faxes));
            }
        }
    }

}