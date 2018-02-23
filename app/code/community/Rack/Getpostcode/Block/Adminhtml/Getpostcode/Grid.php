<?php

class Rack_Getpostcode_Block_Adminhtml_Getpostcode_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('getpostcodeGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('getpostcode/postcode')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('getpostcode')->__('ID'),
            'align'     =>'right',
            'width'     => '30px',
            'index'     => 'id',
         ));

        $this->addColumn('post_code', array(
            'header'    => Mage::helper('getpostcode')->__('Postcode'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'post_code',
        ));

        $this->addColumn('prefecture_name', array(
            'header'    => Mage::helper('getpostcode')->__('Prefecture Name'),
            'align'     => 'left',
            'width'     => '150px',
            'index'     => 'prefecture_name',
        ));
        
        $this->addColumn('city_ward', array(
            'header'    => Mage::helper('getpostcode')->__('City Ward'),
            'align'     => 'left',
            'width'     => '200px',
            'index'     => 'city_ward',
        ));
      
        $this->addColumn('area', array(
            'header'    => Mage::helper('getpostcode')->__('Area'),
            'align'     => 'left',
            'width'     => '250px',
            'index'     => 'area',
        ));
      
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('getpostcode')->__('Action'),
                'width'     => '50',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('getpostcode')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        //$this->addExportType('*/*/exportCsv', Mage::helper('getpostcode')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('getpostcode')->__('XML'));
        
      return parent::_prepareColumns();
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('postcode_id');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('getpostcode')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('getpostcode')->__('Are you sure?')
        ));

        return $this;
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}