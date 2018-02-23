<?php
/**
 * Japanpost table rate collection
 * 
 * @category   Verite
 * @package    Verite_Japanpost
 * @author     
 */
class Verite_Japanpost_Model_Mysql4_Tablerate_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * directory/country table name
     *
     * @var string
     */
    protected $_countryTable;

    /**
     * directory/country_region table name
     *
     * @var string
     */
    protected $_regionTable;
    /**
     * Constructor
     */
    protected function _construct() {
        $this->_init('japanpost/tablerate');
        $this->_countryTable    = $this->getTable('directory/country');
        $this->_regionTable     = $this->getTable('directory/country_region');
    }
    /**
     * Initialize select, add country iso3 code and region name
     *
     * @return void
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $this->_select->joinLeft(
                array('country_table' => $this->_countryTable), 
                'country_table.country_id = main_table.dest_country_id', 
                array('dest_country' => 'iso2_code'))
            ->joinLeft(
                array('region_table' => $this->_regionTable),
                'region_table.region_id = main_table.dest_region_id',
                array('dest_region' => 'code'));
    }
    /**
     * Add website filter to collection
     *
     * @param int $websiteId
     * @return Verite_Japanpost_Model_Mysql4_Tablerate_Collection
     */
    public function setWebsiteFilter($websiteId)
    {
        return $this->addFieldToFilter('website_id', $websiteId);
    }
    
    /**
     * Add condition name (code) filter to collection
     *
     * @param string $conditionName
     * @return Mage_Shipping_Model_Mysql4_Carrier_Tablerate_Collection
     */
    public function setConditionFilter($conditionName)
    {
        return $this->addFieldToFilter('condition_name', $conditionName);
    }
    
    /**
     * Add country filter to collection
     *
     * @param string $countryId
     * @return Mage_Shipping_Model_Mysql4_Carrier_Tablerate_Collection
     */
    public function setCountryFilter($countryId)
    {
        return $this->addFieldToFilter('dest_country_id', $countryId);
    }
}