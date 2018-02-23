<?php
class Rack_Pdf_Model_File
{
    const XML_PATH_FONTDIR = 'pdf/font/';
    const PDF_FONT_REGULER = 'reguler';
    const PDF_FONT_BOLD = 'bold';
    const PDF_FONT_ITALIC = 'italic';

    protected $_fontPathStr;

    public function getPath()
    {
        return Mage::getBaseDir() . DS . Mage::getStoreConfig(self::XML_PATH_FONTDIR . $this->_fontPathStr);
    }
    
    public function __construct($path)
    {
        $this->_fontPathStr = $path;
    }
}
