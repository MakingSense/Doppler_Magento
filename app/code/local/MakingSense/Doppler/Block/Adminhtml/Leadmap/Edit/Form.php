<?php
/**
 * Leadmap edit form
 *
 * @category    MakingSense
 * @package     Doppler
 * @author      Gabriel Guarino <guarinogabriel@gmail.com>
 */
class MakingSense_Doppler_Block_Adminhtml_Leadmap_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * Prepare form before rendering HTML
	 *
	 * @return MakingSense_Doppler_Block_Adminhtml_Leadmap_Edit_Form
	 */
	protected function _prepareForm()
	{
		$model = Mage::registry('leadmap_data');

		if (!Mage::helper('makingsense_doppler')->testAPIConnection()) {
			return parent::_prepareForm();
		}

		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl("*/*/save", array('id' => $this->getRequest()->getParam('id'))),
			'method' => 'post'
		));
		
        $fieldset = $form->addFieldset('leadmap_form', array(
			'legend' => Mage::helper('makingsense_doppler')->__('Leadmap information')
		));

		$dopplerFields = Mage::helper('makingsense_doppler')->getDopplerFields();

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

	/**
	* Get all available customer attributes
	*
	* @return array
	*/
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
}