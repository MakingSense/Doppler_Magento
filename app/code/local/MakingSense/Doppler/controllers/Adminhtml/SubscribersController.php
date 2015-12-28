<?php

class MakingSense_Doppler_Adminhtml_SubscribersController extends Mage_Adminhtml_Controller_Action {

    protected function initAction (){
        $this->loadLayout()
            ->_setActiveMenu('makingsense_doppler/subscribers');

        return $this;
    }

    protected function _initCustomer($idFieldName = 'id')
    {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Subscribers'));

        $customerId = (int) $this->getRequest()->getParam($idFieldName);
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }

    /**
     * Customers list action
     */
    public function indexAction()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Subscribers'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('doppler/subscribers');

        /**
         * Append customers block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('makingsense_doppler/adminhtml_subscribers', 'subscribers')
        );

        /**
         * Add breadcrumb item
         */
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Customers'), Mage::helper('adminhtml')->__('Customers'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Subscribers'), Mage::helper('adminhtml')->__('Manage Subscribers'));

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Customer edit action
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

            $model = Mage::getModel('customer/customer');
            if ($id){
                $model->load($id);

                if (!$model->getId()){
                    $this->_getSession()->addError($this->__('Mapping does not exist'));
                    $this->_redirect('*/*/');
                    return;
                }
            }

            Mage::register('subscribers_data', $model);

            $this->initAction()
                ->_addContent($this->getLayout()->createBlock('makingsense_doppler/adminhtml_subscribers_edit'))
                ->renderLayout();
        }
    }

    /**
     * Create new customer action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Delete customer action
     */
    public function deleteAction()
    {
        $this->_initCustomer();
        $customer = Mage::registry('current_customer');
        if ($customer->getId()) {
            try {
                $customer->load($customer->getId());
                $customer->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The customer has been deleted.'));
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/customer');
    }

    /**
     * Save customer action
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost();

        if ($data) {
            try {
                // Get cURL resource
                $ch = curl_init();

                // Set url
                curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/guarinogabriel@gmail.com/lists/' . $data['doppler_list'] . '/subscribers');

                Mage::log('https://restapi.fromdoppler.com/accounts/guarinogabriel@gmail.com/lists/' . $data['doppler_list'] . '/subscribers', null,'curlOpt.log');

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
                $body = '{ email: "' . $data['email'] . '" }';

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
                        $this->_getSession()->addSuccess($this->__('The customer has been subscribed to the selected list'));
                    } else {
                        $responseContent = json_decode($resp, true);
                        $this->_getSession()->addError($this->__('The following errors occurred processing your request: ' . $responseContent['title']));
                    }
                }

                // Close request to clear up some resources
                curl_close($ch);

            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect("*/*/");
    }

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'customers.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/customer_grid')
            ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'customers.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/customer_grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Prepare file download response
     *
     * @todo remove in 1.3
     * @deprecated please use $this->_prepareDownloadResponse()
     * @see Mage_Adminhtml_Controller_Action::_prepareDownloadResponse()
     * @param string $fileName
     * @param string $content
     * @param string $contentType
     */
    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $this->_prepareDownloadResponse($fileName, $content, $contentType);
    }

    /**
     * Customer orders grid
     *
     */
    public function ordersAction() {
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Customer last orders grid for ajax
     *
     */
    public function lastOrdersAction() {
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Customer newsletter grid
     *
     */
    public function newsletterAction()
    {
        $this->_initCustomer();
        $subscriber = Mage::getModel('newsletter/subscriber')
            ->loadByCustomer(Mage::registry('current_customer'));

        Mage::register('subscriber', $subscriber);
        $this->loadLayout();
        $this->renderLayout();
    }

    public function wishlistAction()
    {
        $this->_initCustomer();
        $customer = Mage::registry('current_customer');
        if ($customer->getId()) {
            if($itemId = (int) $this->getRequest()->getParam('delete')) {
                try {
                    Mage::getModel('wishlist/item')->load($itemId)
                        ->delete();
                }
                catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        $this->getLayout()->getUpdate()
            ->addHandle(strtolower($this->getFullActionName()));
        $this->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();

        $this->renderLayout();
    }

    /**
     * Customer last view wishlist for ajax
     *
     */
    public function viewWishlistAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * [Handle and then] get a cart grid contents
     *
     * @return string
     */
    public function cartAction()
    {
        $this->_initCustomer();
        $websiteId = $this->getRequest()->getParam('website_id');

        // delete an item from cart
        $deleteItemId = $this->getRequest()->getPost('delete');
        if ($deleteItemId) {
            $quote = Mage::getModel('sales/quote')
                ->setWebsite(Mage::app()->getWebsite($websiteId))
                ->loadByCustomer(Mage::registry('current_customer'));
            $item = $quote->getItemById($deleteItemId);
            if ($item && $item->getId()) {
                $quote->removeItem($deleteItemId);
                $quote->collectTotals()->save();
            }
        }

        $this->loadLayout();
        $this->getLayout()->getBlock('admin.customer.view.edit.cart')->setWebsiteId($websiteId);
        $this->renderLayout();
    }

    /**
     * Get shopping cart to view only
     *
     */
    public function viewCartAction()
    {
        $this->_initCustomer();
        $this->loadLayout()
            ->getLayout()
            ->getBlock('admin.customer.view.cart')
            ->setWebsiteId($this->getRequest()->getParam('website_id'));
        $this->renderLayout();
    }

    /**
     * Get shopping carts from all websites for specified client
     *
     */
    public function cartsAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Get customer's product reviews list
     *
     */
    public function productReviewsAction()
    {
        $this->_initCustomer();
        $this->loadLayout()
            ->getLayout()
            ->getBlock('admin.customer.reviews')
            ->setCustomerId(Mage::registry('current_customer')->getId())
            ->setUseAjax(true);
        $this->renderLayout();
    }

    /**
     * Get customer's tags list
     *
     */
    public function productTagsAction()
    {
        $this->_initCustomer();
        $this->loadLayout()
            ->getLayout()
            ->getBlock('admin.customer.tags')
            ->setCustomerId(Mage::registry('current_customer')->getId())
            ->setUseAjax(true);
        $this->renderLayout();
    }

    public function tagGridAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->getLayout()->getBlock('admin.customer.tags')->setCustomerId(
            Mage::registry('current_customer')
        );
        $this->renderLayout();
    }

    public function validateAction()
    {
        $response       = new Varien_Object();
        $response->setError(0);
        $websiteId      = Mage::app()->getStore()->getWebsiteId();
        $accountData    = $this->getRequest()->getPost('account');

        $customer = Mage::getModel('customer/customer');
        $customerId = $this->getRequest()->getParam('id');
        if ($customerId) {
            $customer->load($customerId);
            $websiteId = $customer->getWebsiteId();
        } else if (isset($accountData['website_id'])) {
            $websiteId = $accountData['website_id'];
        }

        /* @var $customerForm Mage_Customer_Model_Form */
        $customerForm = Mage::getModel('customer/form');
        $customerForm->setEntity($customer)
            ->setFormCode('adminhtml_customer')
            ->setIsAjaxRequest(true)
            ->ignoreInvisible(false)
        ;

        $data   = $customerForm->extractData($this->getRequest(), 'account');
        $errors = $customerForm->validateData($data);
        if ($errors !== true) {
            foreach ($errors as $error) {
                $this->_getSession()->addError($error);
            }
            $response->setError(1);
        }

        # additional validate email
        if (!$response->getError()) {
            # Trying to load customer with the same email and return error message
            # if customer with the same email address exisits
            $checkCustomer = Mage::getModel('customer/customer')
                ->setWebsiteId($websiteId);
            $checkCustomer->loadByEmail($accountData['email']);
            if ($checkCustomer->getId() && ($checkCustomer->getId() != $customer->getId())) {
                $response->setError(1);
                $this->_getSession()->addError(
                    Mage::helper('adminhtml')->__('Customer with the same email already exists.')
                );
            }
        }

        $addressesData = $this->getRequest()->getParam('address');
        if (is_array($addressesData)) {
            /* @var $addressForm Mage_Customer_Model_Form */
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('adminhtml_customer_address')->ignoreInvisible(false);
            foreach (array_keys($addressesData) as $index) {
                if ($index == '_template_') {
                    continue;
                }
                $address = $customer->getAddressItemById($index);
                if (!$address) {
                    $address   = Mage::getModel('customer/address');
                }

                $requestScope = sprintf('address/%s', $index);
                $formData = $addressForm->setEntity($address)
                    ->extractData($this->getRequest(), $requestScope);

                $errors = $addressForm->validateData($formData);
                if ($errors !== true) {
                    foreach ($errors as $error) {
                        $this->_getSession()->addError($error);
                    }
                    $response->setError(1);
                }
            }
        }

        if ($response->getError()) {
            $this->_initLayoutMessages('adminhtml/session');
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }

    public function massSubscribeAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if(!is_array($customersIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s).'));

        } else {
            try {
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $customer->setIsSubscribed(true);
                    $customer->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were updated.', count($customersIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massUnsubscribeAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if(!is_array($customersIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s).'));
        } else {
            try {
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $customer->setIsSubscribed(false);
                    $customer->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were updated.', count($customersIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    public function massDeleteAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if(!is_array($customersIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s).'));
        } else {
            try {
                $customer = Mage::getModel('customer/customer');
                foreach ($customersIds as $customerId) {
                    $customer->reset()
                        ->load($customerId)
                        ->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were deleted.', count($customersIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    public function massAssignGroupAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if(!is_array($customersIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s).'));
        } else {
            try {
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $customer->setGroupId($this->getRequest()->getParam('group'));
                    $customer->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were updated.', count($customersIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    public function viewfileAction()
    {
        $file   = null;
        $plain  = false;
        if ($this->getRequest()->getParam('file')) {
            // download file
            $file   = Mage::helper('core')->urlDecode($this->getRequest()->getParam('file'));
        } else if ($this->getRequest()->getParam('image')) {
            // show plain image
            $file   = Mage::helper('core')->urlDecode($this->getRequest()->getParam('image'));
            $plain  = true;
        } else {
            return $this->norouteAction();
        }

        $path = Mage::getBaseDir('media') . DS . 'customer';

        $ioFile = new Varien_Io_File();
        $ioFile->open(array('path' => $path));
        $fileName   = $ioFile->getCleanPath($path . $file);
        $path       = $ioFile->getCleanPath($path);

        if ((!$ioFile->fileExists($fileName) || strpos($fileName, $path) !== 0)
            && !Mage::helper('core/file_storage')->processStorageFile(str_replace('/', DS, $fileName))
        ) {
            return $this->norouteAction();
        }

        if ($plain) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            switch (strtolower($extension)) {
                case 'gif':
                    $contentType = 'image/gif';
                    break;
                case 'jpg':
                    $contentType = 'image/jpeg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    break;
            }

            $ioFile->streamOpen($fileName, 'r');
            $contentLength = $ioFile->streamStat('size');
            $contentModify = $ioFile->streamStat('mtime');

            $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', $contentLength)
                ->setHeader('Last-Modified', date('r', $contentModify))
                ->clearBody();
            $this->getResponse()->sendHeaders();

            while (false !== ($buffer = $ioFile->streamRead())) {
                echo $buffer;
            }
        } else {
            $name = pathinfo($fileName, PATHINFO_BASENAME);
            $this->_prepareDownloadResponse($name, array(
                'type'  => 'filename',
                'value' => $fileName
            ));
        }

        exit();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array
     * @return array
     */
    protected function _filterPostData($data)
    {
        $data['account'] = $this->_filterDates($data['account'], array('dob'));
        return $data;
    }
}