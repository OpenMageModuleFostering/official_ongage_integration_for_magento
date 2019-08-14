<?php
/*
    * Ongage Magento Plug-in
    * Version 1.0.5
    * Released July, 2014
    * Credits: Jaldip Upadhyay, Krunal Patel, Pratik Patel, Dharmesh Vasani
    * Terms of Service: http://www.ongage.com/legal-terms/terms-of-service
    * Copyright (C) Ongage
*/
class Ongage_MageGage_Block_Form_Register extends Mage_Customer_Block_Form_Register
{
    /**
     *  Newsletter module availability
     *
     *  @return boolean
     */
    public function isNewsletterEnabled()
    {
        $aConfigration = Mage::helper('gage')->getUserDetails();
        if($aConfigration['letter_subscription'])
        {    
            return false;
        }
        else
        {
            return Mage::helper('core')->isModuleOutputEnabled('Mage_Newsletter');
        }        
    }    
}