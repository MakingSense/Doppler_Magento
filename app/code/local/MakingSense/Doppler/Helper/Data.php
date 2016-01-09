<?php
/**
 * Module data helper
 *
 * @category    MakingSense
 * @package     Doppler
 * @author      Gabriel Guarino <guarinogabriel@gmail.com>
 */

class MakingSense_Doppler_Helper_Data extends Mage_Core_Helper_Abstract
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
     * Doppler fields array
     *
     * @var null|array
     */
    protected $_fieldsArray = null;

    /**
     * Doppler lists array
     *
     * @var null|array
     */
    protected $_listsArray = null;

    /**
    * Doppler list statuses
    */
    const DOPPLER_LIST_STATUS_DELETED = 'deleted';
    const DOPPLER_LIST_STATUS_ENABLED = 'enabled';

    /**
     * API call to test if Doppler API is active
     */
    public function testAPIConnection() {

        $usernameValue = Mage::getStoreConfig('doppler/connection/username');
        $apiKeyValue = Mage::getStoreConfig('doppler/connection/key');

        $apiAvailable = false;

        if($usernameValue != '' && $apiKeyValue != '')
        {
            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue . '/lists');

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: token " . $apiKeyValue,
                ]
            );

            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if ($resp)
            {
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($statusCode == '200')
                {
                    $apiAvailable = true;
                }
            }

            // Close request to clear up some resources
            curl_close($ch);
        }

        return $apiAvailable;

    }

    /**
     * Export Magento customer to Doppler
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param int $dopplerListId
     *
     * @return bool $exportedSuccessfully
     */
    public function exportCustomerToDoppler($customer, $dopplerListId)
    {
        $exportedSuccessfully = false;

        $usernameValue = Mage::getStoreConfig('doppler/connection/username');
        $apiKeyValue = Mage::getStoreConfig('doppler/connection/key');

        if($usernameValue != '' && $apiKeyValue != '')
        {
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
            ]);

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

            for ($i = 0; $i < $mappedFieldsCount; $i++) {
                $fieldName = $leadMappingArrayKeys[$i];
                $customerAttributeValue = $this->_customerAttributes[$customerAttributesArrayKeys[$i]];
                $body .= '{ name: "' . $fieldName . '", value: "' . $customerAttributeValue . '" }, ';
            }

            $body .= ']}';

            // Set body
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if ($resp)
            {
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($statusCode == '200')
                {
                    // Set doppler_synced attribute to true since the customer was successfully exported
                    if($customer->getId() > 1){
                        $customer->setDopplerSynced('1')->save();
                    }

                    $exportedSuccessfully = true;
                }
            }

            // Close request to clear up some resources
            curl_close($ch);
        }

        return $exportedSuccessfully;
    }

    /**
     * Get all fields from Doppler
     */
    public function getDopplerFields()
    {
        $this->_fieldsArray = array();

        $usernameValue = Mage::getStoreConfig('doppler/connection/username');
        $apiKeyValue = Mage::getStoreConfig('doppler/connection/key');

        if($usernameValue != '' && $apiKeyValue != '') {
            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue. '/fields');

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: token " . $apiKeyValue,
                ]
            );

            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if($resp)
            {
                $responseContent = json_decode($resp, true);
                $fieldsResponseArray = $responseContent['items'];

                foreach ($fieldsResponseArray as $field)
                {
                    $fieldName = $field['name'];
                    $this->_fieldsArray[$fieldName] = $fieldName;
                }
            }

            // Close request to clear up some resources
            curl_close($ch);
        }

        return $this->_fieldsArray;
    }

    /**
     * Get Doppler lists from API
     */
    public function getDopplerLists()
    {
        $this->_listsArray = array();

        $usernameValue = Mage::getStoreConfig('doppler/connection/username');
        $apiKeyValue = Mage::getStoreConfig('doppler/connection/key');

        if($usernameValue != '' && $apiKeyValue != '') {
            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue. '/lists');

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Set headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: token " . $apiKeyValue,
                ]
            );

            // Send the request & save response to $resp
            $resp = curl_exec($ch);

            if($resp)
            {
                $responseContent = json_decode($resp, true);
                $listsResponseArray = $responseContent['items'];

                foreach ($listsResponseArray as $list)
                {
                    $fieldName = $list['name'];
                    $listId = $list['listId'];
                    $this->_listsArray[$listId] = $fieldName;
                }

            }

            // Close request to clear up some resources
            curl_close($ch);
        }

        return $this->_listsArray;
    }

    /**
     * Get default Doppler list from Magento
     */
    public function getDefaultDopplerList() {
        $defaultDopplerList = 0;
        $defaultListCollection = Mage::getModel('makingsense_doppler/defaultlist')->getCollection();

        foreach ($defaultListCollection->getData() as $defaultList)
        {
            $listStatus = $defaultList['list_status'];

            if ($listStatus == self::DOPPLER_LIST_STATUS_ENABLED) {
                $defaultDopplerList = $defaultList['listId'];
            }
        }

        return $defaultDopplerList;
    }

    /**
     * Check if Doppler list is the default list
     *
     * @param int $listId
     * @return int
     */
    public function isDefaultList($listId)
    {
        if ($listId)
        {
            $list = Mage::getModel('makingsense_doppler/lists')->load($listId);
            $dopplerListId = $list->getListId();

            $defaultListCollection = Mage::getModel('makingsense_doppler/defaultlist')->getCollection();
            foreach ($defaultListCollection as $defaultList)
            {
                if ($dopplerListId == $defaultList->getData('listId'))
                {
                    return 1;
                }
            }
        }

        return 0;
    }
}