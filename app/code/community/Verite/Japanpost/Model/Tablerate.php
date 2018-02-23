<?php
/**
 * Table rate model
 *
 * @category   Verite
 * @package    Verite_Japanpost
 * @author     
 */
class Verite_Japanpost_Model_Tablerate extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     * 
     * @var string
     */
    protected $_eventPrefix = 'japanpost_tablerate';
    /**
     * Parameter name in event
     * 
     * In observe method you can use $observer->getEvent()->getItem() in this case
     * 
     * @var string
     */
    protected $_eventObject = 'tablerate';
    /**
     * Model cache tag for clear cache in after save and after delete
     * 
     * When you use true - all cache will be clean
     * 
     * @var string || true
     */
    protected $_cacheTag = 'japanpost_tablerate';
    /**
     * Table rates
     * 
     * @var array
     */
    protected $_tablerates;
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('japanpost/tablerate');
    }
    /**
     * Retrieve japanpost shipping table rate helper
     *
     * @return Verite_Japanpost_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('japanpost');
    }
    /**
     * Get table rates
     * 
     * @return array
     */
    public function getTablerates()
    {
        if (is_null($this->_tablerates)) {
            $this->_tablerates = array();
            foreach ($this->getCollection() as $tablerate) {
                $this->_tablerates[$tablerate->getId()] = $tablerate;
            }
        }
        return $this->_tablerates;
    }
    /**
     * Retrieve table rate by id
     * 
     * @param int $tablerateId
     * @return Verite_Japanpost_Model_Tablerate
     */
    public function getTablerateById($tablerateId)
    {
        $tablerates = $this->getTablerates();
        if (isset($tablerates[$tablerateId])) return $tablerates[$tablerateId];
        else return null;
    }
    /**
     * Create filter chain
     * 
     * @return Zend_Filter
     */
    protected function createFilterChain()
    {
        return new Zend_Filter();
    }
	/**
     * Create validator chain
     * 
     * @return Zend_Validate
     */
    protected function createValidatorChain()
    {
        return new Zend_Validate();
    }
    /**
     * Get text filter
     * 
     * @return Zend_Filter
     */
    protected function getTextFilter()
    {
        return $this->createFilterChain()
                ->appendFilter(new Zend_Filter_StringTrim())
                ->appendFilter(new Zend_Filter_StripNewlines())
                ->appendFilter(new Zend_Filter_StripTags());
    }
    /**
     * Filter float
     * 
     * @param mixed $value
     * @return float
     */
    public function filterFloat($value)
    {
        return (float) ((string) $value);
    }
    /**
     * Filter destination country
     * 
     * @param mixed $value
     * @return string
     */
    public function filterDestCountry($value)
    {
        $helper = $this->getHelper();
        if ($value && ($value != '*')) {
            $countriesISO2Codes = $helper->getCountriesISO2Codes();
            $countriesISO3Codes = $helper->getCountriesISO3Codes();
            if (isset($countriesISO2Codes[$value])) {
                $value = $value;
            } else if (in_array($value, $countriesISO2Codes)) {
                $value = array_search($value, $countriesISO2Codes);
            } elseif (in_array($value, $countriesISO3Codes)) {
                $value = array_search($value, $countriesISO3Codes);
            } else $value = '0';
        } else $value = '0';
        return $value;
    }
    /**
     * Filter destination region
     * 
     * @param mixed $value
     * @return string
     */
    public function filterDestRegion($value)
    {
        $helper = $this->getHelper();
        $countryId = $this->filterDestCountry($this->getData('dest_country_id'));
        if ($countryId && $value && ($value != '*')) {
            $regionsCodes = $helper->getRegionsCodes($countryId);
            if (is_array($regionsCodes)) {
                if (isset($regionsCodes[$value])) {
                    $value = $value;
                } else if (in_array($value, $regionsCodes)) {
                    $value = array_search($value, $regionsCodes);
                } else $value = '0';
            } else $value = '0';
        } else $value = '0';
        return $value;
    }
    /**
     * Filter destination zip
     * 
     * @param mixed $value
     * @return string
     */
    public function filterDestZip($value)
    {
        return ($value == '' || $value == '*') ? '' : $value;
    }
    /**
     * Filter condition name
     * 
     * @param mixed $value
     * @return string
     */
    public function filterConditionName($value)
    {
        $values = Mage::getSingleton('shipping/carrier_tablerate')->getCode('condition_name');
        return (isset($values[$value])) ? $value : null;
    }
    /**
     * Get destination country filter
     * 
     * @return Zend_Filter
     */
    protected function getDestCountryFilter()
    {
        return $this->getTextFilter()->appendFilter(new Zend_Filter_Callback(array(
            'callback' => array($this, 'filterDestCountry'), 
        )));
    }
    /**
     * Get destination region filter
     * 
     * @return Zend_Filter
     */
    protected function getDestRegionFilter()
    {
        return $this->getTextFilter()->appendFilter(new Zend_Filter_Callback(array(
            'callback' => array($this, 'filterDestRegion'), 
        )));
    }
    /**
     * Get destination zip filter
     * 
     * @return Zend_Filter
     */
    protected function getDestZipFilter()
    {
        return $this->getTextFilter()->appendFilter(new Zend_Filter_Callback(array(
            'callback' => array($this, 'filterDestZip'), 
        )));
    }
    /**
     * Get condition name filter
     * 
     * @return Zend_Filter
     */
    protected function getConditionNameFilter()
    {
        return $this->getTextFilter()->appendFilter(new Zend_Filter_Callback(array(
            'callback' => array($this, 'filterConditionName'), 
        )));
    }
    /**
     * Get condition value filter
     * 
     * @return Zend_Filter
     */
    protected function getConditionValueFilter()
    {
        return $this->getTextFilter()->appendFilter(new Zend_Filter_Callback(array(
            'callback' => array($this, 'filterFloat'), 
        )));
    }
    /**
     * Get price filter
     * 
     * @return Zend_Filter
     */
    protected function getPriceFilter()
    {
        return $this->getTextFilter()->appendFilter(new Zend_Filter_Callback(array(
            'callback' => array($this, 'filterFloat'), 
        )));
    }
    /**
     * Get cost filter
     * 
     * @return Zend_Filter
     */
    protected function getCostFilter()
    {
        return $this->getTextFilter()->appendFilter(new Zend_Filter_Callback(array(
            'callback' => array($this, 'filterFloat'), 
        )));
    }
    /**
     * Filter table rate
     *
     * @throws Mage_Core_Exception
     * @return Verite_Japanpost_Model_Tablerate
     */
    public function filter()
    {
        $filters = array(
            'dest_country_id'     => $this->getDestCountryFilter(), 
            'dest_region_id'      => $this->getDestRegionFilter(), 
            'dest_zip'            => $this->getDestZipFilter(), 
            'condition_name'      => $this->getConditionNameFilter(), 
            'condition_value'     => $this->getConditionValueFilter(), 
            'price'               => $this->getPriceFilter(), 
            'cost'                => $this->getCostFilter(), 
        );
        foreach ($filters as $field => $filter) {
            $this->setData($field, $filter->filter($this->getData($field)));
        }
        return $this;
    }
    /**
     * Validate range
     * 
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     * @return boolean
     */
    public function validateRange($value, $min = null, $max = null)
    {
        if ((strval($value) !== '')) {
            if (!is_null($min)) {
                if ($value < $min) return false; 
            }
            if (!is_null($max)) {
                if ($value > $max) return false; 
            }
        }
        return true;
    }
    /**
     * Get text validator
     * 
     * @param boolean $isRequired
     * @param int $minLength
     * @param int $maxLength
     * @return Zend_Validate
     */
    protected function getTextValidator($isRequired = false, $minLength = null, $maxLength = null)
    {
        $validator = $this->createValidatorChain();
        if ($isRequired) $validator->addValidator(new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::STRING), true);
        if (!is_null($minLength) || !is_null($maxLength)) {
            $options = array();
            if (!is_null($minLength)) $options['min'] = $minLength;
            if (!is_null($maxLength)) $options['max'] = $maxLength;
            $validator->addValidator(new Zend_Validate_StringLength($options), true);
        }
        return $validator;
    }
    /**
     * Get integer validator
     * 
     * @param boolean $isRequired
     * @param int $min
     * @param int $max
     * @return Zend_Validate
     */
    protected function getIntegerFilter($isRequired = false, $min = null, $max = null)
    {
        $validator = $this->createValidatorChain();
        if ($isRequired) $validator->addValidator(new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::INTEGER), true);
        $validator->addValidator(new Zend_Validate_Int(), true);
        if (!is_null($min) || !is_null($max)) {
            $validator->addValidator(new Zend_Validate_Callback(array(
                'callback' => array($this, 'validateRange'), 'options' => array($min, $max), 
            )), true);
        }
        return $validator;
    }
    /**
     * Get float validator
     * 
     * @param boolean $isRequired
     * @param int $min
     * @param int $max
     * @return Zend_Validate
     */
    protected function getFloatFilter($isRequired = false, $min = null, $max = null)
    {
        $validator = $this->createValidatorChain();
        if ($isRequired) $validator->addValidator(new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::FLOAT), true);
        $validator->addValidator(new Zend_Validate_Float(), true);
        if (!is_null($min) || !is_null($max)) {
            $validator->addValidator(new Zend_Validate_Callback(array(
                'callback' => array($this, 'validateRange'), 'options' => array($min, $max), 
            )), true);
        }
        return $validator;
    }
	/**
     * Validate catalog inventory stock
     *
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function validate()
    {
        $helper = $this->getHelper();
        $validators = array(
            'dest_country_id'     => $this->getTextValidator(false, 0, 4), 
            'dest_region_id'      => $this->getIntegerFilter(false, 0), 
            'dest_zip'            => $this->getTextValidator(false, 0, 10), 
            'condition_name'      => $this->getTextValidator(true, 0, 20), 
            'condition_value'     => $this->getFloatFilter(false, 0), 
            'price'               => $this->getFloatFilter(false, 0), 
            'cost'                => $this->getFloatFilter(false, 0, 5), 
        );
        $errorMessages = array();
        foreach ($validators as $field => $validator) {
            if (!$validator->isValid($this->getData($field))) {
                $errorMessages = array_merge($errorMessages, $validator->getMessages());
            }
        }
        if (!count($errorMessages)) {
            $tablerate = Mage::getModel('japanpost/tablerate')->loadByRequest($this);
            if ($tablerate->getId()) {
                array_push($errorMessages, $helper->__('Duplicate rate.'));
            }
        }
        if (count($errorMessages)) Mage::throwException(join("\n", $errorMessages));
        return true;
    }
	/**
     * Processing object before save data
     *
     * @return Verite_Japanpost_Model_Tablerate
     */
    protected function _beforeSave()
    {
        $this->filter();
        $this->validate();
        return parent::_beforeSave();
    }
    /**
     * Get title
     * 
     * @return string
     */
    public function getTitle()
    {
        $helper = $this->getHelper();
        $title = null;
        $country = $region = null;
        if ($this->getDestRegionId()) $region = $helper->getRegion($this->getDestRegionId());
        if ($this->getDestCountryId()) $country = $helper->getCountry($this->getDestCountryId());
        $zip = $this->getDestZip();
        $conditionNames = Mage::getSingleton('shipping/carrier_tablerate')->getCode('condition_name');
        $conditionName = $this->getConditionName();
        $conditionName = (isset($conditionNames[$conditionName])) ? $conditionNames[$conditionName] : '';
        $conditionValue = $this->getConditionValue();
        $title = implode(', ', array(
            (($region) ? $region->getName() : '*'), 
            (($zip) ? $zip : '*'), 
            (($country) ? $country->getName() : '*'), 
            (($conditionName) ? $conditionName : ''), 
            (($conditionValue) ? floatval($conditionValue) : '0'), 
        ));
        return $title;
    }
    /**
     * Load table rate by request
     * 
     * @param Varien_Object $request
     * @return Verite_Japanpost_Model_Tablerate
     */
    public function loadByRequest(Varien_Object $request)
    {
        $this->_getResource()->loadByRequest($this, $request);
        $this->setOrigData();
        return $this;
    }
}