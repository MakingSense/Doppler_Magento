<?php
/**
 * MakingSense_Doppler module observer
 *
 * @category    MakingSense
 * @package     Doppler
 * @author      Gabriel Guarino <guarinogabriel@gmail.com>
 */
class MakingSense_Doppler_Model_Observer
{
    /**
     * When an user registers, then send customer data to Doppler default list
    */
    public function userRegistration()
    {
        // If the customer is logged in after the observer dispatch, that means that the customer was successfully registered
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            Mage::log(Mage::getSingleton('customer/session')->getCustomer()->getData(), null,'registered-customer.log');
        }
    }

    /**
     * When an user creates a new order and register, then send customer data to Doppler default list
     * @param $observer
    */
    public function checkoutUserRegistration($observer)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getQuote();

        // Validate that the checkout method was "register"
        if ($quote->getData('checkout_method') != Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER) {
            return;
        }

        // Get customer data
        $customer = $quote->getCustomer()->getData();
        Mage::log($customer, null,'debug.log');
    }
}