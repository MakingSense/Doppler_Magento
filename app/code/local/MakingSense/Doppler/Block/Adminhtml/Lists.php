<?php

class MakingSense_Doppler_Block_Adminhtml_Lists extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct ()
	{
		$this->_controller = 'adminhtml_lists';
		$this->_blockGroup = 'makingsense_doppler';
		$this->_headerText = Mage::helper('makingsense_doppler')->__('Doppler Lists');
		$this->_addButtonLabel = Mage::helper('makingsense_doppler')->__('Add List');
	
		parent::__construct();
	}

}