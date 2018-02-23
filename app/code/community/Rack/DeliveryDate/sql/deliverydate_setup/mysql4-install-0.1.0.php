<?php
/** 
 * @var $installer Mage_Sales_Model_Entity_Setup 
 */
$installer = $this;

$installer->startSetup();
$installer->addAttribute('order', 'delivery_date', array('type' => 'varchar', 'visible' => true, 'required' => false))
          ->addAttribute('order', 'delivery_time', array('type' => 'varchar', 'visible' => true, 'required' => false))
          ->addAttribute('order_address', 'delivery_date', array('type' => 'varchar', 'visible' => true, 'required' => false))
          ->addAttribute('order_address', 'delivery_time', array('type' => 'varchar', 'visible' => true, 'required' => false))
          ->addAttribute('quote', 'delivery_date', array('type' => 'varchar', 'visible' => true, 'required' => false))
          ->addAttribute('quote', 'delivery_time', array('type' => 'varchar', 'visible' => true, 'required' => false))
          ->addAttribute('quote_address', 'delivery_date', array('type' => 'varchar', 'visible' => true, 'required' => false))
          ->addAttribute('quote_address', 'delivery_time', array('type' => 'varchar', 'visible' => true, 'required' => false));

$installer->endSetup();