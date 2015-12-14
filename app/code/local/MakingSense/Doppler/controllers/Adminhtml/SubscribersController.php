<?php

class MakingSense_Doppler_Adminhtml_SubscribersController extends Mage_Adminhtml_Controller_Action {
	protected function initAction (){
		$this->loadLayout()
			 ->_setActiveMenu('makingsense_doppler/subscribers');
		
		return $this;
	}
	
	public function indexAction (){
		$this->initAction()
			 ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_subscribers'))
			 ->renderLayout();
	}
	
	public function newAction (){
		$this->_forward('edit');
	}
	
	public function editAction (){
		$id = $this->getRequest()->getParam('id');
		
		$model = Mage::getModel('makingsense_doppler/subscribers');
		if ($id){
			$model->load($id);
			
			if (!$model->getId()){
				$this->_getSession()->addError($this->__('Subscribers does not exist'));
				$this->_redirect('*/*/');
				return;
			}
		}
		
		Mage::register('subscribers_data', $model);
		
		$this->initAction()
			 ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_subscribers_edit'))
			 ->renderLayout();
	}
	
	public function deleteAction (){
		$id = $this->getRequest()->getParam('id');
		if ($id){
			try {
				$model = Mage::getModel('makingsense_doppler/subscribers')->load($id);
				if (!$model->getId()){
					$this->_getSession()->addError("Subscribers $id does not exist");
					$this->_redirect("*/*/");
					return;
				}
				
				$model->delete();
				$this->_getSession()->addSuccess($this->__('Subscribers deleted.'));
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
				$model = Mage::getModel('makingsense_doppler/subscribers');
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
		$data = $this->getRequest()->getParam('subscribers');
		if (!is_array($data)){
			$this->_getSession()->addError(
				$this->__("Please select at least one record")
			);
		} else {
			try {
				foreach ($data as $id){
					$subscribers = Mage::getModel('makingsense_doppler/subscribers')->load($id);
					$subscribers->delete();
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