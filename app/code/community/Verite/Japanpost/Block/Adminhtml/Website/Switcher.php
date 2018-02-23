<?php
/**
 * Website switcher block
 * 
 * @category   Verite
 * @package    Verite_Japanpost
 * @author     
 */
class Verite_Japanpost_Block_Adminhtml_Website_Switcher extends Mage_Adminhtml_Block_Template
{
    /**
     * Website
     * 
     * @var Mage_Core_Model_Website
     */
    protected $_website;
    /**
     * Website variable name
     * 
     * @var string
     */
    protected $_websiteVarName = 'website';
    /**
     * Whether has default option or not
     * 
     * @var boolean
     */
    protected $_hasDefaultOption = false;
    /**
     * Retrieve japanpost shipping table rate helper
     *
     * @return Verite_Japanpost_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('japanpost');
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $helper = $this->_getHelper();
        parent::__construct();
        $this->setTemplate('verite_japanpost/website/switcher.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultWebsiteName($helper->__('All Websites'));
    }
    /**
     * Get websites
     *
     * @return array
     */
    public function getWebsites()
    {
        return $this->_getHelper()->getWebsites();
    }
    /**
     * Get website
     * 
     * @return Mage_Core_Model_Website
     */
    protected function getWebsite()
    {
        if (is_null($this->_website)) $this->_website = $this->_getHelper()->getWebsite();
        return $this->_website;
    }
    /**
     * Get website identifier
     * 
     * @return mixed
     */
    public function getWebsiteId()
    {
        return $this->_getHelper()->getWebsiteId($this->getWebsite());
    }
    /**
     * Set/Get whether the switcher should show default option
     * 
     * @param bool $hasDefaultOption
     * @return bool
     */
    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) $this->_hasDefaultOption = $hasDefaultOption;
        return $this->_hasDefaultOption;
    }
    /**
     * Get switch URL
     * 
     * @return string
     */
    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) return $url;
        return $this->getUrl('*/*/*', array('_current' => true, $this->_websiteVarName => null));
    }
}