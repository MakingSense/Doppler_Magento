<?php

class MakingSense_Doppler_Block_Adminhtml_Leadmap_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

	protected $_fieldsArray = null;

	protected function _prepareForm (){
		$model = Mage::registry('leadmap_data');
		
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl("*/*/save", array('id' => $this->getRequest()->getParam('id'))),
			'method' => 'post'
		));
		
        $fieldset = $form->addFieldset('leadmap_form', array(
			'legend' => Mage::helper('makingsense_doppler')->__('Leadmap information')
		));

		$dopplerFields = $this->getDopplerFields();

		$fieldset->addField('doppler_field_name', 'select', array(
			'label'     => Mage::helper('makingsense_doppler')->__('Doppler Field Name'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'doppler_field_name',
			'values' => $dopplerFields
		));

		$magentoAttributes = $this->getCustomerAttributes();

		$fieldset->addField('magento_field_name', 'select', array(
			'label'     => Mage::helper('makingsense_doppler')->__('Magento Field Name'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'magento_field_name',
			'values' => $magentoAttributes,
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

	public function getCustomerAttributes()
	{
		$attributes = Mage::getModel('customer/entity_attribute_collection')
			->addVisibleFilter();
		$result = array();
		foreach ($attributes as $attribute) {
			if (($label = $attribute->getName()))
				$result[$label] = $label;
		}
		return $result;
	}

	public function getDopplerFields()
	{
		$this->_fieldsArray = array();

		// Get cURL resource
		$ch = curl_init();

		// Set url
		curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/guarinogabriel@gmail.com/fields');

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
			$fieldsResponseArray = $responseContent['items'];

			foreach ($fieldsResponseArray as $field) {
				$fieldName = $field['name'];
				$this->_fieldsArray[$fieldName] = $fieldName;
			}

		}

		// Close request to clear up some resources
		curl_close($ch);

		return $this->_fieldsArray;
	}
}