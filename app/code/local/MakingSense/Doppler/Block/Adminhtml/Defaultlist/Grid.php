<?php
/**
 * Defaultlist edit grid
 *
 * @category    MakingSense
 * @package     Doppler
 * @author      Gabriel Guarino <guarinogabriel@gmail.com>
 */
class MakingSense_Doppler_Block_Adminhtml_Defaultlist_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
	 * Set grid ID, default sort by and direction
	 */
	public function __construct (){
		parent::__construct();
		
		$this->setId('makingsense_doppler_defaultlist_grid');
		$this->setFilterVisibility(false);
		$this->setHeadersVisibility(false);
		$this->setPagerVisibility(false);
		$this->setEmptyText('There is no default Doppler list.');
		$this->setSaveParametersInSession(true);
	}

	/**
	 * Set collection for grid
	 */
    protected function _prepareCollection (){

		$collection = Mage::getModel('makingsense_doppler/defaultlist')->getCollection();
		$this->setCollection($collection);
		
		return parent::_prepareCollection();
	}

	/**
	 * Set columns for grid
	 */
	protected function _prepareColumns (){
		$this->addColumn('list_id', array(
			'header' => Mage::helper('makingsense_doppler')->__('List ID'),
			'index'  => 'list_id',
			'filter' => false,
			'width'  => '90px'
		));
		$this->addColumn('name', array(
			'header' => Mage::helper('makingsense_doppler')->__('List Name'),
			'filter'     => false,
			'index'  => 'name'
		));
		$this->addColumn('last_import', array(
			'header' => Mage::helper('makingsense_doppler')->__('Last Import Date'),
			'index'  => 'last_import',
			'filter'     => false,
			'width'  => '200px'
		));
	}

	/**
	 * Set URL for table row
	 */
	public function getRowUrl ($row){
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
	
}