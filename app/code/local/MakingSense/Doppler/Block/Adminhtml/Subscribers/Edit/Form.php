<?php

class MakingSense_Doppler_Block_Adminhtml_Subscribers_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
	protected function _prepareForm (){
		$model = Mage::registry('subscribers_data');
		
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl("*/*/save", array('id' => $this->getRequest()->getParam('id'))),
			'method' => 'post'
		));
		
        $fieldset = $form->addFieldset('subscribers_form', array(
			'legend' => Mage::helper('makingsense_doppler')->__('Subscribers information')
		));
		
		$fieldset->addField('name', 'text', array(
			'label'     => Mage::helper('makingsense_doppler')->__('Name'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'name',
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