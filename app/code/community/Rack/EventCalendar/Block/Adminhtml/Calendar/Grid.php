<?php
class Rack_EventCalendar_Block_Adminhtml_Calendar_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('calendarGrid');
		$this->setDefaultSort('day');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
	}
	
	protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0); 
        return Mage::app()->getStore($storeId);
    }
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('calendar/calendar')->getCollection();
		$store = $this->_getStore();
		if ($store->getId()) {
            $collection->addStoreFilter($store);
		}
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
    {
        $this->addColumn('day_id', array(
                    'header'    => Mage::helper('calendar')->__('ID'),
                    'align'     =>'right',
                    'width'     => '50px',
                    'index'     => 'day_id',
                    ));

        $this->addColumn('day', array(
                    'header'    => Mage::helper('calendar')->__('Day'),
                    'align'     =>'left',
                    'index'     => 'day',
                    'type'      => 'date'
                    ));

        $this->addColumn('day_comment', array(
                    'header'    => Mage::helper('calendar')->__('Day Comment'),
                    'align'     => 'left',
                    'index'     => 'day_comment',
                    ));

        $this->addColumn('created_time', array(
                    'header'    => Mage::helper('calendar')->__('Created'),
                    'align'     => 'left',
                    'width'     => '120px',
                    'type'      => 'date',
                    'default'   => '',
                    'index'     => 'created_time',
                    ));

        $this->addColumn('is_holiday', array(
                    'header' => Mage::helper('calendar')->__('Is Holiday'),
                    'align'  => 'left',
                    'width'  => '80px',
                    'index'  => 'is_holiday',
                    'type'   => 'options',
                    'options' => array(
                        1 => Mage::helper('calendar')->__('Yes'),
                        2 => Mage::helper('calendar')->__('No'),
                        ),
                    )
                );


        $this->addColumn('status', array(
                    'header'    => Mage::helper('calendar')->__('Status'),
                    'align'     => 'left',
                    'width'     => '80px',
                    'index'     => 'status',
                    'type'      => 'options',
                    'options'   => array(
                        1 => Mage::helper('calendar')->__('Enabled'),
                        2 => Mage::helper('calendar')->__('Disabled'),
                        ),
                    ));

        $this->addColumn('action',
                array(
                    'header'    =>  Mage::helper('calendar')->__('Action'),
                    'width'     => '100',
                    'type'      => 'action',
                    'getter'    => 'getId',
                    'actions'   => array(
                        array(
                            'caption'   => Mage::helper('calendar')->__('Edit'),
                            'url'       => array('base'=> '*/*/edit'),
                            'field'     => 'id'
                             )
                             ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
                   ));	
                   return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('day_id');
        $this->getMassactionBlock()->setFormFieldName('calendar');

        $this->getMassactionBlock()->addItem('delete', array(
                                             'label'    => Mage::helper('calendar')->__('Delete'),
                                             'url'      => $this->getUrl('*/*/massDelete'),
                                             'confirm'  => Mage::helper('calendar')->__('Are you sure?')
                                             ));

        $statuses = Mage::getSingleton('calendar/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
                                             'label'=> Mage::helper('calendar')->__('Change status'),
                                             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                                             'additional' => array(
                                                     'visibility' => array(
                                                         'name' => 'status',
                                                         'type' => 'select',
                                                         'class' => 'required-entry',
                                                         'label' => Mage::helper('calendar')->__('Status'),
                                                         'values' => $statuses
                                                        )
                                                    )
                                             ));
         return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
