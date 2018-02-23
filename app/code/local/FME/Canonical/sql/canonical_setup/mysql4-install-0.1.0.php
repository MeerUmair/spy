<?php

 $installer = new Mage_Eav_Model_Entity_Setup('core_setup');

    $installer->startSetup();
    $installer->addAttribute('catalog_product', 'fme_canonicalurl', array(
          'label'         => 'Canonical Url',
          'input'         => 'select',
          'type'          => 'varchar',
          'class'         => '',
          'global'        => true,
          'visible'       => true,
          'required'      => false,
          'user_defined'  => false,
          'default'       => '',
          'apply_to'      => 'simple,configurable,bundle,grouped',
            'option' => array(
              'value' => array( 
                'optionone' => array('Config'),)),
          'visible_on_front' => true,
          'is_configurable' => false,
          'wysiwyg_enabled' => false,
          'used_in_product_listing' => true,
          'is_html_allowed_on_front' => true,
          'group'         => 'Meta Information',
          'sort_order'    => '84',
        ));
    $installer->endSetup();