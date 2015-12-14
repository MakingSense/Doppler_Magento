<?php

class MakingSense_Doppler_Model_Subscribers extends Mage_Core_Model_Abstract {

	public function _construct (){
		parent::_construct();
		$this->_init('makingsense_doppler/doppler_subscribers');
	}

}