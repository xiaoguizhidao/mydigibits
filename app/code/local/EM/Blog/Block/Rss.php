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

class EM_Blog_Block_Rss extends Mage_Rss_Block_Abstract
{
    protected function _construct()
    {
        /*
        * setting cache to save the rss for 10 minutes
        */
	    $this->setCacheKey('rss_catalog_category_'
            .$this->getRequest()->getParam('cid').'_'
            .$this->getRequest()->getParam('sid')
        );
        $this->setCacheLifetime(600);
    }

    protected function _toHtml()
    {
        $rssObj = Mage::getModel('rss/rss');

        $route = Mage::helper('blog')->getRoute();

        $url = $this->getUrl($route);
        $catId = Mage::helper('core')->urlDecode($this->getRequest()->getParam('cat'));
        $link = '';
        if($catId){
			$category = Mage::getModel('blog/category')->setStoreId(Mage::app()->getStore()->getId())->load($catId);
            $titleEnd = ' - '.$category->getName();
			$collection = $category->getPostCollection();
			$link = $category->getPathUrl().'/';
		}	
        elseif($tagId = Mage::helper('core')->urlDecode($this->getRequest()->getParam('tag_id'))){
			$tag = Mage::getModel('blog/tag')->load($tagId);
            $titleEnd = ' - tag '.$tag->getName();
			$collection = $tag->getPostCollection();
		}
		else{
			$collection = Mage::getModel('blog/post')->getCollection()
						->setStoreId(Mage::app()->getStore()->getId())
						->addAttributeToFilter('status',1)
						->addAttributeToSelect('*');
		}	
		
        $title = Mage::getStoreConfig('blog/info/page_title').$titleEnd;
        $data = array('title' => $title,
                'description' => $title,
                'link'        => $url,
                'charset'     => 'UTF-8'
                );

        if (Mage::getStoreConfig('blog/rss/image') != "")
        {
                $data['image'] = $this->getSkinUrl(Mage::getStoreConfig('blog/rss/rssimage'));
        }

        $rssObj->_addHeader($data);

        $helper = Mage::helper('cms');
		$processor = $helper->getBlockTemplateProcessor();

        if ($collection->getSize()>0) {
                foreach ($collection as $post) {
					    $data = array(
                                                'title'         => $post->getTitle(),
                                                'link'          => Mage::getUrl('blog').$link. $post->getPostIdentifier().'.html',
                                                'description'   => $processor->filter($post->getPostContent()),
												'lastUpdate'		=> strtotime($post->getCreatedAt())
                                                );

                        $rssObj->_addEntry($data);
                }
        }
        return $rssObj->createRssXml();
    }
}
