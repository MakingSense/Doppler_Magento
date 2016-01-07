<?php
/**
 * Subscribers admin page controller
 *
 * @category    MakingSense
 * @package     Doppler
 * @author      Gabriel Guarino <guarinogabriel@gmail.com>
 */

class MakingSense_Doppler_Adminhtml_SubscribersController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Doppler lead mapping array
     *
     * @var null|array
     */
    protected $_leadMapping = null;

    /**
     * Customer attributes from mapped fields
     *
     * @var null|array
     */
    protected $_customerAttributes = null;

    /**
     * Set active menu
     */
    protected function initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('makingsense_doppler/subscribers');

        return $this;
    }

    /**
     * Load current customer
     */
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
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('makingsense_doppler/adminhtml_subscribers_grid')->toHtml()
        );
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

                $usernameValue = Mage::getStoreConfig('doppler/connection/username');
                $apiKeyValue = Mage::getStoreConfig('doppler/connection/key');

                if($usernameValue != '' && $apiKeyValue != '') {

                    // Get cURL resource
                    $ch = curl_init();

                    // Set url
                    curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue . '/lists/' . $data['doppler_list'] . '/subscribers');

                    // Set method
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

                    // Set options
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                    // Set headers
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        "Authorization: token " . $apiKeyValue,
                        "Content-Type: application/json",
                    ]);

                    // Get Doppler mapped fields from Magento
                    $leadmapCollection = Mage::getModel('makingsense_doppler/leadmap')->getCollection();

                    foreach ($leadmapCollection->getData() as $leadmap)
                    {
                        $this->_leadMapping[$leadmap['doppler_field_name']] = $leadmap['magento_field_name'];
                    }

                    // Load Magento customer from ID
                    $customer = Mage::getModel('customer/customer')->load($data['entity_id']);

                    // Load customer attributes from mapped fields
                    foreach ($this->_leadMapping as $field)
                    {
                        $this->_customerAttributes[$field] = $customer->getData($field);
                    }

                    /* Sample body format for API (add subscriber to list)
                     * {"email": "eeef1cba-0718-4b18-b68f-5e56adaa08b9@mailinator.com",
                        "fields": [ {name: "FIRSTNAME", value: "First Name"},
                                    {name: "LASTNAME", value: "Last Name"},
                                    {name: "GENDER", value: "N"},
                                    {name: "BIRTHDAY", value: "N"}]}
                    */

                    // Create body
                    $body = '{ "email": "' . $data['email'] . '", ';
                    $body .= ' "fields": [ ';

                    $mappedFieldsCount = count($this->_leadMapping);
                    $leadMappingArrayKeys = array_keys($this->_leadMapping);
                    $customerAttributesArrayKeys = array_keys($this->_customerAttributes);
                    $this->_apiRequestBodyArray = array();

                    for($i = 0; $i < $mappedFieldsCount; $i++)
                    {
                        $fieldName = $leadMappingArrayKeys[$i];
                        $customerAttributeValue = $this->_customerAttributes[$customerAttributesArrayKeys[$i]];
                        $body .= '{ name: "'. $fieldName .'", value: "'. $customerAttributeValue .'" }, ';
                    }

                    $body .= ']}';

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

                            // Set doppler_synced attribute to true since the customer was successfully exported
                            $customer = Mage::getModel('customer/customer');
                            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                            $customer->load($data['entity_id']);
                            if($customer->getId() > 1){
                                $customer->setDopplerSynced('1')->save();
                            }

                        } else {
                            $responseContent = json_decode($resp, true);
                            $this->_getSession()->addError($this->__('The following errors occurred processing your request: ' . $responseContent['title']));
                        }
                    }

                    // Close request to clear up some resources
                    curl_close($ch);
                }

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
     * MassExport action logic
     */
    public function massExportAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if(!is_array($customersIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s).'));

        } else {
            try {
                $exportedSuccessfully = 0;
                $exportedWithErrors = 0;
                foreach ($customersIds as $customerId) {
                    $dopplerListId = $this->getRequest()->getParam('list');

                    $usernameValue = Mage::getStoreConfig('doppler/connection/username');
                    $apiKeyValue = Mage::getStoreConfig('doppler/connection/key');

                    if ($usernameValue != '' && $apiKeyValue != '') {

                        // Load selected customer
                        $customer = Mage::getModel('customer/customer')->load($customerId);
                        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());

                        // Get cURL resource
                        $ch = curl_init();

                        // Set url
                        curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue . '/lists/' . $dopplerListId . '/subscribers');

                        // Set method
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

                        // Set options
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                        // Set headers
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                "Authorization: token " . $apiKeyValue,
                                "Content-Type: application/json",
                            ]
                        );

                        // Get Doppler mapped fields from Magento
                        $leadmapCollection = Mage::getModel('makingsense_doppler/leadmap')->getCollection();

                        foreach ($leadmapCollection->getData() as $leadmap)
                        {
                            $this->_leadMapping[$leadmap['doppler_field_name']] = $leadmap['magento_field_name'];
                        }

                        // Load customer attributes from mapped fields
                        foreach ($this->_leadMapping as $field)
                        {
                            $this->_customerAttributes[$field] = $customer->getData($field);
                        }

                        /* Sample body format for API (add subscriber to list)
                         * {"email": "eeef1cba-0718-4b18-b68f-5e56adaa08b9@mailinator.com",
                            "fields": [ {name: "FIRSTNAME", value: "First Name"},
                                        {name: "LASTNAME", value: "Last Name"},
                                        {name: "GENDER", value: "N"},
                                        {name: "BIRTHDAY", value: "N"}]}
                        */

                        // Create body
                        $body = '{ "email": "' . $customer->getEmail() . '", ';
                        $body .= ' "fields": [ ';

                        $mappedFieldsCount = count($this->_leadMapping);
                        $leadMappingArrayKeys = array_keys($this->_leadMapping);
                        $customerAttributesArrayKeys = array_keys($this->_customerAttributes);
                        $this->_apiRequestBodyArray = array();

                        for($i = 0; $i < $mappedFieldsCount; $i++)
                        {
                            $fieldName = $leadMappingArrayKeys[$i];
                            $customerAttributeValue = $this->_customerAttributes[$customerAttributesArrayKeys[$i]];
                            $body .= '{ name: "'. $fieldName .'", value: "'. $customerAttributeValue .'" }, ';
                        }

                        $body .= ']}';

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

                                // Set doppler_synced attribute to true since the customer was successfully exported
                                if ($customer->getId() > 1) {
                                    $customer->setDopplerSynced('1')->save();
                                }

                                $exportedSuccessfully++;

                            } else {
                                $responseContent = json_decode($resp, true);
                                $exportedWithErrors++;
                            }
                        }

                        // Close request to clear up some resources
                        curl_close($ch);
                    }
                }

                if ($exportedSuccessfully) {
                    $this->_getSession()->addSuccess($this->__('%d customer(s) successfully subscribed to the selected list', $exportedSuccessfully));
                }
                if ($exportedWithErrors) {
                    $this->_getSession()->addError($this->__('Some errors ocurred when exporting %d customer(s)', $exportedWithErrors));
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Validate admin user permissions
     */
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