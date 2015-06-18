<?php
class EM_Blog_Block_Adminhtml_Category_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId    = 'entity_id';
        $this->_controller  = 'adminhtml_category';
        $this->_mode        = 'edit';
		$this->_blockGroup = 'blog';
        parent::__construct();
        
    }
}