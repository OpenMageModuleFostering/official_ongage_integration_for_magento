<?php
/*
    * Ongage Magento Plug-in
    * Version 1.0.3
    * Released March, 2014
    * Credits: Jaldip Upadhyay, Krunal Patel, Pratik Patel, Dharmesh Vasani
    * Terms of Service: http://www.ongage.com/legal-terms/terms-of-service
    * Copyright (C) Ongage
*/
class Ongage_MageGage_Block_Adminhtml_System_Config_OauthWizard extends Mage_Adminhtml_Block_System_Config_Form_Field {

    /**
     * Set template to itself
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('magegage/system/config/oauth_wizard.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $originalData = $element->getOriginalData();
        $sCsvFilePath = Mage::getBaseUrl('media');
        $sFilePath=$sCsvFilePath.'ongagefile/samplefile.csv';
        $label = $originalData['button_label'];
        $this->addData(array(
            'button_label' => $this->helper('gage')->__($label),
            'button_url'   => $sFilePath,
        ));
        return $this->_toHtml();
    }
}
