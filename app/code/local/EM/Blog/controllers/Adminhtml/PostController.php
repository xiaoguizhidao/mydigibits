<?php
class EM_Blog_Adminhtml_PostController extends Mage_Adminhtml_Controller_Action
{
    public function _initPost()
    {
        $id = $this->getRequest()->getParam('id');
		
        if(!Mage::registry('post_data') || Mage::registry('post_data')->getId()!=$id)
        {
			
            $post = Mage::getModel('blog/post')->setStoreId($this->getRequest()->getParam('store',0))->load($id);
			$post->setData('_edit_mode', true);
            if(Mage::registry('post_data'))
                Mage::unregister ('post_data');
            Mage::register('post_data', $post);
			if(Mage::registry('current_post'))
                Mage::unregister ('current_post');
			Mage::register('current_post', $post);
        }
        return Mage::registry('post_data');
    }
	
	/**
     * Initialize post before saving
     */
    protected function _initPostSave()
    {
        $post     = $this->_initPost();
        $postData = $this->getRequest()->getPost('post');
		if(!$postData['custom_design_from'])
			$postData['custom_design_from'] = NULL;
		if(!$postData['custom_design_to'])
			$postData['custom_design_to'] = NULL;
		$post->setStoreId($postData['store']);
		if(!$postData['post_identifier'])
			$url = Mage::helper('blog/post')->friendlyURL($postData['title']);
		else
			$url = Mage::helper('blog/post')->friendlyURL($postData['post_identifier']);
		$postData['post_identifier'] = $url;	
		$post->addData($postData);		
        /**
         * Check "Use Default Value" checkboxes values
         */
        if ($useDefaults = $this->getRequest()->getPost('use_default')) {
            foreach ($useDefaults as $attributeCode) {
                $post->setData($attributeCode, false);
            }
        }

        /**
         * Init product links data (related, upsell, crosssel)
         */
        $links = $this->getRequest()->getPost('links');
        if (isset($links['related']) && !$post->getRelatedReadonly()) {
            $post->setRelatedLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['related']));
        }
        
        /**
         * Initialize product categories
         */
        $categoryIds = $this->getRequest()->getPost('category_ids');
        if (null !== $categoryIds) {
            if (empty($categoryIds)) {
                $categoryIds = array();
            }
            $post->setCategoryIds($categoryIds);
        }

        return $post;
    }

    protected function _initAction() {
            $this->loadLayout()
                    ->_setActiveMenu('blog/items')
                    ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

            return $this;
    }

    public function indexAction() {
            $this->_initAction();
            $this->getLayout()->getBlock('head')->setTitle($this->__('Manage Posts'));
            $this->renderLayout();
    }
	
	/**
     * Get related products grid
     */
    public function relatedGridAction()
    {
        $this->_initPost();
        $this->loadLayout();
        $this->getLayout()->getBlock('blog.post.edit.tab.related')
            ->setProductsRelated($this->getRequest()->getPost('products_related', null));
        $this->renderLayout();
    }
	
	/**
     * Get related posts grid and serializer block
     */
    public function relatedAction()
    {
        $this->_initPost();
        $this->loadLayout();
        $this->getLayout()->getBlock('blog.post.edit.tab.related')
            ->setProductsRelated($this->getRequest()->getPost('products_related', null));
        $this->renderLayout();
    }

    public function categoriesAction()
    {
        $this->_initPost();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function categoriesJsonAction()
    {
        $post = $this->_initPost();

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('blog/adminhtml_post_edit_tab_categories')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

	/*
		Post edit form
	*/
    public function editAction() {
        $post = $this->_initPost();
		if($post->getId())
        {
            $tags = $post->getTags();
            $post->setData('tags',$tags);
            $post->setOrigData('tags',$tags);
			$post->setStore($this->getRequest()->getParam('store',0));
        }

        if ($post->getId()) {
            $this->loadLayout();
            $this->_setActiveMenu('blog/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			if($post->getId())
				$this->_addLeft($this->getLayout()->createBlock('adminhtml/store_switcher'));	
            $this->_addContent($this->getLayout()->createBlock('blog/adminhtml_post_edit'))
                    ->_addLeft($this->getLayout()->createBlock('blog/adminhtml_post_edit_tabs'));

            $this->renderLayout();
        } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Item does not exist'));
                $this->_redirect('*/*/');
        }
    }

    public function newAction() { 
        Mage::register('post_data', Mage::getModel('blog/post')->setStore($this->getRequest()->getParam('store',0)));

        $this->loadLayout();
        $this->_setActiveMenu('blog/items');

        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('blog/adminhtml_post_edit'))
                ->_addLeft($this->getLayout()->createBlock('blog/adminhtml_post_edit_tabs'));

        $this->renderLayout();
    }

    public function autotagAction()
    {
        $model = Mage::getModel('blog/tag');
        $tag = $this->_request->getParam('q');
        //header('Content-type: application/json');
        //echo json_encode($model->getTags($tag));
        $tagList = $model->getTagsAjax($tag);
        $html = "<ul>";
        foreach($tagList as $t)
        {
           $html .= "<li tag='".$t->getName()."' value='".$t->getId()."'><strong>".$t->getName()."</strong></li>";
        }
        $html .= "</ul>";
		$this->getResponse()->setBody($html);        
    }

    public function saveAction() {
		$post = $this->_initPostSave();
        if ($data = $this->getRequest()->getPost()) {
           
        try {
				$post->setData('tag_ids',$this->getRequest()->getParam('tags'));
				$post->save();
					
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('blog')->__('Post was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $post->getId(),'store'=>$data['post']['store']));

                        return;
                }
                $this->_redirect('*/*/',array('store'=>$data['post']['store']));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'),'store'=>$data['post']['store']));
                return;
            }
        }
    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Unable to find item to save'));
    $this->_redirect('*/*/');
    }

    public function deleteAction() {
            if( $this->getRequest()->getParam('id') > 0 ) {
                    try {
                            $model = Mage::getModel('blog/post')->load($this->getRequest()->getParam('id'));
                            $path = Mage::getBaseDir('media').DS."em_blog".DS."posts".DS;
                            $oldPictureLink = $model->getImage();
                            if(is_file($path.$oldPictureLink))
                                unlink($path.$oldPictureLink);

                              //Remove old picture thumbnail
                              $thumnailWidth = Mage::getStoreConfig('blog/info/thumbnail_width');
                              $thumnailHeight = Mage::getStoreConfig('blog/info/thumbnail_height');
                              $resizePathThumbnail = $thumnailWidth."x".$thumnailHeight;
                              $thumbnailLink = $path."thumbnail".DS.$resizePathThumbnail.DS.$oldPictureLink;
                              if(is_file($thumbnailLink))
                                  unlink($thumbnailLink);

                              //Remove thumbnail at admin
                              if(is_file($path."admin".DS."50x50".DS.$oldPictureLink))
                                  unlink($path."admin".DS."50x50".DS.$oldPictureLink);
                              $model->delete();

                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                            $this->_redirect('*/*/');
                    } catch (Exception $e) {
                            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    }
            }
            $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $postIds = $this->getRequest()->getParam('post');
        if(!is_array($postIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                $path = Mage::getBaseDir('media').DS."em_blog".DS."posts".DS;
                foreach ($postIds as $postId) {
                    $post = Mage::getModel('blog/post')->load($postId);
                    
                    $oldPictureLink = $post->getImage();
                    if(is_file($path.$oldPictureLink))
                        unlink($path.$oldPictureLink);

                      //Remove old picture thumbnail
                      $thumnailWidth = Mage::getStoreConfig('blog/info/thumbnail_width');
                      $thumnailHeight = Mage::getStoreConfig('blog/info/thumbnail_height');
                      $resizePathThumbnail = $thumnailWidth."x".$thumnailHeight;
                      $thumbnailLink = $path."thumbnail".DS.$resizePathThumbnail.DS.$oldPictureLink;
                      if(is_file($thumbnailLink))
                          unlink($thumbnailLink);

                      //Remove thumbnail at admin
                      if(is_file($path."admin".DS."50x50".DS.$oldPictureLink))
                          unlink($path."admin".DS."50x50".DS.$oldPictureLink);
                    $post->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($postIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $postIds = $this->getRequest()->getParam('post');
        if(!is_array($postIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($postIds as $postId) {
                    $blog = Mage::getSingleton('blog/post')
                        ->load($postId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($postIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'post.csv';
        $content    = $this->getLayout()->createBlock('blog/adminhtml_post_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'post.xml';
        $content    = $this->getLayout()->createBlock('blog/adminhtml_post_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    public function validateAction()
    {
        $postData = $this->getRequest()->getPost('post');
		$useDefaults = $this->getRequest()->getPost('use_default');
		$response = new Varien_Object();
		if(in_array('post_identifier',$useDefaults))
			$response->setError(false);
		else{
			if(!$postData['post_identifier'])
				$url = Mage::helper('blog/post')->friendlyURL($postData['title']);
			else
				$url = Mage::helper('blog/post')->friendlyURL($postData['post_identifier']);
			$urlInstance = Mage::getModel('blog/url');
			if($urlInstance->validate($url.'.html','post_id',$postData['entity_id'])){
				$response->setError(false);
				$response->setError(true);
				$response->setAttribute("post_identifier");
				$response->setMessage(Mage::helper('blog')->__("The value of post identifier is unique"));
				$response->setData('post_identifier',$url );
			}	
			else
				$response->setError(false);
		}	
        $this->getResponse()->setBody($response->toJson());
    }
	
}