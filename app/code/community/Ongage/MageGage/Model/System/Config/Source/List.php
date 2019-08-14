 <?php
/*
    * Ongage Magento Plug-in
    * Version 1.0.3
    * Released March, 2014
    * Credits: Jaldip Upadhyay, Krunal Patel, Pratik Patel, Dharmesh Vasani
    * Terms of Service: http://www.ongage.com/legal-terms/terms-of-service
    * Copyright (C) Ongage
*/
class Ongage_MageGage_Model_System_Config_Source_List
{
    /**
     * Lists for API key will be stored here
     *
     * @access protected
     * @var array Email lists for given API key
     */
    protected $_lists   = null;
    /**
     * Load lists and store on class property
     *
     * @return void
     */
    public function __construct()
    {
        if( is_null($this->_lists) )
        {
            $this->_lists = Mage::getSingleton('gage/api')
                                            ->getLists();
        }
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	$aLists = array();    
    	if(!empty($this->_lists))
        {
            foreach($this->_lists->payload as $aListData)
            {
                $aLists []= array('value' => $aListData->id, 'label' => $aListData->name . ' (' . $aListData->last_active_count. ' members )');
            }
    	}
        else
        {
            $aLists []= array('value' => '', 'label' => 'No List Avalable');
    	}
        return $aLists;
    }
}