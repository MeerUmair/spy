<?php

class Verite_Japanpost_Block_Adminhtml_Upload extends Mage_Adminhtml_Block_Template
{
    public function getPostUrl()
    {
        return $this->getUrl('adminhtml/japanpost_import/import');
    }
}