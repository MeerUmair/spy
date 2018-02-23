<?php

class Rack_Jp_Validator_Helper_Data extends Mage_Core_Helper_Abstract {
    const FULL_WIDTH_ONLY = 'jpcore/validator/forcefullwidthname';
    const NAME_LENGTH_KEY = 'jpcore/validator/namelength';
    const ADDRESS_FULL_WIDTH_ONLY = 'jpcore/validator/forcefullwidthaddress';
    const ADDRESS_LENGTH_KEY = 'jpcore/validator/addresslength';
    const TEL_NUMBER_ONLY = 'jpcore/validator/forcedigitsfortel';
    const POST_NUMBER_ONLY = 'jpcore/validator/forcedigitsforpost';
    const SEPARATE_POST = 'jpcore/validator/separatepostcode';
    const SEPARATE_TEL = 'jpcore/validator/separatetel';
    const ADDRESS_CHECK_SEPARATELY= 'jpcore/validator/validateaddresstotallength';

    public function validateName($customer, &$errors) {
        if (!Rack_Jp_Validator_Helper_CharCheck::isFullWidth($customer->getFirstname()) && Mage::getStoreConfig(self::FULL_WIDTH_ONLY)) {
            $customer->setFirstname(Rack_Jp_Validator_Helper_CharConverter::convertToFullWidth($customer->getFirstname()));
        }
        
        $length = (int) Mage::getStoreConfig(self::NAME_LENGTH_KEY);
        
        if ($customer->getFirstname() != null && ($length > 0) && mb_strlen($customer->getFirstname(), 'utf-8') > $length) {
            $errors[] = $this->__('Please enter the First name less than %d characters.', $length);
        }

        // Lastname
        if (!Rack_Jp_Validator_Helper_CharCheck::isFullWidth($customer->getLastname()) && Mage::getStoreConfig(self::FULL_WIDTH_ONLY)) {
            $customer->setLastname(Rack_Jp_Validator_Helper_CharConverter::convertToFullWidth($customer->getLastname()));
        }
        if ($customer->getLastname() != null && ($length > 0) && mb_strlen($customer->getLastname(), 'utf-8') > $length) {
            $errors[] = $this->__('Please enter the Last name less than %d characters.', $length);
        }

    }

    public function validateKana($customer, &$errors) {
        if (Mage::getStoreConfig('jpcore/name/requirekana')) {
            if (!Zend_Validate::is($customer->getLastnamekana(), 'NotEmpty')) {
                $errors[] = $this->__('Please enter the Last name kana.');
            }

            if (!Zend_Validate::is($customer->getFirstnamekana(), 'NotEmpty')) {
                $errors[] = $this->__('Please enter the First name kana.');
            }
        }

        if (Mage::getStoreConfig('jpcore/name/kanaformat')) {
            if ((Zend_Validate::is($customer->getLastnamekana(), 'NotEmpty')) && !Zend_Validate::is($customer->getLastnamekana(), 'Regex', array('/^[ぁ-んー]*$/u'))) {
                $errors[] = $this->__('Please enter the Last name kana Full-width Hiragana.');
            }

            if ((Zend_Validate::is($customer->getFirstnamekana(), 'NotEmpty')) && !Zend_Validate::is($customer->getFirstnamekana(), 'Regex', array('/^[ぁ-んー]*$/u'))) {
                $errors[] = $this->__('Please enter the First name kana Full-width Hiragana.');
            }
        } else {
            if ((Zend_Validate::is($customer->getLastnamekana(), 'NotEmpty')) && !Zend_Validate::is($customer->getLastnamekana(), 'Regex', array('/^[ァ-ヴー]*$/u'))) {
                $errors[] = $this->__('Please enter the Last name kana Full-width Katakana.');
            }

            if ((Zend_Validate::is($customer->getFirstnamekana(), 'NotEmpty')) && !Zend_Validate::is($customer->getFirstnamekana(), 'Regex', array('/^[ァ-ヴー]*$/u'))) {
                $errors[] = $this->__('Please enter the First name kana Full-width Katakana.');
            }

            if (!Rack_Jp_Validator_Helper_CharCheck::isFullWidth($customer->getLastnamekana())) {
                $customer->setLastnamekana(Rack_Jp_Validator_Helper_CharConverter::convertToFullWidth($customer->getLastnamekana()));
            }

            if (!Rack_Jp_Validator_Helper_CharCheck::isFullWidth($customer->getFirstnamekana())) {
                $customer->setFirstnamekana(Rack_Jp_Validator_Helper_CharConverter::convertToFullWidth($customer->getFirstnamekana()));
            }
        }
        
        $length = (int) Mage::getStoreConfig(self::NAME_LENGTH_KEY);

        if ($customer->getLastnamekana() != null && ($length > 0) && mb_strlen($customer->getLastnamekana(), 'utf-8') > $length) {
            $errors[] = $this->__('Please enter the Last name kana less than %d characters.', $length);
        }

        if ($customer->getFirstnamekana() != null && ($length > 0) && mb_strlen($customer->getFirstnamekana(), 'utf-8') > $length) {
            $errors[] = $this->__('Please enter the First name kana less than %d characters.', $length);
        }
    }

    public function validateAddress($address, &$errors) {
        $length = (int) Mage::getStoreConfig(self::ADDRESS_LENGTH_KEY);
        
        if ($address->getCity() != null && ($length > 0) && mb_strlen($address->getCity(), 'utf-8') > $length) {
            $errors[] = $this->__('The Maximum length of field City is %d Characters.', $length);
        }

        if (Mage::getStoreConfig(self::ADDRESS_CHECK_SEPARATELY)) {
            // Street 1
            if ($address->getStreet1() != null && ($length > 0) && mb_strlen($address->getStreet1(), 'utf-8') > $length) {
                $errors[] = $this->__('The Maximum length of field Address1 is %d Characters.', $length);
            }

            if ($address->getStreet2() != null && ($length > 0) && mb_strlen($address->getStreet1(), 'utf-8') > $length) {
                $errors[] = $this->__('The Maximum length of field Address2 is %d Characters.', $length);
            }

            if ($address->getStreet3() != null && ($length > 0) && mb_strlen($address->getStreet1(), 'utf-8') > $length) {
                $errors[] = $this->__('The Maximum length of field Address3 is %d Characters.', $length);
            }

            if ($address->getStreet4() != null && ($length > 0) && mb_strlen($address->getStreet1(), 'utf-8') > $length) {
                $errors[] = $this->__('The Maximum length of field Address4 is %d Characters.', $length);
            }
        } else {
            $_address = $address->getStreet1() . $address->getStreet2() . $address->getStreet3() . $address->getStreet4();
            if ($_address != null && ($length > 0) && mb_strlen($_address, 'utf-8') > $length) {
                $errors[] = $this->__('The Maximum length of field Address is %d Characters.', $length);
            }
        }
    }

    public function validateTel($address, &$errors) {
        $type = $this->_getActionType();

        if ($type == 'billing' || $type == 'shipping') {
            $request = Mage::app()->getRequest()->getParam($type);
        } elseif ($type === 'form') {
            $request = Mage::app()->getRequest();
        } else {
            $request = $address;
        }
        if (Mage::getStoreConfig(self::SEPARATE_TEL) && $type) {
            $phones = array();
            $_error = 0;

            for ($i = 1; $i <= Mage::getStoreConfig('jpcore/validator/numberoftelform'); $i++) {
                $_phone = 'telephone' . $i;

                if ($type == 'billing' || $type == 'shipping') {
                    if (!$request[$_phone]) {
                        $_error++;
                    } else {
                        $phones[] = $request[$_phone];
                    }
                } else {
                    if (!$request->getParam($_phone)) {
                        $_error++;
                    } else {
                        $phones[] = $request->getParam($_phone);
                    }
                }
            }

            if ($_error != 0) {
                $errors[] = $this->__('Please enter telephone');
            } else {
                $length = (Mage::getStoreConfig('jpcore/validator/telmaxlength') + (Mage::getStoreConfig('jpcore/validator/numberoftelform') - 1));
                $phone = join(Mage::getStoreConfig('jpcore/validator/telseparator'), $phones);
                if (!Zend_Validate::is($phone, 'Regex', array(sprintf('/^[0-9\%s]{1,%s}$/', Mage::getStoreConfig('jpcore/validator/telseparator'), $length)))) {
                    $errors[] = $this->__('The length of Telephone is less than %d and only numbers are allowed.', Mage::getStoreConfig('jpcore/validator/telmaxlength'));
                }

                if (Rack_Jp_Validator_Helper_CharCheck::isFullWidth($phone)) {
                    $errors[] = $this->__('Please enter Telephone in halfwidth.');
                }
            }
        } elseif (Mage::getStoreConfig(self::TEL_NUMBER_ONLY)) {
            $pattern = sprintf('/^\d{1,%s}$/', Mage::getStoreConfig('jpcore/validator/telmaxlength'));
            
            if(Mage::getStoreConfig(self::SEPARATE_TEL)){
                $length = (Mage::getStoreConfig('jpcore/validator/telmaxlength') + (Mage::getStoreConfig('jpcore/validator/numberoftelform') - 1));
                $pattern = sprintf('/^[0-9\%s]{1,%s}$/', Mage::getStoreConfig('jpcore/validator/telseparator'), $length);
            } 
            
            if (!Zend_Validate::is($address->getTelephone(), 'Regex', array($pattern))) {
                $errors[] = $this->__('The length of Telephone is less than %d and only numbers are allowed.', Mage::getStoreConfig('jpcore/validator/telmaxlength'));
            }

            if (Rack_Jp_Validator_Helper_CharCheck::isFullWidth($address->getTelephone())) {
                $errors[] = $this->__('Please enter Telephone in halfwidth.');
            }
        } else {
            $length = Mage::getStoreConfig('jpcore/validator/telmaxlength');
            if(Mage::getStoreConfig(self::SEPARATE_TEL)) {
                $length = (Mage::getStoreConfig('jpcore/validator/telmaxlength') + (Mage::getStoreConfig('jpcore/validator/numberoftelform') - 1));
            }
            if (!Zend_Validate::is($address->getTelephone(), 'Regex', array(sprintf('/^[0-9\%s]{1,%s}$/', Mage::getStoreConfig('jpcore/validator/telseparator'), $length)))) {
                $errors[] = $this->__('The length of Telephone is less than %d and only numbers and %s are allowed.', Mage::getStoreConfig('jpcore/validator/telmaxlength'), Mage::getStoreConfig('jpcore/validator/telseparator'));
            }

            if (Rack_Jp_Validator_Helper_CharCheck::isFullWidth($address->getTelephone())) {
                $errors[] = $this->__('Please enter Telephone in halfwidth.');
            }
        }
    }

    public function validateFax($address, &$errors) {
        $type = $this->_getActionType();
        if ($type == 'billing' || $type == 'shipping') {
            $request = Mage::app()->getRequest()->getParam($type);
        } elseif ($type === 'form') {
            $request = Mage::app()->getRequest();
        } else {
            $request = $address;
        }

        if (Mage::getStoreConfig(self::SEPARATE_TEL) && $type) {
            $faxes = array();
            $_error = 0;

            for ($i = 1; $i <= Mage::getStoreConfig('jpcore/validator/numberoftelform'); $i++) {
                $_fax = 'fax' . $i;
                if ($type == 'billing' || $type == 'shipping') {
                    if (!$request[$_fax]) {
                        $_error++;
                    } else {
                        $faxes[] = $request[$_fax];
                    }
                } else {
                    if (!$request->getParam($_fax)) {
                        $_error++;
                    } else {
                        $faxes[] = $request->getParam($_fax);
                    }
                }
            }

            if ($_error > 0 && $_error != Mage::getStoreConfig('jpcore/validator/numberoftelform')) {
                $errors[] = $this->__('Please enter fax');
            } elseif ($_error == 0) {
                $fax = join(Mage::getStoreConfig('jpcore/validator/telseparator'), $faxes);
                $length = (Mage::getStoreConfig('jpcore/validator/telmaxlength') + (Mage::getStoreConfig('jpcore/validator/numberoftelform') - 1));
                if (!Zend_Validate::is($fax, 'Regex', array(sprintf('/^[0-9\%s]{1,%s}$/', Mage::getStoreConfig('jpcore/validator/telseparator'), $length)))) {
                    $errors[] = $this->__('The length of Fax is less than %d and only numbers are allowed.', Mage::getStoreConfig('jpcore/validator/telmaxlength'));
                }

                if (Rack_Jp_Validator_Helper_CharCheck::isFullWidth($fax)) {
                    $errors[] = $this->__('Please enter Fax in halfwidth.');
                }
            }
        } elseif ($address->getFax() && Mage::getStoreConfig(self::TEL_NUMBER_ONLY)) {
            $pattern = sprintf('/^\d{1,%s}$/', Mage::getStoreConfig('jpcore/validator/telmaxlength'));
            
            if(Mage::getStoreConfig(self::SEPARATE_TEL)){
                $length = (Mage::getStoreConfig('jpcore/validator/telmaxlength') + (Mage::getStoreConfig('jpcore/validator/numberoftelform') - 1));
                $pattern = sprintf('/^[0-9\%s]{1,%s}$/', Mage::getStoreConfig('jpcore/validator/telseparator'), $length);
            }            

            if (!Zend_Validate::is($address->getFax(), 'Regex', array($pattern))) {
                $errors[] = $this->__('The length of Fax is less than %d and only numbers are allowed.', Mage::getStoreConfig('jpcore/validator/telmaxlength'));
            }

            if (Rack_Jp_Validator_Helper_CharCheck::isFullWidth($address->getFax())) {
                $errors[] = $this->__('Please enter Fax in halfwidth.');
            }
        } elseif ($address->getFax()) {
            $length = Mage::getStoreConfig('jpcore/validator/telmaxlength');
            if(Mage::getStoreConfig(self::SEPARATE_TEL)) {
                $length = (Mage::getStoreConfig('jpcore/validator/telmaxlength') + (Mage::getStoreConfig('jpcore/validator/numberoftelform') - 1));
            }            
            if (!Zend_Validate::is($address->getFax(), 'Regex', array(sprintf('/^[0-9%s]{1,%s}$/', Mage::getStoreConfig('jpcore/validator/telseparator'), $length)))) {
                $errors[] = $this->__('The length of Fax is less than %d and only numbers and %s are allowed.', Mage::getStoreConfig('jpcore/validator/telmaxlength'), Mage::getStoreConfig('jpcore/validator/telseparator'));
            }

            if (Rack_Jp_Validator_Helper_CharCheck::isFullWidth($address->getFax())) {
                $errors[] = $this->__('Please enter Fax in halfwidth.');
            }
        }
    }

    public function validatePostcode($address, &$errors) {
        $type = $this->_getActionType();
        if ($type == 'billing' || $type == 'shipping') {
            $request = Mage::app()->getRequest()->getParam($type);
        } elseif ($type === 'form') {
            $request = Mage::app()->getRequest();
        } else {
            $request = $address;
        }

        if (Mage::getStoreConfig(self::SEPARATE_POST) && $type) {
            $zips = array();
            $_error = 0;

            for ($i = 1; $i <= Mage::getStoreConfig('jpcore/validator/numberofpostcodeform'); $i++) {
                $_zip = 'postcode' . $i;
                if ($type == 'billing' || $type == 'shipping') {
                    if (!$request[$_zip]) {
                        $_error++;
                    } else {
                        $zips[] = $request[$_zip];
                    }
                } else {
                    if (!$request->getParam($_zip)) {
                        $_error++;
                    } else {
                        $zips[] = $request->getParam($_zip);
                    }
                }
            }

            if ($_error != 0 && $_error != Mage::getStoreConfig('jpcore/validator/numberofpostcodeform')) {
                $errors[] = $this->__('Please enter Zip/Postalcode.');
            } elseif ($_error == 0) {
                $zip = join(Mage::getStoreConfig('jpcore/validator/postcodeseparator'), $zips);

                if (!Zend_Validate::is($zip, 'Regex', array(sprintf('/^\d{3}\%s\d{4}$/', Mage::getStoreConfig('jpcore/validator/postcodeseparator'))))) {
                    $errors[] = $this->__('The length of Zip/Postalcode is %d digits and only numbers are allowed.', Mage::getStoreConfig('jpcore/validator/postmaxlength'));
                }

                if (Rack_Jp_Validator_Helper_CharCheck::isFullWidth($zip)) {
                    $errors[] = $this->__('Please enter Zip/Postalcode in halfwidth.');
                }
            }
        } elseif ($address->getPostcode() && Mage::getStoreConfig(self::POST_NUMBER_ONLY)) {
            $pattern = sprintf('/^[0-9\%s]{1,%d}$/', Mage::getStoreConfig('jpcore/validator/postcodeseparator'), Mage::getStoreConfig('jpcore/validator/postmaxlength'));
            if(Mage::getStoreConfig(self::SEPARATE_POST)){
                $pattern = sprintf('/^\d{3}\%s\d{4}$/', Mage::getStoreConfig('jpcore/validator/postcodeseparator'));
            }

            if (!Zend_Validate::is($address->getPostcode(), 'Regex', array($pattern))) {
                $errors[] = $this->__('The length of Zip/Postalcode is %d digits and only numbers are allowed.', Mage::getStoreConfig('jpcore/validator/postmaxlength'));
            }
            if (Rack_Jp_Validator_Helper_CharCheck::isFullWidth($address->getPostcode())) {
                $errors[] = $this->__('Please enter Zip/Postalcode in halfwidth.');
            }
        } elseif ($address->getPostcode()) {
            $length = Mage::getStoreConfig('jpcore/validator/postmaxlength');
            if(Mage::getStoreConfig(self::SEPARATE_POST)){
                $length = Mage::getStoreConfig('jpcore/validator/postmaxlength') + (Mage::getStoreConfig('jpcore/validator/numberofpostcodeform') - 1);
            }

            if (!Zend_Validate::is($address->getPostcode(), 'Regex', array(sprintf('/^[0-9\%s]{1,%d}$/', Mage::getStoreConfig('jpcore/validator/postcodeseparator'), $length)))) {
                $errors[] = $this->__('The length of Zip/Postalcode is %d digits and only numbers and %s are allowed.', Mage::getStoreConfig('jpcore/validator/postmaxlength'), Mage::getStoreConfig('jpcore/validator/postcodeseparator'));
            }

            if (Rack_Jp_Validator_Helper_CharCheck::isFullWidth($address->getPostcode())) {
                $errors[] = $this->__('Please enter Zip/Postalcode in halfwidth.');
            }
        }
    }

    public function convertStreetToFullWidth($address) {
        $streetArr = array(
            Rack_Jp_Validator_Helper_CharConverter::convertToFullWidth($address->getStreet1()),
            Rack_Jp_Validator_Helper_CharConverter::convertToFullWidth($address->getStreet2()),
            Rack_Jp_Validator_Helper_CharConverter::convertToFullWidth($address->getStreet3()),
            Rack_Jp_Validator_Helper_CharConverter::convertToFullWidth($address->getStreet4())
        );
        $address->setStreet($streetArr);
    }

    public function convertCityToFullWidth($address) {
        $address->setCity(Rack_Jp_Validator_Helper_CharConverter::convertToFullWidth($address->getCity()));
    }

    protected function _getActionType() {
        $actionName = Mage::app()->getRequest()->getActionName();
        $type = '';
        switch ($actionName) {
            case 'saveBilling':
                $type = 'billing';
                break;
            case 'saveShipping':
                $type = 'shipping';
                break;
            case 'formPost':
                $type = 'form';
                break;
        }

        return $type;
    }

}