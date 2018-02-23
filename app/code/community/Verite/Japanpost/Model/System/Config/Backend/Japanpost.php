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
 * Backend model for japanpost shipping table rates CSV importing
 *
 * @category    Verite
 * @package     Verite_Japanpost
 * @author     
 */

class Verite_Japanpost_Model_System_Config_Backend_Japanpost extends Mage_Core_Model_Config_Data
{
    public function _afterSave()
    {
        Mage::getResourceModel('japanpost/tablerate')->uploadAndImport($this);
    }
}
