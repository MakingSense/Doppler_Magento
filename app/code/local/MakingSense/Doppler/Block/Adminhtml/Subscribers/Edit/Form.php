<?php

class MakingSense_Doppler_Block_Adminhtml_Subscribers_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

	protected $_listsArray = null;

	protected function _prepareForm (){
		$model = Mage::registry('subscribers_data');

		if (!Mage::helper('makingsense_doppler')->testAPIConnection()) {
			return parent::_prepareForm();
		}

		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl("*/*/save", array('id' => $this->getRequest()->getParam('id'))),
			'method' => 'post'
		));

        $fieldset = $form->addFieldset('subscribers_form', array(
			'legend' => Mage::helper('makingsense_doppler')->__('Export customer to list')
		));

		if ($model->getId()){
			$fieldset->addField('firstname', 'text', array(
				'label'     => Mage::helper('makingsense_doppler')->__('First Name'),
				'required'  => false,
				'readonly' => true,
				'name'      => 'firstname',
				'class'		=> 'non-editable'
			));
			$fieldset->addField('lastname', 'text', array(
				'label'     => Mage::helper('makingsense_doppler')->__('Last Name'),
				'required'  => false,
				'readonly' => true,
				'name'      => 'lastname',
				'class'		=> 'non-editable'
			));
			$fieldset->addField('email', 'text', array(
				'label'     => Mage::helper('makingsense_doppler')->__('Email'),
				'required'  => false,
				'readonly' => true,
				'name'      => 'email',
				'class'		=> 'non-editable'
			));
		}

		$dopplerLists = $this->getDopplerLists();

		$fieldset->addField('doppler_list', 'select', array(
			'label'     => Mage::helper('makingsense_doppler')->__('Doppler List'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'doppler_list',
			'values' => $dopplerLists
		));

		if ($model->getId()){
			$fieldset->addField('id', 'hidden', array(
				'name' => 'id',
            ));
		}
		
		$form->setUseContainer(true);
		$form->setValues($model->getData());
		$this->setForm($form);
		
		return parent::_prepareForm();
	}

	public function getDopplerLists()
	{
		$this->_listsArray = array();

		// Get cURL resource
		$ch = curl_init();

		// Set url
		curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/guarinogabriel@gmail.com/lists');

		// Set method
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

		// Set options
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		// Set headers
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
				"Authorization: token 75D1DFD190CDC50AE95EAEAAB661F949",
			]
		);

		// Send the request & save response to $resp
		$resp = curl_exec($ch);

		if(!$resp) {
			Mage::log('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
		} else {

			$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			Mage::log("Response HTTP Status Code : " . $statusCode, null,'doppler.log');
			Mage::log("Response HTTP Body : " . $resp, null,'doppler.log');

			$responseContent = json_decode($resp, true);
			$listsResponseArray = $responseContent['items'];

			foreach ($listsResponseArray as $list) {
				$fieldName = $list['name'];
				$listId = $list['listId'];
				$this->_listsArray[$listId] = $fieldName;
			}

		}

		// Close request to clear up some resources
		curl_close($ch);

		return $this->_listsArray;
	}
}