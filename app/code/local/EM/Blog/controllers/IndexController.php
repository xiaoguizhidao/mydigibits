<?php
class EM_Blog_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $title = $this->getLayout()->getBlock('head')->getTitle();
        $this->getLayout()->getBlock('head')->setTitle("$title ".Mage::getStoreConfig('blog/info/page_title'));
        $this->renderLayout();
    }
}
