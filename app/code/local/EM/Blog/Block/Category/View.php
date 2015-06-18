<?php
class EM_Blog_Block_Category_View extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        $category = $this->getCurrentCategory();
        $route = Mage::helper('blog')->getRoute();
        $isBlogPage = Mage::app()->getFrontController()->getAction()->getRequest()->getModuleName() == 'blog';
        
        // show breadcrumbs
        if ($isBlogPage && Mage::getStoreConfig('blog/info/blogcrumbs') && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))){
            $breadcrumbs->addCrumb('home', array('label'=>Mage::helper('blog')->__('Home'), 'title'=>Mage::helper('blog')->__('Go to Home Page'), 'link'=>Mage::getUrl('blog')));;
            $breadcrumbs->addCrumb('blog', array('label'=>Mage::getStoreConfig('blog/info/title'), 'title'=>Mage::helper('blog')->__('Return to ' .Mage::getStoreConfig('blog/info/title')), 'link'=>Mage::getUrl($route)));
            if($category){
                $catNames = array();
                while($category->getId() != Mage::getStoreConfig('blog/info/root_category_id'))
                {
                    $catNames[] = $category;
                    $category = Mage::getModel('blog/category')->load($category->getParentId());
                }
                $catNames = array_reverse($catNames);
                $link = "";
                $i = 0;
                foreach($catNames as $c)
                {
                    if($i == count($catNames)-1)
                        $breadcrumbs->addCrumb('blog_cat_'.$c->getId(), array(
                                            'label'=>$c->getName(),
                                            'title'=> $c->getName()
                                          ));
                    else
                        $breadcrumbs->addCrumb('blog_cat_'.$c->getId(), array(
                                            'label'=>$c->getName(),
                                            'title'=> $c->getName(),
                                            'link'=>Mage::getUrl('blog').$link.$c->getUrlKey().'.html'));
                    $link .= $c->getUrlKey().'/';
                    $i++;
                }
                 
            }
        }


        return parent::_prepareLayout();

    }
    public function getCurrentCategory()
    {
        return Mage::registry('current_cat');
    }

    public function getCmsBlockHtml()
    {
        if (!$this->getData('cms_block_html_blog')) {
            $html = $this->getLayout()->createBlock('cms/block')
                ->setBlockId($this->getCurrentCategory()->getCmsBlock())
                ->toHtml();
            $this->setData('cms_block_html_blog', $html);
        }
        return $this->getData('cms_block_html_blog');
    }

    public function getPostListHtml()
    {
        return $this->getChildHtml('list.post');
    }

}
