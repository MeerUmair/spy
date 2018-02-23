<?php

class Rack_EventCalendar_Model_Mysql4_Calendar_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
		parent::_construct();
        $this->_init('calendar/calendar');
    }
	
	public function addEnableFilter($status)
    {
        $this->getSelect()
            ->where('status = ?', $status);
        return $this;
    }
	
    /**
     * Add Filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     * @return Mage_Cms_Model_Mysql4_Page_Collection
     */
    
    public function addStoreFilter($store)
    {
		if (!Mage::app()->isSingleStoreMode()) {
			if ($store instanceof Mage_Core_Model_Store) {
				$store = array($store->getId());
			}
	
			$this->getSelect()
			->where('main_table.store_id in (?)', array(0, $store));
	
			return $this;
		}
		return $this;
    }
}
