<?php
class EM_Blog_Model_Abstract extends Mage_Catalog_Model_Abstract
{
	/*
		Get Url Instance
		@return EM_Blog_Model_Url
	*/
	protected function getUrlInstance(){
		return Mage::getSingleton('blog/url');
	}
	
	/**
     * Returns array with dates for custom design
     *
     * @return array
     */
    public function getCustomDesignDate()
    {
        $result = array();
        $result['from'] = $this->getData('custom_design_from');
        $result['to'] = $this->getData('custom_design_to');

        return $result;
    }
}