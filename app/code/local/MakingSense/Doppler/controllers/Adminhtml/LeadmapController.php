<?php
/**
 * Leadmap admin page controller
 *
 * @category    MakingSense
 * @package     Doppler
 * @author      Gabriel Guarino <guarinogabriel@gmail.com>
 */

class MakingSense_Doppler_Adminhtml_LeadmapController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Set active menu
     */
    protected function initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('makingsense_doppler/leadmap');

        return $this;
    }

    /**
     * Create block for index action
     */
    public function indexAction()
    {

        $this->initAction()
            ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_leadmap'))
            ->renderLayout();
    }

    /**
     * Forward new to edit action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit action logic
     */
    public function editAction()
    {
        if (!Mage::helper('makingsense_doppler')->testAPIConnection()) {
            Mage::getSingleton('core/session')->addError($this->__('The Doppler API is not currently available, please try later'));

            $this->initAction()
                ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_leadmap_edit'))
                ->renderLayout();
        } else {

            $id = $this->getRequest()->getParam('id');

            $model = Mage::getModel('makingsense_doppler/leadmap');
            if ($id){
                $model->load($id);

                if (!$model->getId()){
                    $this->_getSession()->addError($this->__('Mapping does not exist'));
                    $this->_redirect('*/*/');
                    return;
                }
            }

            Mage::register('leadmap_data', $model);

            $this->initAction()
                ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_leadmap_edit'))
                ->renderLayout();
        }

    }

    /**
     * Delete action logic
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id){
            try {
                $model = Mage::getModel('makingsense_doppler/leadmap')->load($id);
                if (!$model->getId()){
                    $this->_getSession()->addError("Leadmap with id '%s' does not exist", $id);
                    $this->_redirect("*/*/");
                    return;
                }

                $model->delete();
                $this->_getSession()->addSuccess($this->__('Leadmap deleted'));
            } catch (Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect("*/*/");
    }

    /**
     * Save action logic
     */
    public function saveAction()
    {

        $data = $this->getRequest()->getPost();

        if ($data){
            try {
                // Validate that there is no attribute already associated with this Doppler field
                $fieldAlreadyExist = false;

                $mappedFields = Mage::getModel('makingsense_doppler/leadmap')->getCollection()->getData();

                $savedDopplerFieldName = '';

                foreach ($mappedFields as $field) {

                    // If editing mapping
                    if (array_key_exists('id', $data)) {
                        if ($field['id'] != $data['id']) {
                            $dopplerFieldName = $field['doppler_field_name'];
                            $savedDopplerFieldName = $data['doppler_field_name'];

                            if ($dopplerFieldName == $savedDopplerFieldName) {
                                $fieldAlreadyExist = true;
                            }
                        }
                    // If creating new mapping
                    } else {
                        $dopplerFieldName = $field['doppler_field_name'];
                        $savedDopplerFieldName = $data['doppler_field_name'];

                        if ($dopplerFieldName == $savedDopplerFieldName) {
                            $fieldAlreadyExist = true;
                        }
                    }
                }

                if (!$fieldAlreadyExist) {
                    $model = Mage::getModel('makingsense_doppler/leadmap');
                    $model->setData($data);
                    $model->save();

                    $this->_getSession()->addSuccess($this->__('Saved'));
                } else {
                    $this->_getSession()->addError($this->__('There is already a Magento attribute associated with the following Doppler field: %s', $savedDopplerFieldName));
                }

            } catch (Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect("*/*/");
    }

    /**
     * MassDelete action logic
     */
    public function massDeleteAction()
    {
        $data = $this->getRequest()->getParam('leadmap');
        if (!is_array($data)){
            $this->_getSession()->addError(
                $this->__("Please select at least one record")
            );
        } else {
            try {
                foreach ($data as $id){
                    $leadmap = Mage::getModel('makingsense_doppler/leadmap')->load($id);
                    $leadmap->delete();
                }

                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been deleted', count($data))
                );
            } catch (Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect("*/*/");
    }
}