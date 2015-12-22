<?php

class MakingSense_Doppler_Helper_Data extends Mage_Core_Helper_Abstract {

    public function testAPIConnection() {

        $usernameValue = Mage::getStoreConfig('doppler/connection/username');
        $apiKeyValue = Mage::getStoreConfig('doppler/connection/key');

        $apiAvailable = false;

        if($usernameValue != '' && $apiKeyValue != '') {
            // Get cURL resource
            $ch = curl_init();

            // Set url
            curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/');

            // Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            // Set options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

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

}