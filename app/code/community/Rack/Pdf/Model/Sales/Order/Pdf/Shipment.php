<?php
class Rack_Pdf_Model_Sales_Order_Pdf_Shipment extends Mage_Sales_Model_Order_Pdf_Shipment
{
    protected function _setFontRegular($object, $size = 7)
    {
        $fontFile = Mage::getModel('pdf/file', Rack_Pdf_Model_File::PDF_FONT_REGULER);
        $font = Zend_Pdf_Font::fontWithPath($fontFile->getPath());
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = 7)
    {
        $fontFile = Mage::getModel('pdf/file', Rack_Pdf_Model_File::PDF_FONT_BOLD);
        $font = Zend_Pdf_Font::fontWithPath($fontFile->getPath());
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($object, $size = 7)
    {
        $fontFile = Mage::getModel('pdf/file', Rack_Pdf_Model_File::PDF_FONT_ITALIC);
        $font = Zend_Pdf_Font::fontWithPath($fontFile->getPath());
        $object->setFont($font, $size);
        return $font;
    }
}
