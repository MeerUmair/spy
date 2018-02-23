<?php

class Rack_Jp_Core_Block_Adminhtml_Directory_Grid_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('directoryGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('jpcore/postcode')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('jpcore')->__('ID'),
            'align'     =>'right',
            'width'     => '30px',
            'index'     => 'currency_id',
         ));

        $this->addColumn('currency code', array(
            'header'    => Mage::helper('jpcore')->__('Currency Code'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'currency_code',
        ));

        $this->addColumn('precision', array(
            'header'    => Mage::helper('jpcore')->__('Precision'),
            'align'     => 'left',
            'width'     => '150px',
            'index'     => 'precision',
        ));
        
        $this->addColumn('marker_position', array(
            'header'    => Mage::helper('jpcore')->__('Marker Position'),
            'align'     => 'left',
            'width'     => '50px',
            'index'     => 'marker_position',
        ));
      
        $this->addColumn('area', array(
            'header'    => Mage::helper('jpcore')->__('Area'),
            'align'     => 'left',
            'width'     => '250px',
            'index'     => 'area',
        ));
      
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('jpcore')->__('Action'),
                'width'     => '50',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('jpcore')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        //$this->addExportType('*/*/exportCsv', Mage::helper('jpcore')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('jpcore')->__('XML'));
        
      return parent::_prepareColumns();
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('currency_id');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('jpcore')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('jpcore')->__('Are you sure?')
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