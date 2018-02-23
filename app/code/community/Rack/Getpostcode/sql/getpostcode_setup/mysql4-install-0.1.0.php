<?php

$installer = $this;

/* @var $installer Rack_Getpostcode_Model_Setup */

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('post_code_ref')};
CREATE TABLE `{$this->getTable('post_code_ref')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_code` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `prefecture_name_kana` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `city_ward_kana` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `area_kana` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prefecture_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `city_ward` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `area` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 ");

$installer->endSetup();
