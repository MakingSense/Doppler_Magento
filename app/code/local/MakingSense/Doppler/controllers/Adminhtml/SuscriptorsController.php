<?php

class MakingSense_Doppler_Adminhtml_SuscriptorsController extends Mage_Adminhtml_Controller_Action {
	protected function initAction (){
		$this->loadLayout()
			 ->_setActiveMenu('makingsense_doppler/suscriptors');
		
		return $this;
	}
	
	public function indexAction (){
		$this->initAction()
			 ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_suscriptors'))
			 ->renderLayout();
	}
	
	public function newAction (){
		$this->_forward('edit');
	}
	
	public function editAction (){
		$id = $this->getRequest()->getParam('id');
		
		$model = Mage::getModel('makingsense_doppler/suscriptors');
		if ($id){
			$model->load($id);
			
			if (!$model->getId()){
				$this->_getSession()->addError($this->__('Suscriptors does not exist'));
				$this->_redirect('*/*/');
				return;
			}
		}
		
		Mage::register('suscriptors_data', $model);
		
		$this->initAction()
			 ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_suscriptors_edit'))
			 ->renderLayout();
	}
	
	public function deleteAction (){
		$id = $this->getRequest()->getParam('id');
		if ($id){
			try {
				$model = Mage::getModel('makingsense_doppler/suscriptors')->load($id);
				if (!$model->getId()){
					$this->_getSession()->addError("Suscriptors $id does not exist");
					$this->_redirect("*/*/");
					return;
				}
				
				$model->delete();
				$this->_getSession()->addSuccess($this->__('Suscriptors deleted.'));
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
				$model = Mage::getModel('makingsense_doppler/suscriptors');
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
		$data = $this->getRequest()->getParam('suscriptors');
		if (!is_array($data)){
			$this->_getSession()->addError(
				$this->__("Please select at least one record")
			);
		} else {
			try {
				foreach ($data as $id){
					$suscriptors = Mage::getModel('makingsense_doppler/suscriptors')->load($id);
					$suscriptors->delete();
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