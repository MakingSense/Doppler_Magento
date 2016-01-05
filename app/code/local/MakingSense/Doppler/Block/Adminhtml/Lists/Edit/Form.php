<?php
/**
 * Lists edit form
 *
 * @category    MakingSense
 * @package     Doppler
 * @author      Gabriel Guarino <guarinogabriel@gmail.com>
 */
class MakingSense_Doppler_Block_Adminhtml_Lists_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * Prepare form before rendering HTML
	 *
	 * @return MakingSense_Doppler_Block_Adminhtml_Lists_Edit_Form
	 */
	protected function _prepareForm()
	{

		$model = Mage::registry('lists_data');
		
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl("*/*/save", array('id' => $this->getRequest()->getParam('id'))),
			'method' => 'post'
		));

        $fieldset = $form->addFieldset('lists_form', array(
			'legend' => Mage::helper('makingsense_doppler')->__('List information')
		));

		$fieldset->addField('name', 'text', array(
			'label'     => Mage::helper('makingsense_doppler')->__('Name'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'name',
		));

		if ($this->getRequest()->getParam('id'))
		{
			$fieldset->addField('list_id', 'text', array(
				'label'     => Mage::helper('makingsense_doppler')->__('List ID'),
				'required'  => false,
				'readonly' => true,
				'name'      => 'list_id',
				'class'		=> 'non-editable'
			));

			$fieldset->addField('creation_date', 'text', array(
				'label'     => Mage::helper('makingsense_doppler')->__('Creation Date'),
				'required'  => false,
				'readonly' => true,
				'name'      => 'creation_date',
				'class'		=> 'non-cleditable'
			));

			$fieldset->addField('subscribers_count', 'text', array(
				'label'     => Mage::helper('makingsense_doppler')->__('Subscribers Count'),
				'required'  => false,
				'readonly' => true,
				'name'      => 'subscribers_count',
				'class'		=> 'non-editable'
			));

		}

		if ($model->getId())
		{
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