<?php
/**
 * Doppler module system config form field
 *
 * @category    MakingSense
 * @package     Doppler
 * @author      Gabriel Guarino <guarinogabriel@gmail.com>
 */

class MakingSense_Doppler_Block_System_Config_Form_Fieldset_Connection_Test extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Add jQuery to system configuration section
     */
    public function _prepareLayout()
    {
        $head = $this->getLayout()->getBlock('head');
        $head->addJs('lib/jquery/jquery-1.10.2.js');
        $head->addJs('lib/jquery/noconflict.js');

        return parent::_prepareLayout();
    }

    /**
     * Customize test connection system configuration element
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $block = Mage::app()->getLayout()->createBlock('adminhtml/widget_form_renderer_element')
            ->setTemplate('doppler/form/testconnection.phtml');

        $usernameValue = Mage::getStoreConfig('doppler/connection/username');
        $apiKeyValue = Mage::getStoreConfig('doppler/connection/key');

        if($usernameValue != '' && $apiKeyValue != '') {

            if (Mage::helper('makingsense_doppler')->testAPIConnection()) {
                // Get cURL resource
                $ch = curl_init();

                // Set url
                curl_setopt($ch, CURLOPT_URL, 'https://restapi.fromdoppler.com/accounts/' . $usernameValue);

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
                    $block->setStatusCode($statusCode);

                    Mage::log("Response HTTP Status Code : " . $statusCode, null,'doppler.log');
                    Mage::log("Response HTTP Body : " . $resp, null,'doppler.log');
                }

                // Close request to clear up some resources
                curl_close($ch);
            } else {
                $block->setStatusCode('404');
            }

        }

        return $block->toHtml();
    }


}