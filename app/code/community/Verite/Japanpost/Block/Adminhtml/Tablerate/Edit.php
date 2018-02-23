<?php
/**
 * Table rate edit block
 *
 * @category   Verite
 * @package    Verite_Japanpost
 * @author     
 */
class Verite_Japanpost_Block_Adminhtml_Tablerate_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
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
     * Retrieve admin session model
     *
     * @return Mage_Admin_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('admin/session');
    }
    /**
     * Retrieve registered japanpost shipping table rate model
     *
     * @return Verite_Japanpost_Model_Tablerate
     */
    protected function getModel()
    {
        return Mage::registry('japanpost_tablerate');
    }
    /**
     * Check is allowed action
     * 
     * @param   string $action
     * @return  bool
     */
    protected function isAllowedAction($action)
    {
        return $this->getSession()->isAllowed('sales/shipping/tablerates/'.$action);
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_objectId = 'tablerate_id';
        $this->_blockGroup = 'japanpost';
        $this->_controller = 'adminhtml_tablerate';
        parent::__construct();
        $helper = $this->_getHelper();
        if ($this->isAllowedAction('save')) {
            $this->_updateButton('save', 'label', $helper->__('Save Rate'));
        } else $this->_removeButton('save');
        if ($this->isAllowedAction('delete')) {
            $this->_updateButton('delete', 'label', $helper->__('Delete JapanPost Rate'));
        } else $this->_removeButton('delete');
    }
    /**
     * Get header text
     * 
     * @return  string
     */
    public function getHeaderText()
    {
        $helper = $this->_getHelper();
        $model = $this->getModel();
        if ($model && $model->getId()) {
            return $helper->__("Edit JapanPost Rate '%s'", $this->htmlEscape($model->getTitle()));
        } else {
            return $helper->__('New JapanPost Rate');
        }
    }
    /**
     * Preparing block layout
     *
     * @return Verite_Japanpost_Block_Adminhtml_Tablerate_Edit
     */
    protected function _prepareLayout()
    {
        $json = $this->helper('directory')->getRegionJson();
        $this->_formScripts[] = 'var updater = new RegionUpdater("japanpost_tablerate_dest_country_id", "none", "japanpost_tablerate_dest_region_id", '.$json.', "disable")';
        return parent::_prepareLayout();
    }
    /**
     * Get URL parameters
     * 
     * @return array
     */
    protected function getURLParams()
    {
        return array('website' => $this->getWebsiteId());
    }
    /**
     * Get URL for back (reset) button
     * 
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/', $this->getURLParams());
    }
    /**
     * Get URL for delete button
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array_merge(
            array($this->_objectId => $this->getRequest()->getParam($this->_objectId)), $this->getURLParams()
        ));
    }
    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) return $this->getData('form_action_url');
        return $this->getUrl('*/'.$this->_controller.'/save', $this->getURLParams());
    }
}