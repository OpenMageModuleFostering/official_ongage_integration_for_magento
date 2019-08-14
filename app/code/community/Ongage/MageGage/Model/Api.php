<?php
/*
    * Ongage Magento Plug-in
    * Version 1.0.4
    * Released March, 2014
    * Credits: Jaldip Upadhyay, Krunal Patel, Pratik Patel, Dharmesh Vasani
    * Terms of Service: http://www.ongage.com/legal-terms/terms-of-service
    * Copyright (C) Ongage
*/
class Ongage_MageGage_Model_Api 
{
    var $apiUrl;
    var $sUserName;
    var $sPassword;
    var $sAccountId;
    var $sListId;
    /**
     * Initialize API
     *
     * @param array $args
     * @return void
     */
    public function __construct($args) 
    {
        if(!isset($args['userDetails']))
        {
           $args['userDetails'] = Mage::helper('gage')->getUserDetails();
        }
        $this->sUserName = $args['userDetails']['user_name'];
        $this->sPassword = $args['userDetails']['password'];
        $this->sAccountId = $args['userDetails']['account_id'];
        $this->sListId = $args['userDetails']['attach_list'];
        
        define('USERNAME', $this->sUserName);
        define('PASSWORD', $this->sPassword);
        define('ACCOUNT', $this->sAccountId);
        define('LIST_ID', $this->sListId);
        define('URL', 'http://connect.ongage.net/api/');
    }
    public function getLists($aRequest = array(),$sMethod = 'get')
    {
        return $this->post_request($aRequest, URL.'lists', $sMethod);
    }
    public function post_request($request, $link, $method)
    {
        $sListId = $this->sListId;
        $request['list_id'] = $sListId;

        $request_json = json_encode($request);
        $c = curl_init();
        $link = $link;
        switch($method)
        {
            case "post":
                curl_setopt($c, CURLOPT_URL, $link);
                curl_setopt($c, CURLOPT_POST, TRUE);
                curl_setopt($c, CURLOPT_POSTFIELDS,$request_json );
                break;
            case "put":
                curl_setopt($c, CURLOPT_URL, $link);
                curl_setopt($c, CURLOPT_PUT, TRUE);
                $temp = tmpfile();
                fwrite($temp, $request_json);
                fseek($temp, 0);
                curl_setopt($c, CURLOPT_INFILE, $temp);
                curl_setopt($c, CURLOPT_INFILESIZE, strlen($request_json));
                break;
            case "get":
                if ( ! empty($request))
                {
                        $link .= '?' . http_build_query($request);
                }
                curl_setopt($c, CURLOPT_URL, $link);
                break;
        }
        $headers = array(
            'X_USERNAME: ' . USERNAME,
            'X_PASSWORD: ' . PASSWORD,
            'X_ACCOUNT_CODE: ' . ACCOUNT,
        );

        curl_setopt($c, CURLOPT_HTTPHEADER, array_merge(array(
                // Overcoming POST size larger than 1k wierd behaviour
                // @link  http://www.php.net/manual/en/function.curl-setopt.php#82418
                'Expect:'), $headers
        ));

        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $response_raw = curl_exec($c);

        if ($method == 'put')
        {
                fclose($temp); // this removes the file
        }

        $errno =  curl_errno ( $c );
        $result = json_decode($response_raw);
        
        return $result;
    }
    //below code for when register user then ongage entry
    public function get_regisrationdata($aRegistrationData)
    {   
        $aConfigration = Mage::helper('gage')->getUserDetails();
        $aRequest = array();
        $aRequest['fields'] = $aRegistrationData;
        $nNewsSubscribe = isset($aRegistrationData['is_subscribed'])?$aRegistrationData['is_subscribed']:'';
        if($aConfigration['letter_subscription'] || $nNewsSubscribe )
        {
            return $this->post_request($aRequest, URL.'contacts', 'post');
        }
    }
    //below code for checkout time register in ongage
    public function getguest_regisrationdata($aCustomer_Info)
    {
        $aRequest = array(); 
        $_customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getSingleton('sales/order');
        $order->load($lastOrderId);
        $_totalData = $order->getData();
        $_details = $customer->getData();               
        
        $sEmail = $_totalData['customer_email'];
        $aCustomer_Info['email'] = $sEmail; 
        return $this->post_request($aCustomer_Info, URL.'contacts', 'post');
    }
    //below code for homepage subscriber guest user
    public function guest_subscription($sGuestUserEmailId)
    {
        $aRequest = array();       
        $aRequest['email'] = $sGuestUserEmailId;        
        return $this->post_request($aRequest, URL.'contacts', 'post');
    }
    //below code for delete subscriber user in magento as well as ongage
    public function delete_subscriber($sEmailId)
    {  
        $aRequest = array();
        $aRequest['change_to'] = 'remove';
        $aRequest['emails'] = array($sEmailId);        
        return $this->post_request($aRequest, URL.'contacts/remove', 'post');
    }
    //below code for unsubscribe user subscriber user in magento as well as ongage
    public function unsubscribe_subscriber($sEmailId)
    { 
        $aRequest = array();
        $aRequest['change_to'] = 'unsubscribe';
        $aRequest['emails'] = array($sEmailId);        
        return $this->post_request($aRequest, URL.'contacts/remove', 'post');
    }
    //below code for when first time load list at that time entry in ongage
    public function firsttime_ongageentry($aAllEmailId)
    { 
        $aRequest = array();       
        $aRequest['email'] = $aAllEmailId; 
        return $this->post_request($aRequest, URL.'contacts', 'post');
    }
}