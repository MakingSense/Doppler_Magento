<?php
/**
 * Module data helper
 *
 * @category    MakingSense
 * @package     Doppler
 * @author      Gabriel Guarino <guarinogabriel@gmail.com>
 */

class MakingSense_Doppler_Helper_Data extends Mage_Core_Helper_Abstract {

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
     * API call to test if Doppler API is active
     */
    public function testAPIConnection() {

        $usernameValue = Mage::getStoreConfig('doppler/connection/username');
        $apiKeyValue = Mage::getStoreConfig('doppler/connection/key');

        $apiAvailable = false;

        if($usernameValue != '' && $apiKeyValue != '') {
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

            if (!$resp) {
                Mage::log('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
            } else {

                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($statusCode == '200') {
                    $apiAvailable = true;
                }

                Mage::log("Response HTTP Status Code : " . $statusCode, null, 'doppler.log');
                Mage::log("Response HTTP Body : " . $resp, null, 'doppler.log');
            }

            // Close request to clear up some resources
            curl_close($ch);
        }

        return $apiAvailable;

    }

    /**
     * Get all fields from Doppler
     *
     * @author  Gabriel Guarino <guarinogabriel@gmail.com>
     * @since 0.1.3
     * @return array
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

            if(!$resp) {
                Mage::log('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
            } else {

                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                Mage::log("Response HTTP Status Code : " . $statusCode, null,'doppler.log');
                Mage::log("Response HTTP Body : " . $resp, null,'doppler.log');

                $responseContent = json_decode($resp, true);
                $fieldsResponseArray = $responseContent['items'];

                foreach ($fieldsResponseArray as $field) {
                    $fieldName = $field['name'];
                    $this->_fieldsArray[$fieldName] = $fieldName;
                }

            }

            // Close request to clear up some resources
            curl_close($ch);
        }

        return $this->_fieldsArray;
    }

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

            if(!$resp) {
                Mage::log('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
            } else {

                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                Mage::log("Response HTTP Status Code : " . $statusCode, null,'doppler.log');
                Mage::log("Response HTTP Body : " . $resp, null,'doppler.log');

                $responseContent = json_decode($resp, true);
                $listsResponseArray = $responseContent['items'];

                foreach ($listsResponseArray as $list) {
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

}