<?php
/*
    * Ongage Magento Plug-in
    * Version 1.0.4
    * Released March, 2014
    * Credits: Jaldip Upadhyay, Krunal Patel, Pratik Patel, Dharmesh Vasani
    * Terms of Service: http://www.ongage.com/legal-terms/terms-of-service
    * Copyright (C) Ongage
*/
class Ongage_MageGage_Model_Observer
{     
    /**
     * Handle save of System -> Configuration, section <gage>
     *
     * @param Varien_Event_Observer $observer
     * @return void|Varien_Event_Observer
     */
    public function saveConfig(Varien_Event_Observer $observer)
    {   
        $aConfigration = Mage::helper('gage')->getUserDetails();
        $sCsvFilePath = Mage::getBaseUrl('media');
        //below code is for upload csv
        $aEmail = array();
        if (($sHandle = fopen($sCsvFilePath.'ongage/'.$aConfigration['uploadcsv_file'], "r")) !== FALSE) 
        {
            while (($sFileData = fgetcsv($sHandle, 1000, ",")) !== FALSE) 
            {
                $nNumber = count($sFileData);
                for ($nCountRecord=0; $nCountRecord < $nNumber; $nCountRecord++) 
                {
                    if($sFileData[0] != 'email' && $nCountRecord == 0)
                    {
                        array_push($aEmail,$sFileData[0]); 
                        Mage::getModel('newsletter/subscriber')->setImportMode(true)->subscribe($sFileData[0]);
                        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($sFileData[0]);
                        if($sFileData[1] == 'Unsubscribed' )
                        {
                            $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
                        }
                        else
                        {
                            $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
                        }
                        $subscriber->save();
                    }
                }
            }
            fclose($sHandle);
        }   
        
        //below code is for get records from "newsletter_subscriber" table
        $oModelSubscriber = Mage::getModel('newsletter/subscriber');
        $oCollectionRecord = $oModelSubscriber->getCollection();
        $aId_Email = array();
        foreach($oCollectionRecord as $oSubscriberDetails)
        {
            $aData = $oSubscriberDetails->getData();
            $aId_Email[$aData['subscriber_id']] = $aData['subscriber_email'];
        }
        if (!empty($aEmail)) 
        {
            foreach($aId_Email as $nIdRecord  => $sEmail)
            {
                if(in_array($sEmail, $aEmail))
                {
                    '<br/>';
                }
                else
                {
                    $oModelSubscriber = Mage::getModel('newsletter/subscriber');
                    try 
                    {
                        $oModelSubscriber->setId($nIdRecord)->delete();
                    }
                    catch (Exception $e)
                    {
                        echo $e->getMessage();
                    }
                }
            }
        }
        //Below code for when first time load list all magento subscriber attach in Ongage
        $aConfigration = Mage::helper('gage')->getUserDetails();
        $sListValue=$aConfigration['attach_list'];
        if($sListValue != "")
        {
            foreach($oCollectionRecord as $oSubscriberDetails)
            {
                $aData = $oSubscriberDetails->getData();
                $aAllEmailId = $aData['subscriber_email'];            
                $oApiObject = new Ongage_MageGage_Model_Api();
                $oApiObject->firsttime_ongageentry($aAllEmailId);
            }
        }
        //below code for delete import file
        $relativefilepath = './media/ongage/';
        $absolutefilepath = realpath($relativefilepath);
        $sAddFullPath=$absolutefilepath.'/*';
        $sFilesPath = glob($sAddFullPath);
        foreach($sFilesPath as $sFilename)
        {
            if(is_file($sFilename))
                unlink($sFilename);
        }           
        $sScope = is_null($observer->getEvent()->getStore()) ? Mage::app()->getDefaultStoreView()->getCode(): $observer->getEvent()->getStore();
        $sPost   = Mage::app()->getRequest()->getPost();
        $sRequest = Mage::app()->getRequest();
    }
    //This function is call when customer registration form is submited
    public function customerAddInOngage(Varien_Event_Observer $p_oObserver)
    {
        $aConfigration = Mage::helper('gage')->getUserDetails();
        $nCurrentActiveStoreId = Mage::app()->getStore()->getStoreId();
        $aMappingField = $this->getMergeMaps($nCurrentActiveStoreId);
        $aCustomer_Info = array();
        foreach($aMappingField as $aMappingList)
        {
            $sMagentoField = $aMappingList['magento'];
            $sOngageField  = $aMappingList['ongage'];
            if($sOngageField && $sMagentoField)
            {
                $sKey = $sOngageField;
                switch ($sMagentoField)
                {
                    case 'firstname':
                        $aCustomer_Info[$sKey] = $_POST['firstname'];
                    break;

                    case 'lastname':
                        $aCustomer_Info[$sKey] = $_POST['lastname'];
                    break;
                    default:
                    break;
                }
            }
        }
        $aCustomer_Info['is_subscribed'] = $_POST['is_subscribed'];            
        $aCustomer_Info['email'] = $_POST['email'];
        $aCustomer_Info['list_id'] = $aConfigration['attach_list'];

        $aConfigration = Mage::helper('gage')->getUserDetails();
        if($aConfigration['letter_subscription'] == 1)
        {
            $aCustomer_Info['is_subscribed'] = '1';
            Mage::getModel('newsletter/subscriber')->subscribe($_POST['email']);
        }
        $oApiObject = new Ongage_MageGage_Model_Api();
        $oApiObject->get_regisrationdata($aCustomer_Info);
    }
    //This function is call when customer checkout form is submited and Make entry in Magento table or Ongage table
    public function registerCheckoutSubscribe(Varien_Event_Observer $observer)
    {
        $nCheckBoxValue=Mage::app()->getRequest()->getPost();            
        if($nCheckBoxValue['ongage_subscribe'] == 1)
        {  
            $_customerId = Mage::getSingleton('customer/session')->getCustomerId();
            $oCustomer = Mage::getSingleton('customer/session')->getCustomer();
            $nLastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
            $oOrder = Mage::getSingleton('sales/order');
            $oOrder->load($nLastOrderId);
            $_totalData = $oOrder->getData();
            $_details = $oCustomer->getData(); 

            $sSession = Mage::getSingleton('core/session');
            $sCustomerSession = Mage::getSingleton('customer/session');

            $aCustomer_Info = array();
            $orderId = (int)current($observer->getEvent()->getOrderIds());
            $oOrder = Mage::getModel('sales/order')->load($nLastOrderId);

            $oCustomer = new Varien_Object;
            $oCustomer->setId('guest' . time());
            $oCustomer->setEmail($oOrder->getBillingAddress()->getEmail());
            $oCustomer->setStoreId($oOrder->getStoreId());
            $oCustomer->setFirstname($oOrder->getBillingAddress()->getFirstname());
            $oCustomer->setLastname($oOrder->getBillingAddress()->getLastname());
            $oCustomer->setPrimaryBillingAddress($oOrder->getBillingAddress());
            $oCustomer->setPrimaryShippingAddress($oOrder->getShippingAddress());

            Mage::register('og_guest_customer', $oCustomer, TRUE);
            $aMappingField = $this->getMergeMaps($oCustomer->getStoreId());
            foreach($aMappingField as $sMapList)
            {
                $sMagetoField = $sMapList['magento'];
                $sOngageField  = $sMapList['ongage'];
                if($sOngageField && $sMagetoField)
                {
                    $sKey = $sOngageField;
                    $sAddress = $oCustomer->{'getPrimary'.ucfirst('shipping').'Address'}();
                    switch ($sMagetoField)
                    {
                        case 'firstname':
                            $sFirstName = (string)$oCustomer->getData(strtolower($sMagetoField));
                            $aCustomer_Info[$sKey] = $sFirstName;
                        break;
                        case 'lastname':
                            $sLastName = (string)$oCustomer->getData(strtolower($sMagetoField));
                            $aCustomer_Info[$sKey] = $sLastName;
                        break;
                        case 'billing_address':
                        case 'shipping_address':
                            $sAddressField = explode('_', $sMagetoField);
                            if(!$sAddress)
                            {
                                if($oCustomer->{'getDefault' .ucfirst($sAddressField[0])}()) 
                                {
                                    $sAddress = Mage::getModel('customer/address')->load($oCustomer->{'getDefault' .ucfirst($sAddressField[0])}());
                                }
                            }
                            if($sAddress)
                            {
                                $aCustomer_Info[$sKey] = $sAddress->getStreet(1).",".$sAddress->getStreet(2);
                            }
                        break;
                        case 'telephone':
                            $aCustomer_Info[$sKey] = $sAddress->getTelephone();
                        break;
                        case 'country':
                            $aCustomer_Info[$sKey] = $sAddress->getCountryId();
                        break;
                        case 'company':
                            $aCustomer_Info[$sKey] = $sAddress->getCompany();
                        break;
                        case 'date_of_purchase':
                            $nLast_Order = Mage::getResourceModel('sales/order_collection')
                             ->addFieldToFilter('customer_email', $oCustomer->getEmail())
                             ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
                             ->setOrder('created_at', 'desc')
                             ->getFirstItem();
                            if ( $nLast_Order->getId() )
                            {
                                 $aCustomer_Info[$sKey] = Mage::helper('core')->formatDate($nLast_Order->getCreatedAt());
                            }
                        break;
                        case 'group_id':
                            $nGroup_Id = (int)$oCustomer->getData(strtolower($sMagetoField));
                            $sCustomerGroup = Mage::helper('customer')->getGroups()->toOptionHash();
                            if($nGroup_Id == 0)
                            {
                                $aCustomer_Info[$sKey] = 'NOT LOGGED IN';
                            }
                            else
                            {
                                $aCustomer_Info[$sKey] = $sCustomerGroup[$nGroup_Id];
                            }
                        break;
                        case 'ee_customer_balance':
                            $aCustomer_Info[$sKey] = '';
                            if($this->isEnterprise() && $oCustomer->getId())
                            {
                                $_customer = Mage::getModel('customer/customer')->load($oCustomer->getId());
                                if($_customer->getId())
                                {
                                    if (Mage::app()->getStore()->isAdmin())
                                    {
                                        $websiteId = is_null($websiteId) ? Mage::app()->getStore()->getWebsiteId() : $websiteId;
                                    }
                                    $balance = Mage::getModel('enterprise_customerbalance/balance')
                                        ->setWebsiteId($websiteId)
                                        ->setCustomerId($_customer->getId())
                                        ->loadByCustomer();
                                        $aCustomer_Info[$sKey] = $balance->getAmount();
                                }
                            }
                        break;
                        default:
                        break;
                    }
                }
            }
            $sEmail = (string) $_totalData['customer_email'];
            Mage::getModel('newsletter/subscriber')->setImportMode(true)->subscribe($sEmail);
            $oApiObjectCheckout = new Ongage_MageGage_Model_Api();
            $oApiObjectCheckout->getguest_regisrationdata($aCustomer_Info);
        }
    }
    /**
    * Get config setting <map_field>
    *
    * @return array|FALSE
    */
    public function getMergeMaps($nStoreId)
    {
        return unserialize( Mage::helper('gage')->config('map_fields', $nStoreId) );
    }
    /**
     * Check if Magento is EE
     *
     * @return bool
     */
    public function isEnterprise()
    {
        return is_object(Mage::getConfig()->getNode('global/models/enterprise_enterprise'));
    }
    //below code for delete subscriber user in magento as well as ongage
    public function subscriberDeletion(Varien_Event_Observer $observer)
    {
        $subscriber = $observer->getEvent()->getSubscriber();
        $subscriber->setImportMode(TRUE);
        $sEmailId = $subscriber['subscriber_email'];
        $oApiObjectCheckout = new Ongage_MageGage_Model_Api();
        $oApiObjectCheckout->delete_subscriber($sEmailId);
    }
    //below code for unsubscribe user subscriber user in magento as well as ongage
    public function handleSubscriber(Varien_Event_Observer $observer)
    {
        if($_POST['form_key']=='')
        {
        }
        else 
        {
            $subscriber = $observer->getEvent()->getSubscriber();
            $subscriber->setImportMode(TRUE);
            $sEmailId = $subscriber['subscriber_email'];
            $oApiObjectCheckout = new Ongage_MageGage_Model_Api();
            $oApiObjectCheckout->unsubscribe_subscriber($sEmailId);   
        }
    }
}