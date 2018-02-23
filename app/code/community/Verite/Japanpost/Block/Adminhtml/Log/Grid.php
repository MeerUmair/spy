<?php

class Verite_Japanpost_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _construct()
    {
        $this->setId('log_data');
        if ($filter = $this->getRequest()->getParam('default', false)) {
            $this->resetFilter();
            $this->setDefaultFilter(array('type' => $filter));
        }
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        /* @var $collection Verite_Japanpost_Model_Mysql4_Log_Collection */
        $collection = Mage::getResourceModel('japanpost/log_collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => '#',
            'index'     => 'log_id',
            'type'      => 'number'
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('japanpost')->__('Type'),
            'index'     => 'type',
            'type'      => 'options',
            'width'     => '100px',
            'options'   => array(
                'import'    => 'Import',
                'export'    => 'Export'
            )
        ));

        $this->addColumn('level', array(
            'header'    => Mage::helper('japanpost')->__('Level'),
            'index'     => 'level',
            'type'      => 'options',
            'width'     => '80px',
            'options'   => array(
                6    => 'INFO',
                4    => 'WARN'
            )
        ));

        $this->addColumn('date', array(
            'header'    => Mage::helper('japanpost')->__('Date'),
            'index'     => 'date',
            'width'     => '100px',
            'type'      => 'datetime'
        ));

        $this->addColumn('message', array(
            'header'    => Mage::helper('japanpost')->__('Message'),
            'index'     => 'message',
            'type'      => 'longtext'
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return Mage::getUrl('*/*/grid', array('_current' => true));
    }

    protected function resetFilter()
    {
        $session = Mage::getSingleton('adminhtml/session');
        $sessionParamName = $this->getId(). $this->getVarNameFilter();

        $session->unsetData($sessionParamName);
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('log_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('japanpost')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('japanpost')->__('Are you sure?')
        ));

        return $this;
    }
}