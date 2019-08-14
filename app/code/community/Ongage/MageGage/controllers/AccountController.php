<?php
include_once('Mage/Customer/controllers/AccountController.php');
/*
    * Ongage Magento Plug-in
    * Version 1.0.4
    * Released March, 2014
    * Credits: Jaldip Upadhyay, Krunal Patel, Pratik Patel, Dharmesh Vasani
    * Terms of Service: http://www.ongage.com/legal-terms/terms-of-service
    * Copyright (C) Ongage
*/
class Ongage_MageGage_AccountController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('loginPost', 'createpost');

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!$this->getRequest()->isDispatched()) 
        {
            return;
        }
        $action = $this->getRequest()->getActionName();
        $aOpenActions = array(
            'create',
            'login',
            'logoutsuccess',
            'forgotpassword',
            'forgotpasswordpost',
            'resetpassword',
            'resetpasswordpost',
            'confirm',
            'confirmation'
        );
        $sPattern = '/^(' . implode('|', $aOpenActions) . ')/i';

        if (!preg_match($sPattern, $action)) 
        {
            if (!$this->_getSession()->authenticate($this)) 
            {
                $this->setFlag('', 'no-dispatch', true);
            }
        }
        else
        {
            $this->_getSession()->setNoReferer(true);
        }
    }
    /**
     * Action postdispatch
     *
     * Remove No-referer flag from customer session after each action
     */
    public function postDispatch()
    {
        parent::postDispatch();
        $this->_getSession()->unsNoReferer(false);
    }
    /**
     * Default customer account page
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('content')->append(
            $this->getLayout()->createBlock('customer/account_dashboard')
        );
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Account'));
        $this->renderLayout();
    }
    /**
     * Create customer account action
     */
    public function createPostAction()
    {
        /** @var $sSession Mage_Customer_Model_Session */
        
        $aConfigration = Mage::helper('gage')->getUserDetails();
        if($aConfigration['letter_subscription'])
        {
            $_POST['is_subscribed']=1;
            $sSession = $this->_getSession();
            if ($sSession->isLoggedIn()) 
            {
                $this->_redirect('*/*/');
                return;
            }
            $sSession->setEscapeMessages(true); // prevent XSS injection in user input
            if (!$this->getRequest()->isPost()) 
            {
                $sErrUrl = $this->_getUrl('*/*/create', array('_secure' => true));
                $this->_redirectError($sErrUrl);
                return;
            }
            $aCustomer = $this->_getCustomer();
            try 
            {
                $aErrors = $this->_getCustomerErrors($aCustomer);
                if (empty($aErrors)) 
                {
                    $aCustomer->save();
                    $this->_dispatchRegisterSuccess($aCustomer);
                    $this->_successProcessRegistration($aCustomer);
                    return;
                }
                else
                {
                    $this->_addSessionError($aErrors);
                }
            } 
            catch (Mage_Core_Exception $e) 
            {
                $sSession->setCustomerFormData($this->getRequest()->getPost());
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) 
                {
                    $sUrl = $this->_getUrl('customer/account/forgotpassword');
                    $sMessage = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $sUrl);
                    $sSession->setEscapeMessages(false);
                }
                else
                {
                    $sMessage = $e->getMessage();
                }
                $sSession->addError($sMessage);
            } 
            catch (Exception $e) 
            {
                $sSession->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save the customer.'));
            }
            $sErrUrl = $this->_getUrl('*/*/create', array('_secure' => true));
            $this->_redirectError($sErrUrl); 
        }
        else
        {
            $sSession = $this->_getSession();
            if ($sSession->isLoggedIn()) 
            {
                $this->_redirect('*/*/');
                return;
            }
            $sSession->setEscapeMessages(true); // prevent XSS injection in user input
            if (!$this->getRequest()->isPost()) 
            {
                $sErrUrl = $this->_getUrl('*/*/create', array('_secure' => true));
                $this->_redirectError($sErrUrl);
                return;
            }
            $aCustomer = $this->_getCustomer();
            try 
            {
                $aErrors = $this->_getCustomerErrors($aCustomer);
                if (empty($aErrors)) 
                {
                    $aCustomer->save();
                    $this->_dispatchRegisterSuccess($aCustomer);
                    $this->_successProcessRegistration($aCustomer);
                    return;
                }
                else
                {
                    $this->_addSessionError($aErrors);
                }
            } 
            catch (Mage_Core_Exception $e) 
            {
                $sSession->setCustomerFormData($this->getRequest()->getPost());
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) 
                {
                    $sUrl = $this->_getUrl('customer/account/forgotpassword');
                    $sMessage = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $sUrl);
                    $sSession->setEscapeMessages(false);
                }
                else
                {
                    $sMessage = $e->getMessage();
                }
                $sSession->addError($sMessage);
            } 
            catch (Exception $e) 
            {
                $sSession->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save the customer.'));
            }
            $sErrUrl = $this->_getUrl('*/*/create', array('_secure' => true));
            $this->_redirectError($sErrUrl);
        }        
    }
}