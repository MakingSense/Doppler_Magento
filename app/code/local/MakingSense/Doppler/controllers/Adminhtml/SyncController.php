<?php

class MakingSense_Doppler_Adminhtml_SyncController extends Mage_Adminhtml_Controller_Action {

    protected function initAction (){
        $this->loadLayout()
            ->_setActiveMenu('makingsense_doppler/sync');

        return $this;
    }

    public function indexAction (){
        $this->initAction()
            ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_sync'))
            ->renderLayout();
    }

    public function newAction (){
        $this->_forward('edit');
    }

    public function editAction (){
        $id = $this->getRequest()->getParam('id');

        $model = Mage::getModel('makingsense_doppler/sync');
        if ($id){
            $model->load($id);

            if (!$model->getId()){
                $this->_getSession()->addError($this->__('Entry does not exist'));
                $this->_redirect('*/*/');
                return;
            }
        }

        Mage::register('sync_data', $model);

        $this->initAction()
            ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_sync_edit'))
            ->renderLayout();
    }

    public function deleteAction (){
        $id = $this->getRequest()->getParam('id');
        if ($id){
            try {
                $model = Mage::getModel('makingsense_doppler/sync')->load($id);
                if (!$model->getId()){
                    $this->_getSession()->addError("Entry $id does not exist");
                    $this->_redirect("*/*/");
                    return;
                }

                $model->delete();
                $this->_getSession()->addSuccess($this->__('Entry deleted.'));
            } catch (Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect("*/*/");
    }

    public function saveAction()
    {

        $data = $this->getRequest()->getPost();

        if ($data)
        {
            try {
                // Get cURL resource
                $ch = curl_init();

                // Set url
                curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/guarinogabriel@gmail.com/sync');

                // Set method
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

                // Set options
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                // Set headers
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        "Authorization: token 75D1DFD190CDC50AE95EAEAAB661F949",
                    ]
                );

                // Create body
                $body = '{ name: "' . $data['name'] . '" }';

                Mage::log($body, null,'body.log');

                // Set body
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

                // Send the request & save response to $resp
                $resp = curl_exec($ch);

                if(!$resp) {
                    Mage::log('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
                } else {

                    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    Mage::log("Response HTTP Status Code : " . $statusCode, null, 'doppler.log');
                    Mage::log("Response HTTP Body : " . $resp, null, 'doppler.log');

                    if ($statusCode == '201') {
                        $this->_getSession()->addSuccess($this->__('The entry has been sucessfully created.'));
                    } else {
                        $responseContent = json_decode($resp, true);
                        $this->_getSession()->addError($this->__('The following errors ocurred creating your entry: ' . $responseContent['title']));
                    }
                }

                // Close request to clear up some resources
                curl_close($ch);

            } catch (Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect("*/*/");
    }

    public function massDeleteAction (){
        $data = $this->getRequest()->getParam('sync');
        if (!is_array($data)){
            $this->_getSession()->addError(
                $this->__("Please select at least one record")
            );
        } else {
            try {
                foreach ($data as $id){
                    $sync = Mage::getModel('makingsense_doppler/sync')->load($id);
                    $sync->delete();
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