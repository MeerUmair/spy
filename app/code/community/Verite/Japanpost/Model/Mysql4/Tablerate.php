<?php
/**
 * Japanpost table rate resource model
 *
 * @category   Verite
 * @package    Verite_Japanpost
 * @author     
 */
class Verite_Japanpost_Model_Mysql4_Tablerate extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Constructor
     */
    protected function _construct() {
        $this->_init('japanpost/tablerate', 'pk');
    }
    /**
     * Perform actions before object save
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object) {
        parent::_beforeSave($object);
        return $this;
    }
    /**
     * Load table rate by request
     * 
     * @param Verite_Japanpost_Model_Tablerate $tablerate
     * @param Varien_Object $request
     * @return Verite_Japanpost_Model_Mysql4_Tablerate
     */
    public function loadByRequest(Verite_Japanpost_Model_Tablerate $tablerate, Varien_Object $request)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from($this->getMainTable());
        $conditions = array();
        if ($request->getId()) array_push($conditions, '(pk != '.$adapter->quote($request->getId()).')');
        $websiteId = ($request->getWebsiteId()) ? $request->getWebsiteId() : '0';
        $destCountryId = ($request->getDestCountryId()) ? $request->getDestCountryId() : '0';
        $destRegionId = ($request->getDestRegionId()) ? $request->getDestRegionId() : '0';
        $destZip = ($request->getDestZip()) ? $request->getDestZip() : '';
        $conditionName = ($request->getConditionName()) ? $request->getConditionName() : '';
        $conditionValue = ($request->getConditionValue()) ? $request->getConditionValue() : '';
        array_push($conditions, '(website_id = '.$adapter->quote($websiteId).')');
        array_push($conditions, '(dest_country_id = '.$adapter->quote($destCountryId).')');
        array_push($conditions, '(dest_region_id = '.$adapter->quote($destRegionId).')');
        array_push($conditions, '(dest_zip = '.$adapter->quote($destZip).')');
        array_push($conditions, '(condition_name = '.$adapter->quote($conditionName).')');
        array_push($conditions, '(condition_value = '.$adapter->quote($conditionValue).')');
        $select->where(implode(' AND ', $conditions));
        $select->limit(1);
        $row = $adapter->fetchRow($select);
        if ($row && !empty($row)) $tablerate->setData($row);
        $this->_afterLoad($tablerate);
        return $this;
    }
    
        /**
     * Return table rate array or false by rate request
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return array|false
     */
    public function getRate(Mage_Shipping_Model_Rate_Request $request)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array(
            ':website_id'   => (int)$request->getWebsiteId(),
            ':country_id'   => $request->getDestCountryId(),
            ':region_id'    => $request->getDestRegionId(),
            ':postcode'     => $request->getDestPostcode()
        );
        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where('website_id=:website_id')
            ->order(array('condition_value DESC', 'dest_country_id DESC', 'dest_region_id DESC', 'dest_zip DESC'))
            ->limit(1);

        // render destination condition
        $orWhere = '(' . implode(') OR (', array(
            "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = :postcode",
            "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = ''",
            "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = ''",
            "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = :postcode",
            "dest_country_id = '0' AND dest_region_id = 0 AND dest_zip = ''",
        )) . ')';
        $select->where($orWhere);

        // render condition by condition name
        if (is_array($request->getConditionName())) {
            $orWhere = array();
            $i       = 0;
            foreach ($request->getConditionName() as $conditionName) {
                $bindNameKey  = sprintf(':condition_name_%d', $i);
                $bindValueKey = sprintf(':condition_value_%d', $i);
                $orWhere[] = "(condition_name = {$bindNameKey} AND condition_value <= {$bindValueKey})";
                $bind[$bindNameKey]  = $conditionName;
                $bind[$bindValueKey] = $request->getData($conditionName);
                $i ++;
            }

            if ($orWhere) {
                $select->where(implode(' OR ', $orWhere));
            }
        } else {
            $bind[':condition_name']  = $request->getConditionName();
            $bind[':condition_value'] = $request->getData($request->getConditionName());

            $select->where('condition_name = :condition_name');
            $select->where('condition_value <= :condition_value');
        }

        return $adapter->fetchRow($select, $bind);
    }
    
    /**
     * Upload table rate file and import data from it
     *
     * @param Varien_Object $object
     * @throws Mage_Core_Exception
     * @return Verite_Japanpost_Model_Mysql4_Carrier_Japanpost
     */
    public function uploadAndImport(Varien_Object $object)
    {
        if (empty($_FILES['groups']['tmp_name']['japanpost']['fields']['import']['value'])) {
            return $this;
        }

        $csvFile = $_FILES['groups']['tmp_name']['japanpost']['fields']['import']['value'];
        $website = Mage::app()->getWebsite($object->getScopeId());

        $this->_importWebsiteId     = (int)$website->getId();
        $this->_importUniqueHash    = array();
        $this->_importErrors        = array();
        $this->_importedRows        = 0;

        $io = new Varien_Io_File();
        $info = pathinfo($csvFile);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');

        // check and skip headers
        $headers = $io->streamReadCsv();
        if ($headers === false || count($headers) < 5) {
            $io->streamClose();
            Mage::throwException(Mage::helper('shipping')->__('Invalid Table Rates File Format'));
        }

        if ($object->getData('groups/japanpost/fields/condition_name/inherit') == '1') {
            $conditionName = (string)Mage::getConfig()->getNode('default/carriers/japanpost/condition_name');
        } else {
            $conditionName = $object->getData('groups/japanpost/fields/condition_name/value');
        }
        $this->_importConditionName = $conditionName;

        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            $rowNumber  = 1;
            $importData = array();

            $this->_loadDirectoryCountries();
            $this->_loadDirectoryRegions();

            // delete old data by website and condition name
            $condition = array(
                'website_id = ?'     => $this->_importWebsiteId,
                'condition_name = ?' => $this->_importConditionName
            );
            $adapter->delete($this->getMainTable(), $condition);

            while (false !== ($csvLine = $io->streamReadCsv())) {
                $rowNumber ++;

                if (empty($csvLine)) {
                    continue;
                }

                $row = $this->_getImportRow($csvLine, $rowNumber);
                if ($row !== false) {
                    $importData[] = $row;
                }

                if (count($importData) == 5000) {
                    $this->_saveImportData($importData);
                    $importData = array();
                }
            }
            $this->_saveImportData($importData);
            $io->streamClose();
        } catch (Mage_Core_Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::throwException($e->getMessage());
        } catch (Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::logException($e);
            Mage::throwException(Mage::helper('shipping')->__('An error occurred while import table rates.'));
        }

        $adapter->commit();

        if ($this->_importErrors) {
            $error = Mage::helper('shipping')->__('%1$d records have been imported. See the following list of errors for each record that has not been imported: %2$s',
                $this->_importedRows, implode(" \n", $this->_importErrors));
            Mage::throwException($error);
        }

        return $this;
    }
    
    /**
     * Load directory countries
     *
     * @return Verite_Japanpost_Model_Mysql4_Carrier_Japanpost
     */
    protected function _loadDirectoryCountries()
    {
        if (!is_null($this->_importIso2Countries) && !is_null($this->_importIso3Countries)) {
            return $this;
        }

        $this->_importIso2Countries = array();
        $this->_importIso3Countries = array();

        /** @var $collection Mage_Directory_Model_Mysql4_Country_Collection */
        $collection = Mage::getResourceModel('directory/country_collection');
        foreach ($collection->getData() as $row) {
            $this->_importIso2Countries[$row['iso2_code']] = $row['country_id'];
            $this->_importIso3Countries[$row['iso3_code']] = $row['country_id'];
        }

        return $this;
    }
    
    
    /**
     * Load directory regions
     *
     * @return Verite_Japanpost_Model_Mysql4_Carrier_Japanpost
     */
    protected function _loadDirectoryRegions()
    {
        if (!is_null($this->_importRegions)) {
            return $this;
        }

        $this->_importRegions = array();

        /** @var $collection Mage_Directory_Model_Mysql4_Region_Collection */
        $collection = Mage::getResourceModel('directory/region_collection');
        foreach ($collection->getData() as $row) {
            $this->_importRegions[$row['country_id']][$row['code']] = (int)$row['region_id'];
        }

        return $this;
    }
    
    /**
     * Validate row for import and return table rate array or false
     * Error will be add to _importErrors array
     *
     * @param array $row
     * @param int $rowNumber
     * @return array|false
     */
    protected function _getImportRow($row, $rowNumber = 0)
    {
        // validate row
        if (count($row) < 5) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Table Rates format in the Row #%s',
                $rowNumber);
            return false;
        }
        // validate country
        if (isset($this->_importIso2Countries[$row[0]])) {
            $countryId = $this->_importIso2Countries[$row[0]];
        } else if (isset($this->_importIso3Countries[$row[0]])) {
            $countryId = $this->_importIso3Countries[$row[0]];
        } else if ($row[0] == '*' || $row[0] == '') {
            $countryId = '0';
        } else {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Country "%s" in the Row #%s.',
                $row[0], $rowNumber);
            return false;
        }

        // validate region
        if ($countryId != '0' && isset($this->_importRegions[$countryId][$row[1]])) {
            $regionId = $this->_importRegions[$countryId][$row[1]];
        } else if ($row[1] == '*' || $row[1] == '') {
            $regionId = 0;
        } else {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Region/State "%s" in the Row #%s.',
                $row[1], $rowNumber);
            return false;
        }

        // detect zip code
        if ($row[2] == '*' || $row[2] == '') {
            $zipCode = '';
        } else {
            $zipCode = $row[2];
        }

        // validate condition value
        $value = $this->_parseDecimalValue($row[3]);
        if ($value === false) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid %s "%s" in the Row #%s.',
                $this->_getConditionFullName($this->_importConditionName), $row[3], $rowNumber);
            return false;
        }

        // validate price
        $price = $this->_parseDecimalValue($row[4]);
        if ($price === false) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Shipping Price "%s" in the Row #%s.',
                $row[4], $rowNumber);
            return false;
        }

        // protect from duplicate
        $hash = sprintf("%s-%d-%s-%F", $countryId, $regionId, $zipCode, $value);
        if (isset($this->_importUniqueHash[$hash])) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Duplicate Row #%s (Country "%s", Region/State "%s", Zip "%s" and Value "%s").',
                $rowNumber, $row[0], $row[1], $zipCode, $value);
            return false;
        }
        $this->_importUniqueHash[$hash] = true;

        return array(
            $this->_importWebsiteId,    // website_id
            $countryId,                 // dest_country_id
            $regionId,                  // dest_region_id,
            $zipCode,                   // dest_zip
            $this->_importConditionName,// condition_name,
            $value,                     // condition_value
            $price                      // price
        );
    }
    
    /**
     * Return import condition full name by condition name code
     *
     * @return string
     */
    protected function _getConditionFullName($conditionName)
    {
        if (!isset($this->_conditionFullNames[$conditionName])) {
            $name = Mage::getSingleton('japanpost/tablerate')->getCode('condition_name_short', $conditionName);
            $this->_conditionFullNames[$conditionName] = $name;
        }

        return $this->_conditionFullNames[$conditionName];
    }
    
    /**
     * Save import data batch
     *
     * @param array $data
     * @return Verite_Japanpost_Model_Mysql4_Carrier_Japanpost
     */
    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns = array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip',
                'condition_name', 'condition_value', 'price');
            $this->_getWriteAdapter()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }

        return $this;
    }

    /**
     * Parse and validate positive decimal value
     * Return false if value is not decimal or is not positive
     *
     * @param string $value
     * @return bool|float
     */
    protected function _parseDecimalValue($value)
    {
        if (!is_numeric($value)) {
            return false;
        }
        $value = (float)sprintf('%.4F', $value);
        if ($value < 0.0000) {
            return false;
        }
        return $value;
    }

    /**
     * Parse and validate positive decimal value
     *
     * @see Verite_Japanpost_Model_Mysql4_Carrier_Japanpost::_parseDecimalValue()
     * @deprecated since 1.4.1.0
     * @param string $value
     * @return bool|float
     */
    protected function _isPositiveDecimalNumber($value)
    {
        return $this->_parseDecimalValue($value);
    }
}