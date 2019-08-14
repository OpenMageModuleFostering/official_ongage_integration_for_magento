<?php
/*
    * Ongage Magento Plug-in
    * Version 1.0.3
    * Released March, 2014
    * Credits: Jaldip Upadhyay, Krunal Patel, Pratik Patel, Dharmesh Vasani
    * Terms of Service: http://www.ongage.com/legal-terms/terms-of-service
    * Copyright (C) Ongage
*/
class Ongage_MageGage_Block_Adminhtml_System_Config_Form_Field_Mapfields extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('magento', array(
            'label' => Mage::helper('gage')->__('Customer'),
            'style' => 'width:120px',
        ));
        $this->addColumn('ongage', array(
            'label' => Mage::helper('gage')->__('OnGage'),
            'style' => 'width:120px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('gage')->__('Add Custom Field');
        parent::__construct();
    }
}