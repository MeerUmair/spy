<?php
$installer = $this;
/* @var $installer Verite_Japanpost_Model_Setup */

$installer->startSetup();

$installer->run("
    CREATE TABLE `{$installer->getTable('japanpost/log')}` (
        `log_id`            INT unsigned AUTO_INCREMENT,
        `type`              enum('import','export'),
        `level`             INT unsigned,
        `date`              DATETIME,
        `message`           TEXT,
        INDEX(`type`),
        PRIMARY KEY (`log_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE `{$installer->getTable('japanpost/import_buffer')}` (
        `id`        INT unsigned auto_increment,
        `data`      TEXT,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE `{$installer->getTable('japanpost/export_buffer')}` (
        `id`        INT unsigned auto_increment,
        `data`      TEXT,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->addAttribute('order', 'is_japanpost_exported', array(
    'visible'   => false,
    'type'      => 'int',
    'required'  => false,
    'default'   => 0,
    'global'    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$installer->endSetup();