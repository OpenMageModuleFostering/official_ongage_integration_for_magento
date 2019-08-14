<?php
include_once('Mage/Newsletter/controllers/SubscriberController.php');
/*
    * Ongage Magento Plug-in
    * Version 1.0
    * Released March, 2014
    * Credits: Jaldip Upadhyay, Krunal Patel, Pratik Patel, Dharmesh Vasani
    * Terms of Service: http://www.ongage.com/legal-terms/terms-of-service
    * Copyright (C) Ongage
*/
class Ongage_MageGage_SubscriberController extends Mage_Core_Controller_Front_Action
{
    /**
      * New subscription action
      */
    public function newAction()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email'))
        {
            $session            = Mage::getSingleton('core/session');
            $customerSession    = Mage::getSingleton('customer/session');
            $email              = (string) $this->getRequest()->getPost('email');
            Mage::getModel('newsletter/subscriber')->subscribe($email);
        }
        $this->_redirectReferer();
        //below code for guest user subscriber in home page
        $sGuestUserEmailId = $this->getRequest()->getPost('email');
        $oApiObject = new Ongage_MageGage_Model_Api();
        $oApiObject->guest_subscription($sGuestUserEmailId);
    }
    
}