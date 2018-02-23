<?php
$installer = $this;
$installer->startSetup();

$installer->run(
        "CREATE TABLE `{$this->getTable('calendar_calendar')}` (
            `day_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `store_id` int(10) unsigned NOT NULL,
            `day` date NOT NULL,
            `day_comment` text,
            `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
            `status` int(10) unsigned NOT NULL,
            `is_holiday` smallint(5) unsigned NOT NULL,
            PRIMARY KEY (`day_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        CREATE TABLE {$this->getTable('calendar_store')} (
            `day_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `store_id` varchar(45) NOT NULL,
            PRIMARY KEY (`day_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

$installer->endSetup();

