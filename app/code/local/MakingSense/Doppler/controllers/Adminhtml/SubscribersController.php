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
                // Load customer
                $customer = Mage::getModel('customer/customer');
                $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                $customer->load($data['entity_id']);

                // Export customer to Doppler
                $wasCustomerSubscribed = Mage::helper('makingsense_doppler')->exportCustomerToDoppler($customer, $data['doppler_list']);

                if ($wasCustomerSubscribed)
                {
                    Mage::getSingleton('core/session')->addSuccess($this->__('The customer has been subscribed to the selected list'));
                } else {
                    Mage::getSingleton('core/session')->addError($this->__('There has been an error exporting the customer to Doppler'));
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
                foreach ($customersIds as $customerId) {
                    $dopplerListId = $this->getRequest()->getParam('list');

                    // Load selected customer
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $customer->setWebsiteId(Mage::app()->getWebsite()->getId());

                    // Export customer to Doppler
                    $wasCustomerExported = Mage::helper('makingsense_doppler')->exportCustomerToDoppler($customer, $dopplerListId);

                    if ($wasCustomerExported)
                    {
                        $exportedSuccessfully++;
                    }
                }

                $exportedWithErrors = count($customersIds) - $exportedSuccessfully;

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