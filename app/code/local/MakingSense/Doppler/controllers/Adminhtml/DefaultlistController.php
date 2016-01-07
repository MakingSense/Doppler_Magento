<?php
/**
 * Defaultlist admin page controller
 *
 * @category    MakingSense
 * @package     Doppler
 * @author      Gabriel Guarino <guarinogabriel@gmail.com>
 */

class MakingSense_Doppler_Adminhtml_DefaultlistController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Set active menu
     */
    protected function initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('makingsense_doppler/defaultlist');

        return $this;
    }

    /**
     * Create block for index action
     */
    public function indexAction()
    {
        $this->initAction()
            ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_defaultlist'))
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
        $id = $this->getRequest()->getParam('id');

        $model = Mage::getModel('makingsense_doppler/defaultlist');
        if ($id){
            $model->load($id);

            if (!$model->getId()){
                $this->_getSession()->addError($this->__('Default list does not exist'));
                $this->_redirect('*/*/');
                return;
            }
        }

        Mage::register('defaultlist_data', $model);

        $this->initAction()
            ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_defaultlist_edit'))
            ->renderLayout();
    }

    /**
     * Delete action logic
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id){
            try {
                $list = Mage::getModel('makingsense_doppler/defaultlist')->load($id);
                if (!$list->getId()){
                    $this->_getSession()->addError("Default list %s does not exist", $id);
                    $this->_redirect("*/*/");
                    return;
                }

                $list->delete();

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

        $this->_redirect("*/*/");
    }


}