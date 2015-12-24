<?php

class MakingSense_Doppler_Adminhtml_ListsController extends Mage_Adminhtml_Controller_Action {

    protected function initAction (){
        $this->loadLayout()
            ->_setActiveMenu('makingsense_doppler/lists');

        return $this;
    }

    public function indexAction (){

        if (!Mage::helper('makingsense_doppler')->testAPIConnection()) {
            Mage::getSingleton('core/session')->addError($this->__('The Doppler API is not currently available, please try later'));
        } else {
            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/guarinogabriel@gmail.com/lists');

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: token 75D1DFD190CDC50AE95EAEAAB661F949",
                ]
            );

            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if(!$resp) {
                Mage::log('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
            } else {

                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                Mage::log("Response HTTP Status Code : " . $statusCode, null, 'doppler.log');
                Mage::log("Response HTTP Body : " . $resp, null, 'doppler.log');

                $responseContent = json_decode($resp, true);

                if ($statusCode == '200') {

                    $model = Mage::getModel('makingsense_doppler/lists');

                    // First, remove the old Doppler lists
                    foreach ($model->getCollection() as $list) {
                        $model->load($list->getId())->delete();
                    }

                    // Then, store all list from latest API call

                    $fieldsResponseArray = $responseContent['items'];

                    foreach ($fieldsResponseArray as $field)
                    {
                        $data = array();


                        $data['name'] = $field['name'];
                        $data['list_id'] = $field['listId'];
                        $data['status'] = $field['currentStatus'];
                        $data['subscribers_count'] = $field['subscribersCount'];
                        $data['creation_date'] = $field['creationDate'];

                        $model->setData($data);
                        $model->save();
                    }

                } else {
                    $this->_getSession()->addError($this->__('The following errors occurred creating your list: %s', $responseContent['title']));
                }
            }

            // Close request to clear up some resources
            curl_close($ch);
        }

        $this->initAction()
            ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_lists'))
            ->renderLayout();
    }

    public function newAction (){
        $this->_forward('edit');
    }

    public function editAction (){
        $id = $this->getRequest()->getParam('id');

        $model = Mage::getModel('makingsense_doppler/lists');
        if ($id){
            $model->load($id);

            if (!$model->getId()){
                $this->_getSession()->addError($this->__('List does not exist'));
                $this->_redirect('*/*/');
                return;
            }
        }

        Mage::register('lists_data', $model);

        $this->initAction()
            ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_lists_edit'))
            ->renderLayout();
    }

    public function deleteAction (){
        $id = $this->getRequest()->getParam('id');
        if ($id){
            try {
                $list = Mage::getModel('makingsense_doppler/lists')->load($id);
                if (!$list->getId()){
                    $this->_getSession()->addError("List %s does not exist", $id);
                    $this->_redirect("*/*/");
                    return;
                }

                // Get cURL resource
                $ch = curl_init();

                // Set url
                curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/guarinogabriel@gmail.com/lists/' . $list->getListId());

                // Set method
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

                // Set options
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                // Set headers
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        "Authorization: token 75D1DFD190CDC50AE95EAEAAB661F949",
                        "Content-Type: application/json",
                    ]
                );


                // Send the request & save response to $resp
                $resp = curl_exec($ch);

                if(!$resp) {
                    Mage::log('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
                } else {

                    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    Mage::log("Response HTTP Status Code : " . $statusCode, null, 'doppler.log');
                    Mage::log("Response HTTP Body : " . $resp, null, 'doppler.log');

                    if ($statusCode == '200') {
                        $this->_getSession()->addSuccess($this->__("The list '%s' has been successfully removed", $list->getName()));
                    } else {
                        $responseContent = json_decode($resp, true);
                        $this->_getSession()->addError($this->__('The following errors occurred removing your list: ' . $responseContent['title']));
                    }
                }

                // Close request to clear up some resources
                curl_close($ch);

                $list->delete();
            } catch (Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect("*/*/");
    }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost();

        if ($data) {
            // If we are editing a list, then save the new list name
            if (array_key_exists('id', $data)) {
                try {
                    // Get cURL resource
                    $ch = curl_init();

                    // Set url
                    curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/guarinogabriel@gmail.com/lists/' . $data['list_id']);

                    // Set method
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

                    // Set options
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                    // Set headers
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            "Authorization: token 75D1DFD190CDC50AE95EAEAAB661F949",
                            "Content-Type: application/json",
                        ]
                    );

                    // Create body
                    $body = '{ name: "' . $data['name'] . '" }';

                    // Set body
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

                    // Send the request & save response to $resp
                    $resp = curl_exec($ch);

                    if (!$resp) {
                        Mage::log('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
                    } else {

                        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                        Mage::log("Response HTTP Status Code : " . $statusCode, null, 'doppler.log');
                        Mage::log("Response HTTP Body : " . $resp, null, 'doppler.log');

                        if ($statusCode == '200') {
                            $this->_getSession()->addSuccess($this->__('The changes have been saved'));
                        } else {
                            $responseContent = json_decode($resp, true);
                            $this->_getSession()->addError($this->__('The following errors occurred creating your list: ' . $responseContent['title']));
                        }
                    }

                    // Close request to clear up some resources
                    curl_close($ch);

                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
            // Else, save the new list
            else {
                try {
                    // Get cURL resource
                    $ch = curl_init();

                    // Set url
                    curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/guarinogabriel@gmail.com/lists');

                    // Set method
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

                    // Set options
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                    // Set headers
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            "Authorization: token 75D1DFD190CDC50AE95EAEAAB661F949",
                            "Content-Type: application/json",
                        ]
                    );

                    // Create body
                    $body = '{ name: "' . $data['name'] . '" }';

                    Mage::log($body, null, 'body.log');

                    // Set body
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

                    // Send the request & save response to $resp
                    $resp = curl_exec($ch);

                    if (!$resp) {
                        Mage::log('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
                    } else {

                        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                        Mage::log("Response HTTP Status Code : " . $statusCode, null, 'doppler.log');
                        Mage::log("Response HTTP Body : " . $resp, null, 'doppler.log');

                        if ($statusCode == '201') {
                            $this->_getSession()->addSuccess($this->__('The list has been successfully created'));
                        } else {
                            $responseContent = json_decode($resp, true);
                            $this->_getSession()->addError($this->__('The following errors occurred creating your list: ' . $responseContent['title']));
                        }
                    }

                    // Close request to clear up some resources
                    curl_close($ch);

                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }

        $this->_redirect("*/*/");
    }

    public function massDeleteAction (){
        $data = $this->getRequest()->getParam('lists');
        if (!is_array($data)){
            $this->_getSession()->addError(
                $this->__("Please select at least one record")
            );
        } else {
            try {
                foreach ($data as $id){
                    $list = Mage::getModel('makingsense_doppler/lists')->load($id);

                    // Get cURL resource
                    $ch = curl_init();

                    // Set url
                    curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/guarinogabriel@gmail.com/lists/' . $list->getListId());

                    // Set method
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

                    // Set options
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                    // Set headers
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            "Authorization: token 75D1DFD190CDC50AE95EAEAAB661F949",
                            "Content-Type: application/json",
                        ]
                    );


                    // Send the request & save response to $resp
                    $resp = curl_exec($ch);

                    if(!$resp) {
                        Mage::log('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
                    } else {

                        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                        Mage::log("Response HTTP Status Code : " . $statusCode, null, 'doppler.log');
                        Mage::log("Response HTTP Body : " . $resp, null, 'doppler.log');

                        if ($statusCode == '200') {
                            $this->_getSession()->addSuccess($this->__("The list '%s' has been successfully removed", $list->getName()));
                        } else {
                            $responseContent = json_decode($resp, true);
                            $this->_getSession()->addError($this->__('The following errors occurred removing your list: ' . $responseContent['title']));
                        }
                    }

                    // Close request to clear up some resources
                    curl_close($ch);


                    $list->delete();
                }
            } catch (Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect("*/*/");
    }
}