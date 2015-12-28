<?php

class MakingSense_Doppler_Block_Adminhtml_Subscribers extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct ()
	{
		$this->_controller = 'adminhtml_subscribers';
		$this->_blockGroup = 'makingsense_doppler';
		$this->_headerText = Mage::helper('makingsense_doppler')->__('Doppler Subscribers');
		$this->_addButtonLabel = Mage::helper('makingsense_doppler')->__('Set Default List');

		parent::__construct();
	}

}