<?php

/**
 * Class to convert half- or full-width character
 * 
 *
 */
class Rack_Jp_Validator_Helper_CharConverter extends Mage_Core_Helper_Abstract
{
    const NAME_LENGTH_KEY = 'validator/validator/namelength';
    /**
     * Converts a Half-width character string 
     * into full-width character string
     * 
     * @param string $string
     */
    public static function convertToFullWidth( $string )
    {
        if(self::getConfigData(self::NAME_LENGTH_KEY)) {
            return mb_convert_kana($string, 'KA', 'UTF-8');
        }
        
        return $string;
    }

    /**
     * Converts a full-width character string 
     * into half-width character string
     * 
     * @param string $string
     */
    public static function convertToHalfWidth( $string )
    {
        if(self::getConfigData(self::NAME_LENGTH_KEY)) {
            return mb_convert_kana($string, 'a', 'UTF-8');
        }
        
        return $string;
    }
    
    public static function getConfigData($key)
    {
        return Mage::getConfig($key);
    }
}