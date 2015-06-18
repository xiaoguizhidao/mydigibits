<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-L.txt
 *
 * @category   AW
 * @package    AW_Blog
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-L.txt
 */

class EM_Blog_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
	const REWRITE_REQUEST_PATH_ALIAS = 'rewrite_request_path';
    public function initControllerRouters($observer)
    {
        $front = $observer->getEvent()->getFront();
        $urlRoot = Mage::app()->getRequest()->getPathInfo();
        if(strstr($urlRoot.'/',"/blog/"))
			$front->addRouter('blog', $this);
    }

    public function analyticUrl($url)
    {
		$urlArray = explode('/',$url);
		if(count($urlArray) > 1){
			$requestArray = array($url,$urlArray[count($urlArray)-1]);
			$urlCollection = Mage::getModel('blog/url')->getCollection()
                            ->addFieldToFilter('request_path',array('in'=>$requestArray));
			if($urlCollection->count() > 1){
				foreach($urlCollection as $url){
					if($url->getPostId()){
						$post = Mage::getModel('blog/post')->load($url->getPostId());
						if($post->getUrlKey().'.html'==$requestArray[1])
							return $url;
						continue;	
					}
					if($url->getTagId()){
						$tag = Mage::getModel('blog/tag')->load($url->getTagId());
						if($tag->getTagIdentifier().'.html' == $requestArray[1])
							return $url;
						continue;	
					}	
					$tmp = $url;
				}
				return $tmp;
			}
			else{
				return $urlCollection->getFirstItem();
			}			
		}

		$urlData = Mage::getModel('blog/url')->getCollection()
						->addFieldToFilter('request_path',$url)
						->getFirstItem();
        return $urlData;
    }

    public function match(Zend_Controller_Request_Http $request)
    {
		if (!Mage::app()->isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }
		//echo '<pre>';print_r(Mage::app()->getRequest());exit;
        $urlRoot = Mage::app()->getRequest()->getPathInfo();
		
        if(!strstr($urlRoot.'/',"/blog/"))
            return true;
		$route = Mage::helper('blog')->getRoute();
		$uri =  str_replace("/blog/","",strstr(Mage::app()->getRequest()->getPathInfo(),"/blog/"));
		if(!Mage::registry('request_path'))
			Mage::register('request_path',$uri);
		$request->setAlias(self::REWRITE_REQUEST_PATH_ALIAS, 'blog/'.$uri);
        if(trim($uri,"/") == "taglist")//go to page view all tag
        {
             Mage::app()->getRequest()->setControllerName('tag');
             Mage::app()->getRequest()->setActionName('taglist');
             return true;
        }
        if($uri)
        {
			$requestInfo = trim($uri);
			$tmp = explode("_", $requestInfo);
			if($tmp[0] == "adminhtml")
				return true;

			$urlData = $this->analyticUrl($requestInfo);

			if($postId = $urlData->getPostId())//detail post page
			{
				Mage::app()->getRequest()->setControllerName('post');
				Mage::app()->getRequest()->setActionName('view');
				Mage::app()->getRequest()->setParam('id',$postId);
				$contentUrl = explode("/",$uri);
				if(count($contentUrl)>1){
					unset($contentUrl[count($contentUrl)-1]);
					Mage::app()->getRequest()->setParam('cat_id',$this->analyticUrl(implode('/',$contentUrl).'.html')->getCategoryId());
				}	
				return true;
			}
			elseif($tagId = $urlData->getTagId())
			{
				Mage::app()->getRequest()->setControllerName('tag');
				Mage::app()->getRequest()->setActionName('view');
				Mage::app()->getRequest()->setParam('tag_id',$tagId);
				return true;
			}
			elseif($catId = $urlData->getCategoryId())
			{
				Mage::app()->getRequest()->setControllerName('category');
				Mage::app()->getRequest()->setActionName('view');
				Mage::app()->getRequest()->setParam('id',$catId);
				return true;
			}

        }
		return true;
    }
}
