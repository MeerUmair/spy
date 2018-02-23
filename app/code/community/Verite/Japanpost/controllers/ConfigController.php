<?php
/**
 * Magento
 *
 * @category    Verite
 * @package     Verite_Japanpost
 * @copyright   
 * @license     
 */


/**
 * config controller
 *
 * @category    Verite
 * @package     Verite_Japanpost
 * @author     
 */
class Verite_Japanpost_ConfigController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Whether current section is allowed
     *
     * @var bool
     */
    protected $_isSectionAllowedFlag = true;

    /**
     * Controller predispatch method
     * Check if current section is found and is allowed
     *
     * @return Verite_Japanpost_ConfigController
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if ($this->getRequest()->getParam('section')) {
            $this->_isSectionAllowedFlag = $this->_isSectionAllowed($this->getRequest()->getParam('section'));
        }

        return $this;
    }

    /**
     *  Custom save logic for section
     */
    protected function _saveSection ()
    {
        $method = '_save' . uc_words($this->getRequest()->getParam('section'), '');
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    /**
     *  Description goes here...
     */
    protected function _saveAdvanced ()
    {
        Mage::app()->cleanCache(
            array(
                'layout',
                Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG
            )
        );
    }

    /**
     * Export japanpost shipping table rates in csv format
     *
     */
    public function exportJapanpostAction()
    {
        $fileName   = 'japanpost.csv';
        /** @var $gridBlock Verite_Japanpost_Block_Carrier_Japanpost_Grid */
        $gridBlock  = $this->getLayout()->createBlock('japanpost/carrier_japanpost_grid');
        $website    = Mage::app()->getWebsite($this->getRequest()->getParam('website'));
        if ($this->getRequest()->getParam('conditionName')) {
            $conditionName = $this->getRequest()->getParam('conditionName');
        } else {
            $conditionName = $website->getConfig('carriers/japanpost/condition_name');
        }
        $gridBlock->setWebsiteId($website->getId())->setConditionName($conditionName);
        $content    = $gridBlock->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Check is allow modify system configuration
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config');
    }

    /**
     * Check if specified section allowed in ACL
     *
     * Will forward to deniedAction(), if not allowed.
     *
     * @param string $section
     * @return bool
     */
    protected function _isSectionAllowed($section)
    {
        try {
            $session = Mage::getSingleton('admin/session');
            $resourceLookup = "admin/system/config/{$section}";
            if ($session->getData('acl') instanceof Mage_Admin_Model_Acl) {
                $resourceId = $session->getData('acl')->get($resourceLookup)->getResourceId();
                if (!$session->isAllowed($resourceId)) {
                    throw new Exception('');
                }
                return true;
            }
        }
        catch (Zend_Acl_Exception $e) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        catch (Exception $e) {
            $this->deniedAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
    }

    /**
     * saving state of config field sets
     *
     * @param array $configState
     * @return bool
     */
    protected function _saveState($configState = array())
    {
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        if (is_array($configState)) {
            $extra = $adminUser->getExtra();
            if (!is_array($extra)) {
                $extra = array();
            }
            if (!isset($extra['configState'])) {
                $extra['configState'] = array();
            }
            foreach ($configState as $fieldset => $state) {
                $extra['configState'][$fieldset] = $state;
            }
            $adminUser->saveExtra($extra);
        }

        return true;
    }
}
