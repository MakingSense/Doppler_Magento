<?php

class MakingSense_Doppler_Block_Adminhtml_Leadmap_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

	public function __construct (){
		$this->_blockGroup = 'makingsense_doppler';
		$this->_controller = 'adminhtml_leadmap';

		parent::__construct();
	}
	
	public function getHeaderText (){
		return $this->__('Leadmap');
	}

}