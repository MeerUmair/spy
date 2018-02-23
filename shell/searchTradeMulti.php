#!/usr/bin/php -f
<?php
require_once "abstract.php";

class Mage_Shell_Compiler extends Mage_Shell_Abstract {
    public function run() {
        $model = Mage::getModel('ecggmo/cron_searchTradeMulti');
        $model->run();
    }
}

$compiler = new Mage_Shell_Compiler();
$compiler->run();

