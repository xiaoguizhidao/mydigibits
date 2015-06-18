<?php
class EM_Blog_Block_Post_View extends Mage_Core_Block_Template
{
    protected $sum = 0;
    protected $posts;
    protected $allowComment = 0;
    protected $nextPost = null;
    protected $prevPost = null;
    protected $linkCat = "";
    protected function _prepareLayout()
    {
        $route = Mage::helper('blog')->getRoute();
        $isBlogPage = Mage::app()->getFrontController()->getAction()->getRequest()->getModuleName() == 'blog';

        // show breadcrumbs
        if ($isBlogPage && Mage::getStoreConfig('blog/info/blogcrumbs') && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))){
            $breadcrumbs->addCrumb('home', array('label'=>Mage::helper('blog')->__('Home'), 'title'=>Mage::helper('blog')->__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));
            $breadcrumbs->addCrumb('blog', array('label'=>Mage::helper('blog')->__('Blog'), 'title'=>Mage::helper('blog')->__('Go to Blog'), 'link'=>Mage::getUrl('blog')));

            $catId = $this->getRequest()->getParam('cat_id');
            $post = $this->getPost();
            if($catId){
                $catNames = array();
                $category = Mage::getModel('blog/category')->load($catId);
				$catIds = $category->getPathIds();
				if(count($catIds) > 2){
					unset($catIds[0]);
					unset($catIds[1]);
					$link = "";
					foreach($catIds as $cid){
						$cat = Mage::getModel('blog/category')->load($cid);
						$breadcrumbs->addCrumb('blog_cat_'.$cat->getId(), array(
                                            'label'=>$cat->getName(),
                                            'title'=> $cat->getName(),
                                            'link'=>Mage::getUrl('blog').$link.$cat->getUrlKey().'.html'));
						$link .= $cat->getUrlKey().'/';					
					}
					$this->setLinkCat($link);
				}
            }
            
            $breadcrumbs->addCrumb('post', array('label'=>$post->getTitle(), 'title'=>$post->getTitle()));
        }
		$this->setNextPrePost();
		$this->initAllowComment();
        return parent::_prepareLayout();

    }

    public function setLinkCat($link)
    {
        $this->linkCat = $link;
    }

    public function getLinkCat()
    {
        return $this->linkCat;
    }

    public function setAllowComment($allow)
    {
        $this->allowComment = $allow;
        return $this;
    }

    public function getAllowComment()
    {
        return $this->allowComment;
    }

    protected function setNextPost($post)
    {
        $this->nextPost = $post;
    }

    protected function setPrevPost($post)
    {
        $this->prevPost = $post;
    }

    public function getNextPost()
    {
        return $this->nextPost;
    }

    public function getPrevPost()
    {
        return $this->prevPost;
    }

    public function getPost()
    {
        return Mage::registry('current_post');
    }
	
	protected function setNextPrePost(){
		$catId = $this->getRequest()->getParam('cat_id',0);
		if($catId){
			$category = Mage::getModel('blog/category')->load($catId);
			$collection = $category->getPostCollection('position','asc');
		}
		else{
			$collection = Mage::getModel('blog/post')->getCollection()
						->setStoreId(Mage::app()->getStore()->getId())
						->addAttributeToFilter('status',1)
						->addAttributeToSelect('*');	
		}
		if($collection->count() < 2)
			return $this;
		$plist = array();
		foreach($collection as $p){
			$plist[] = $p->getId();
		}	
		$current_pid  = $this->getPost()->getId();
		
		$curpos   = array_search($current_pid, $plist);
		// get prev post
		if(isset($plist[$curpos-1]))
			$this->setPrevPost(Mage::getModel('blog/post')->load($plist[$curpos-1]));
		// get next post
		if(isset($plist[$curpos+1]))
			$this->setNextPost(Mage::getModel('blog/post')->load($plist[$curpos+1]));
		return $this;		
	}
    
	protected function initAllowComment(){
		$allow_comment = (int)$this->getPost()->getAllowComment();
		if($allow_comment == 0)//chi co user dang nhap moi duoc comment
		{
			if($this->helper('customer')->isLoggedIn())
				$this->setAllowComment(1);//duoc comment
			else
				$this->setAllowComment(0);//khong duoc comment
		}
		elseif($allow_comment == 1)//Ai cung duoc comment
			$this->setAllowComment(1);//duoc comment
		else//Ai cung khong duoc comment
			$this->setAllowComment(0);//khong duoc comment
		return $this;	
	}
}
