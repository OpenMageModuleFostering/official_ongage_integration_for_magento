<?php
/*
    * Ongage Magento Plug-in
    * Version 1.0.5
    * Released July, 2014
    * Credits: Jaldip Upadhyay, Krunal Patel, Pratik Patel, Dharmesh Vasani
    * Terms of Service: http://www.ongage.com/legal-terms/terms-of-service
    * Copyright (C) Ongage
*/
class Ongage_MageGage_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getUserDetails($sStore = null)
    {   
        $aUserDetail['ENABLED'] = $this->config('ENABLED');
        if($aUserDetail['ENABLED']==1)
        {
            if(is_null($sStore))
            {
                $aUserDetail['user_name'] = $this->config('user_name');
                $aUserDetail['password'] = $this->config('password');
                $aUserDetail['account_id'] = $this->config('account_id');
                $aUserDetail['letter_subscription'] = $this->config('letter_subscription');
                $aUserDetail['attach_list'] = $this->config('attach_list');
                $aUserDetail['ENABLED'] = $this->config('ENABLED');
                $aUserDetail['map_fields'] = $this->config('map_fields');
                $aUserDetail['uploadcsv_file'] = $this->config('uploadcsv_file');
               
            }
            return $aUserDetail;
        }
    }
    /**
    * Get module configuration value
    *
    * @param string $value
    * @param string $sStore
    * @return mixed Configuration setting
    */
    public function config($value, $sStore = null)
    {
        $sStore = is_null($sStore) ? Mage::app()->getStore() : $sStore;
        $oConfigscope = Mage::app()->getRequest()->getParam('store');
        if( $oConfigscope && ($oConfigscope !== 'undefined') )
        {
            $sStore = $oConfigscope;
        }
        return Mage::getStoreConfig("gage/general/$value", $sStore);
    }
    public function canCheckoutSubscribe()
    {
        return $this->config('letter_subscription');
    }
}