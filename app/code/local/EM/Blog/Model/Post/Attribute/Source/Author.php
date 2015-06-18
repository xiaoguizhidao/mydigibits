<?php
class EM_Blog_Model_Post_Attribute_Source_Author extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	protected $_optionsArray = null;
	public function getAdminList(){
		$collection = Mage::getModel('admin/user')->getCollection();
		return $collection;
	}

	public function getAllOptions()
    {
        if (!$this->_options) {
			$this->_options = array();
			foreach($this->getAdminList() as $admin){
				$this->_options[] = array(
					'value'	=>	$admin->getUserId(),
					'label'	=>	$admin->getFirstname().' '.$admin->getLastname()
				);
			}
        }
        return $this->_options;
    }
	
	public function getOptionsArray()
    {
        if (!$this->_optionsArray) {
			$this->_optionsArray = array();
			foreach($this->getAdminList() as $admin){
				$this->_optionsArray[$admin->getUserId()] = $admin->getFirstname().' '.$admin->getLastname();
			}
        }
        return $this->_optionsArray;
    }
}