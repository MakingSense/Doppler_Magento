<?php

class MakingSense_Doppler_Block_Adminhtml_Lists_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

	protected $_fieldsArray = null;

	protected function _prepareForm (){

		$model = Mage::registry('lists_data');
		
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl("*/*/save", array('id' => $this->getRequest()->getParam('id'))),
			'method' => 'post'
		));
		
        $fieldset = $form->addFieldset('lists_form', array(
			'legend' => Mage::helper('makingsense_doppler')->__('List information')
		));

		$fieldset->addField('list_id', 'hidden', array(
			'label'     => Mage::helper('makingsense_doppler')->__('List ID'),
			'required'  => false,
			'name'      => 'list_id',
		));

		$fieldset->addField('name', 'text', array(
			'label'     => Mage::helper('makingsense_doppler')->__('Name'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'name',
		));

		$fieldset->addField('creation_date', 'hidden', array(
			'label'     => Mage::helper('makingsense_doppler')->__('Creation Date'),
			'required'  => false,
			'name'      => 'creation_date',
		));

		$fieldset->addField('subscribers_count', 'hidden', array(
			'label'     => Mage::helper('makingsense_doppler')->__('Subscribers Count'),
			'required'  => false,
			'name'      => 'subscribers_count',
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


}