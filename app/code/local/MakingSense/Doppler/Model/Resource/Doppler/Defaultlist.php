<?php
class MakingSense_Doppler_Model_Resource_Doppler_Defaultlist extends Mage_Core_Model_Resource_Db_Abstract
{
	public function _construct()
	{
		$this->_init('makingsense_doppler/doppler_defaultlist', 'listId');
	}

}