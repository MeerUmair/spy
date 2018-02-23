<?php

$installer = $this;
/* @var $installer Rack_Getpostcode_Model_Setup */

$installer->startSetup();

$file = dirname(__FILE__) . "/../../misc/ken_all.csv";
$model = Mage::getModel('getpostcode/postcode');
$model->import($file);
$installer->endSetup();