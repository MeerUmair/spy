<?php
class Rack_Pdf_Model_Sales_Order_Pdf_Items_Invoice_Default extends Mage_Sales_Model_Order_Pdf_Items_Invoice_Default
{
    protected function _setFontRegular($size = 7)
    {
        $fontFile = Mage::getModel('pdf/file', Rack_Pdf_Model_File::PDF_FONT_REGULER);
        $font = Zend_Pdf_Font::fontWithPath($fontFile->getPath());
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($size = 7)
    {
        $fontFile = Mage::getModel('pdf/file', Rack_Pdf_Model_File::PDF_FONT_BOLD);
        $font = Zend_Pdf_Font::fontWithPath($fontFile->getPath());
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($size = 7)
    {
        $fontFile = Mage::getModel('pdf/file', Rack_Pdf_Model_File::PDF_FONT_ITALIC);
        $font = Zend_Pdf_Font::fontWithPath($fontFile->getPath());
        $this->getPage()->setFont($font, $size);
        return $font;
    }
}
