<?php
error_reporting(E_ALL);
$ROOT_PATH = dirname(__FILE__) . '/../../../../../../';

chdir($ROOT_PATH);
require_once('app/Mage.php');

Mage::setIsDeveloperMode(true);

ini_set('display_errors', 1);

umask(0);
Mage::app();

$model = Mage::getModel('holidays/holidays');
echo $model->getShortestDeliveryDate(5);
