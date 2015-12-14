<?php

class MakingSense_Doppler_Block_Adminhtml_Leadmap_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

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
		
		$fieldset->addField('doppler_field_name', 'text', array(
			'label'     => Mage::helper('makingsense_doppler')->__('Doppler Field Name'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'doppler_field_name',
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
				$result[$attribute->getName()] = $label;
		}
		return $result;
	}
}