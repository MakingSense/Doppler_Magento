<?php
/**
 * Defaultlist edit form
 *
 * @category    MakingSense
 * @package     Doppler
 * @author      Gabriel Guarino <guarinogabriel@gmail.com>
 */
class MakingSense_Doppler_Block_Adminhtml_Defaultlist_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * Prepare form before rendering HTML
	 *
	 * @return MakingSense_Doppler_Block_Adminhtml_Lists_Edit_Form
	 */
	protected function _prepareForm()
	{

		$model = Mage::registry('defaultlist_data');

		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl("*/*/save", array('listId' => $this->getRequest()->getParam('listId'))),
			'method' => 'post'
		));

        $fieldset = $form->addFieldset('defaultlist_form', array(
			'legend' => Mage::helper('makingsense_doppler')->__('List information')
		));

		if ($model->getListId())
		{
			$fieldset->addField('listId', 'hidden', array(
				'name' => 'listId',
            ));
		}

		$form->setUseContainer(true);
		$form->setValues($model->getData());
		$this->setForm($form);

		return parent::_prepareForm();

	}


}