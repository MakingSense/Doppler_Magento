<?php

class MakingSense_Doppler_Block_Adminhtml_Sync extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct (){
		$this->_controller = 'adminhtml_sync';
		$this->_blockGroup = 'makingsense_doppler';
		$this->_headerText = Mage::helper('makingsense_doppler')->__('Doppler Sync');
		$this->_addButtonLabel = Mage::helper('makingsense_doppler')->__('Sync');
	
		parent::__construct();
	}

}