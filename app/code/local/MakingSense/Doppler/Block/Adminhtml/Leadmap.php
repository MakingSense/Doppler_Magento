<?php

class MakingSense_Doppler_Block_Adminhtml_Leadmap extends Mage_Adminhtml_Block_Widget_Grid_Container {
	public function __construct (){
		$this->_controller = 'adminhtml_leadmap';
		$this->_blockGroup = 'makingsense_doppler';
			$this->_headerText = Mage::helper('makingsense_doppler')->__('Lead Mapping Manager');
		$this->_addButtonLabel = Mage::helper('makingsense_doppler')->__('Add Field');
	
		parent::__construct();
	}
}