<?php

class MakingSense_Doppler_Block_Adminhtml_Lists_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct (){
		parent::__construct();
		
		$this->setId('makingsense_doppler_lists_grid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('asc');
		$this->setSaveParametersInSession(true);
	}
	
    protected function _prepareCollection (){

		$collection = Mage::getModel('makingsense_doppler/lists')->getCollection();
		$this->setCollection($collection);
		
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns (){
		$this->addColumn('list_id', array(
			'header' => Mage::helper('makingsense_doppler')->__('List ID'),
			'index' => 'list_id',
			'width' => '90px'
		));
		$this->addColumn('name', array(
			'header' => Mage::helper('makingsense_doppler')->__('List Name'),
			'index' => 'name'
		));
		$this->addColumn('creation_date', array(
			'header' => Mage::helper('makingsense_doppler')->__('Creation Date'),
			'index' => 'creation_date',
			'width' => '200px'
		));
		$this->addColumn('subscribers_count', array(
			'header' => Mage::helper('makingsense_doppler')->__('Subscribers Count'),
			'index' => 'subscribers_count',
			'width' => '90px'
		));
	}
	
	protected function _prepareMassaction (){
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('lists');

		$this->getMassactionBlock()->addItem('delete', array(
			'label'    => Mage::helper('makingsense_doppler')->__('Delete'),
			'url'      => $this->getUrl('*/*/massDelete'),
			'confirm'  => Mage::helper('makingsense_doppler')->__('Are you sure?')
		));
		
		return parent::_prepareMassaction();
	}
	
	public function getRowUrl ($row){
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
	
}