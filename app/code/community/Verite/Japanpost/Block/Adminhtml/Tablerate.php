<?php
/**
 * Table rates block
 * 
 * @category   Verite
 * @package    Verite_Japanpost
 * @author     
 */
class Verite_Japanpost_Block_Adminhtml_Tablerate extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Website
     * 
     * @var Mage_Core_Model_Website
     */
    protected $_website;
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
     * Check is allowed action
     * 
     * @param   string $action
     * @return  bool
     */
    protected function isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/shipping/tablerates/'.$action);
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $helper = $this->_getHelper();
        $this->_controller = 'adminhtml_tablerate';
        $this->_blockGroup = 'japanpost';
        $this->_headerText = $helper->__('Japanpost');
        parent::__construct();
        $this->setTemplate('verite_japanpost/tablerate.phtml');
        if ($this->isAllowedAction('save')) {
            $this->_updateButton('add', 'label', $helper->__('Add New Rate'));
        } else {
            $this->_removeButton('add');
        }
    }
    /**
     * Get create URL
     * 
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new', array('website' => $this->getWebsiteId()));
    }
}