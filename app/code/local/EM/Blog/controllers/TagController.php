<?php
class EM_Blog_TagController extends Mage_Core_Controller_Front_Action
{
	/**
     * Initialize requested tag object
     *
     * @return EM_Blog_Model_Tag
     */
	public function _initTag()
    {
        
        $tagId = (int) $this->getRequest()->getParam('tag_id', false);
        if (!$tagId) {
            return false;
        }

        $tag = Mage::getModel('blog/tag')->load($tagId);
		if($tag->getStatus())
			return false;
		Mage::register('current_tag',$tag);
        return $tag;
    }
	
    /**
     * Tag view action
     */
    public function viewAction()
    {
    	if($tag = $this->_initTag()){
			$date = $tag->getCustomDesignDate();
			if (array_key_exists('from', $date) && array_key_exists('to', $date)
				&& Mage::app()->getLocale()->isStoreDateInInterval(null, $date['from'], $date['to'])
			){
                Mage::helper('blog')->setTheme($tag->getData('custom_design'), $tag->getData('custom_layout_update_xml'), $tag->getData('custom_layout'),$this);
                
            }
            else
            {
                $this->loadLayout();
            }
			$title = $this->getLayout()->getBlock('head')->getTitle();
			$this->getLayout()->getBlock('head')->setTitle("$title tag ".$tag->getName());
			$this->renderLayout();
		}
		elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
		}
    }
	
	/**
     * Taglist view action
     */
	public function taglistAction(){
		$this->loadLayout();
		$this->renderLayout();
	}
}
