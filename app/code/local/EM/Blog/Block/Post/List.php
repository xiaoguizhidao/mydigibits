<?php
class EM_Blog_Block_Post_List extends Mage_Core_Block_Template
{
    protected $sum = 0;
    protected $posts;
    protected function _prepareLayout()
    {
        $route = Mage::helper('blog')->getRoute();
        $isBlogPage = Mage::app()->getFrontController()->getAction()->getRequest()->getModuleName() == 'blog';

        // show breadcrumbs
        if ($isBlogPage && Mage::getStoreConfig('blog/info/blogcrumbs') && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))){
		
                        $breadcrumbs->addCrumb('home', array('label'=>Mage::helper('blog')->__('Home'), 'title'=>Mage::helper('blog')->__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));;
                if($tag = @urldecode($this->getRequest()->getParam('tag_id'))){//tag page
                    $name = Mage::getModel('blog/tag')->load($tag)->getName();
                    $breadcrumbs->addCrumb('blog', array('label'=>Mage::getStoreConfig('blog/info/title'), 'title'=>Mage::helper('blog')->__('Return to ' .Mage::getStoreConfig('blog/info/title')), 'link'=>Mage::getUrl($route)));
                    $breadcrumbs->addCrumb('blog_tag', array('label'=>Mage::helper('blog')->__('Tagged with "%s"', $name), 'title'=> Mage::helper('blog')->__('Tagged with "%s"', $name) ));
                }
				else if($this->getRequest()->getParam('id') || $this->getRequest()->getParam('cat'))
					$breadcrumbs->addCrumb('blog', array('label'=>Mage::getStoreConfig('blog/info/title'), 'title'=>Mage::helper('blog')->__('Return to ' .Mage::getStoreConfig('blog/info/title')), 'link'=>Mage::getUrl($route)));
				else{
                    $breadcrumbs->addCrumb('blog', array('label'=>Mage::getStoreConfig('blog/info/title'), 'title'=>Mage::helper('blog')->__('Return to ' .Mage::getStoreConfig('blog/info/title'))));
                }
        }



        //init toolbar
        $toolbar = $this->getLayout()->createBlock('blog/post_list_toolbar', 'Toolbar');
        $toolbar->setTemplate('em_blog/post/list/toolbar.phtml');
        $toolbar->setOrderField('title');
        if(!$this->getRequest()->getParam('limit')){
            $limit = Mage::getStoreConfig('blog/info/pagesize');
            /*$_limit = 0;
            foreach($toolbar->getAvailableLimit() as $value){
                    if($limit < $value) break;
                    $_limit = $value;
            }*/

            Mage::getSingleton('catalog/session')->setLimitPage($_limit);
            $this->getRequest()->setParam('limit',$_limit);
        }
        $toolbar->setChild('em_blog_pager',$this->getLayout()->createBlock('blog/post_list_toolbar_pager','em_blog_pager'));
        //$toolbar->disableExpanded();

    	$this->setToolbar($toolbar);
        
        return parent::_prepareLayout();

    }
	

    public function getCurrentUrl()
    {
        $route = Mage::getUrl();
        $uri =  Mage::app()->getRequest()->getPathInfo();
        $uri = substr($uri,1,strlen($uri)-1);
        return $route.$uri;
    }
	
	public function getCurrentCategory(){
		return Mage::registry('current_cat');
	}
	
	public function getCurrentTag(){
		return Mage::registry('current_tag');
	}

    public function _initListPost()
    {
        $rs = array();
        $tagId = (int)$this->getRequest()->getParam('tag_id');
        $catId = (int)$this->getRequest()->getParam('id');
		$order = $this->getRequest()->getParam('order','created_at');
		$dir   = $this->getRequest()->getParam('dir','desc');
		if($category = $this->getCurrentCategory()){
			$collection = $category->getPostCollection($order,$dir);
			
			}
        else if($tagId)
			$collection = $this->getCurrentTag()->getPostCollection($order,$dir);
        else{
			$collection = Mage::getModel('blog/post')->getCollection()->setStoreId(Mage::app()->getStore()->getId())
							->addAttributeToSelect('*')
							->addAttributeToFilter('status',1);
			$collection->addAttributeToSort($order,$dir);
		}
		
    
        return $collection;
    }

    public function getPosts()
    {
        $collection = $this->_initListPost();

        $collection->setPageSize(Mage::getStoreConfig('blog/info/pagesize'))
              ->setCurPage($this->getRequest()->getParam('page',1));
        
        $this->setCollection($collection);
        $this->setSize($collection->count());
        
        $route = Mage::helper('blog')->getRoute();
        return $collection;
    }

    public function getCommentByPost($postId)
    {
        return Mage::getModel('blog/comment')->getCollection()
                ->addFieldTofilter('post_id',$postId)
                ->addFieldToFilter('status_comment',array('gt'=>1-Mage::getStoreConfig('blog/info/show_comment_pending')));
    }
   
}
