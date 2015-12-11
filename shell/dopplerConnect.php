<?php
/**
 * Test Doppler connection
 *
 * @copyright      Copyright (c) 2015
 * @author         Gabriel Guarino | gabrielguarino.com
 *
 */

require_once 'abstract.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
umask(0);

class Doppler_Shell_dopplerConnect extends Mage_Shell_Abstract
{
    public function run()
    {
        $serviceUrl = 'https://restapi.fromdoppler.com?access_token=75D1DFD190CDC50AE95EAEAAB661F949';
        $curl = curl_init($serviceUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curlResponse = curl_exec($curl);
        if ($curlResponse === false) {
            $info = curl_getinfo($curl);
            curl_close($curl);
            Mage::log('Error: ' . $info, null,'rest.log');
            die('Error occurred during curl execution. Additional info in log file.');
        }
        curl_close($curl);
        $decoded = json_decode($curlResponse);
        if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
            die('Error occurred: ' . $decoded->response->errormessage);
        }
        echo 'Response OK';
        Mage::log('Response: ' . $decoded->response, null,'rest.log');

    }

}

$shell = new Doppler_Shell_DopplerConnect();
$shell->run();
