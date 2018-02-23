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
 * Japanpost shipping carrier grid block
 * WARNING: This grid used for export japanpost
 *
 * @category    Verite
 * @package     Verite_Japanpost
 * @author      
 */
class Verite_Japanpost_Block_Carrier_Japanpost_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Website filter
     *
     * @var int
     */
    protected $_websiteId;

    /**
     * Condition filter
     *
     * @var string
     */
    protected $_conditionName;

    /**
     * Define grid properties
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('japanpostGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * Set current website
     *
     * @param int $websiteId
     * @return Verite_Japanpost_Block_Carrier_Japanpost_Grid
     */
    public function setWebsiteId($websiteId)
    {
        $this->_websiteId = Mage::app()->getWebsite($websiteId)->getId();
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        if (is_null($this->_websiteId)) {
            $this->_websiteId = Mage::app()->getWebsite()->getId();
        }
        return $this->_websiteId;
    }

    /**
     * Set current website
     *
     * @param int $websiteId
     * @return Verite_Japanpost_Block_Carrier_Japanpost_Grid
     */
    public function setConditionName($name)
    {
        $this->_conditionName = $name;
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getConditionName()
    {
        return $this->_conditionName;
    }

    /**
     * Prepare japanpost shipping table rate collection
     *
     * @return Verite_Japanpost_Block_Carrier_Japanpost_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Mage_Shipping_Model_Mysql4_Carrier_Tablerate_Collection */
        $collection = Mage::getResourceModel('japanpost/tablerate_collection');
        $collection->setConditionFilter($this->getConditionName())
            ->setWebsiteFilter($this->getWebsiteId());

        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    /**
     * Prepare table columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('dest_country', array(
            'header'    => Mage::helper('adminhtml')->__('Country'),
            'index'     => 'dest_country',
            'default'   => '*',
        ));

        $this->addColumn('dest_region', array(
            'header'    => Mage::helper('adminhtml')->__('Region/State'),
            'index'     => 'dest_region',
            'default'   => '*',
        ));

        $this->addColumn('dest_zip', array(
            'header'    => Mage::helper('adminhtml')->__('Zip/Postal Code'),
            'index'     => 'dest_zip',
        ));

        $label = Mage::getSingleton('japanpost/carrier_japanpost')
            ->getCode('condition_name_short', $this->getConditionName());
        $this->addColumn('condition_value', array(
            'header'    => $label,
            'index'     => 'condition_value',
        ));

        $this->addColumn('price', array(
            'header'    => Mage::helper('adminhtml')->__('Shipping Price'),
            'index'     => 'price',
        ));

        return parent::_prepareColumns();
    }
}
