<?php

class MakingSense_Doppler_Block_Adminhtml_Suscriptors extends Mage_Adminhtml_Block_Widget_Grid_Container {
	public function __construct (){
		$this->_controller = 'adminhtml_suscriptors';
		$this->_blockGroup = 'makingsense_doppler';
		$this->_headerText = Mage::helper('makingsense_doppler')->__('Suscriptors Manager');
		$this->_addButtonLabel = Mage::helper('makingsense_doppler')->__('Add Suscriptors');
	
		parent::__construct();
	}
}