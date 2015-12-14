<?php

class MakingSense_Doppler_Adminhtml_LeadmapController extends Mage_Adminhtml_Controller_Action {
    protected function initAction (){
        $this->loadLayout()
            ->_setActiveMenu('makingsense_doppler/leadmap');

        return $this;
    }

    public function indexAction (){
        $this->initAction()
            ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_leadmap'))
            ->renderLayout();
    }

    public function newAction (){
        $this->_forward('edit');
    }

    public function editAction (){
        $id = $this->getRequest()->getParam('id');

        $model = Mage::getModel('makingsense_doppler/leadmap');
        if ($id){
            $model->load($id);

            if (!$model->getId()){
                $this->_getSession()->addError($this->__('leadmap does not exist'));
                $this->_redirect('*/*/');
                return;
            }
        }

        Mage::register('leadmap_data', $model);

        $this->initAction()
            ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_leadmap_edit'))
            ->renderLayout();
    }

    public function deleteAction (){
        $id = $this->getRequest()->getParam('id');
        if ($id){
            try {
                $model = Mage::getModel('makingsense_doppler/leadmap')->load($id);
                if (!$model->getId()){
                    $this->_getSession()->addError("leadmap $id does not exist");
                    $this->_redirect("*/*/");
                    return;
                }

                $model->delete();
                $this->_getSession()->addSuccess($this->__('leadmap deleted.'));
            } catch (Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect("*/*/");
    }

    public function saveAction (){
        $data = $this->getRequest()->getPost();

        if ($data){
            try {
                $model = Mage::getModel('makingsense_doppler/leadmap');
                $model->setData($data);
                $model->save();

                $this->_getSession()->addSuccess($this->__('Saved.'));
            } catch (Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect("*/*/");
    }

    public function massDeleteAction (){
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
                    $this->__('Total of %d record(s) have been deleted.', count($data))
                );
            } catch (Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect("*/*/");
    }
}