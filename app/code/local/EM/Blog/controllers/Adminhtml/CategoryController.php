<?php
class EM_Blog_Adminhtml_CategoryController extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * Initialize requested category and put it into registry.
     * Root category can be returned, if inappropriate store/category is specified
     *
     * @param bool $getRootInstead
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCategory($getRootInstead = false)
    {
		
	
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Categories'))
             ->_title($this->__('Manage Categories'));

        $categoryId = (int) $this->getRequest()->getParam('id',false);
        $storeId    = (int) $this->getRequest()->getParam('store');
        $category = Mage::getModel('blog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::helper('blog/category')->getRootCategory()->getEntityId();
                if (!in_array($rootId, $category->getPathIds())) {
                    // load root category instead wrong one
                    if ($getRootInstead) {
                        $category->load($rootId);
                    }
                    else {
                        $this->_redirect('*/*/', array('_current'=>true, 'id'=>null));
                        return false;
                    }
                }
            }
        }

        if ($activeTabId = (string) $this->getRequest()->getParam('active_tab_id')) {
            Mage::getSingleton('admin/session')->setActiveTabId($activeTabId);
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);
        Mage::getSingleton('cms/wysiwyg_config')->setStoreId($this->getRequest()->getParam('store'));
        return $category;
    }
	
	/**
     * Tree Action
     * Retrieve category tree
     *
     * @return void
     */
    public function treeAction()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        $categoryId = (int) $this->getRequest()->getParam('id');

        if ($storeId) {
            if (!$categoryId) {
                $rootId = Mage::helper('blog/category')->getRootCategory()->getId();
                $this->getRequest()->setParam('id', $rootId);
            }
        }

        $category = $this->_initCategory(true);

        $block = $this->getLayout()->createBlock('blog/adminhtml_category_tree');
        $root  = $block->getRoot();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
            'data' => $block->getTree(),
            'parameters' => array(
                'text'        => $block->buildNodeName($root),
                'draggable'   => false,
                'allowDrop'   => ($root->getIsVisible()) ? true : false,
                'id'          => (int) $root->getId(),
                'expanded'    => (int) $block->getIsWasExpanded(),
                'store_id'    => (int) $block->getStore()->getId(),
                'category_id' => (int) $category->getId(),
                'root_visible'=> (int) $root->getIsVisible()
        ))));
    }

    protected function _initAction() {
            $this->loadLayout()
                    ->_setActiveMenu('blog/items')
                    ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

            return $this;
    }

    /**
    * Blog categories index action
    */
    public function indexAction()
    {
        $this->_forward('edit');
    }
	
	/**
     * Add new category form
     */
    public function addAction()
    {
        Mage::getSingleton('admin/session')->unsActiveTabId();
        $this->initEditTemplate();
    }
	
	public function editAction(){
		$this->initEditTemplate();
	}

    /**
     * Edit category page
     */
    public function initEditTemplate()
    {
        $params['_current'] = true;
        $redirect = false;

        $storeId = (int) $this->getRequest()->getParam('store');
        $parentId = (int) $this->getRequest()->getParam('parent');
        $_prevStoreId = Mage::getSingleton('admin/session')
            ->getLastViewedStore(true);

        if (!empty($_prevStoreId) && !$this->getRequest()->getQuery('isAjax')) {
            $params['store'] = $_prevStoreId;
            $redirect = true;
        }

        $categoryId = (int) $this->getRequest()->getParam('id');
        $_prevCategoryId = Mage::getSingleton('admin/session')
            ->getLastEditedCategory(true);


        if ($_prevCategoryId
            && !$this->getRequest()->getQuery('isAjax')
            && !$this->getRequest()->getParam('clear')) {
           // $params['id'] = $_prevCategoryId;
            $this->getRequest()->setParam('id',$_prevCategoryId);
            //$redirect = true;
        }

         if ($redirect) {
            $this->_redirect('*/*/edit', $params);
            return;
        }

        if ($storeId && !$categoryId && !$parentId) {
            //$store = Mage::app()->getStore($storeId);
            $_prevCategoryId = (int) Mage::helper('blog/category')->getRootCategory()->getId();
            $this->getRequest()->setParam('id', $_prevCategoryId);
        }

        if (!($category = $this->_initCategory(true))) {
            return;
        }

        $this->_title($categoryId ? $category->getName() : $this->__('New Category'));

        /**
         * Check if we have data in session (if duering category save was exceprion)
         */
        $data = Mage::getSingleton('adminhtml/session')->getCategoryData(true);
        if (isset($data['general'])) {
            $category->addData($data['general']);
        }

        /**
         * Build response for ajax request
         */
        if ($this->getRequest()->getQuery('isAjax')) {
            // prepare breadcrumbs of selected category, if any
            $breadcrumbsPath = $category->getPath();
            if (empty($breadcrumbsPath)) {
                // but if no category, and it is deleted - prepare breadcrumbs from path, saved in session
                $breadcrumbsPath = Mage::getSingleton('admin/session')->getDeletedPath(true);
                if (!empty($breadcrumbsPath)) {
                    $breadcrumbsPath = explode('/', $breadcrumbsPath);
                    // no need to get parent breadcrumbs if deleting category level 1
                    if (count($breadcrumbsPath) <= 1) {
                        $breadcrumbsPath = '';
                    }
                    else {
                        array_pop($breadcrumbsPath);
                        $breadcrumbsPath = implode('/', $breadcrumbsPath);
                    }
                }
            }

            Mage::getSingleton('admin/session')
                ->setLastViewedStore($this->getRequest()->getParam('store'));
            Mage::getSingleton('admin/session')
                ->setLastEditedCategory($category->getId());
//            $this->_initLayoutMessages('adminhtml/session');
            $this->loadLayout();

            $eventResponse = new Varien_Object(array(
                'content' => $this->getLayout()->getBlock('category.edit')->getFormHtml()
                    . $this->getLayout()->getBlock('category.tree')
                    ->getBreadcrumbsJavascript($breadcrumbsPath, 'editingCategoryBreadcrumbs'),
                'messages' => $this->getLayout()->getMessagesBlock()->getGroupedHtml(),
            ));

            Mage::dispatchEvent('category_prepare_ajax_response', array(
                'response' => $eventResponse,
                'controller' => $this
            ));

            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode($eventResponse->getData())
            );

            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('blog/categories');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
            ->setContainerCssClass('catalog-categories');

        $this->_addBreadcrumb(Mage::helper('catalog')->__('Manage Catalog Categories'),
             Mage::helper('blog')->__('Manage Categories')
        );

        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($storeId);
        }

        $this->renderLayout();
    }

    public function newAction() {
        $this->_initCurrentCategory();
                    $this->loadLayout();
                    $this->_setActiveMenu('blog/items');

                    $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
                    $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

                    $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

                    $this->_addContent($this->getLayout()->createBlock('blog/adminhtml_category_edit'))
                            ->_addLeft($this->getLayout()->createBlock('blog/adminhtml_category_edit_tabs'));

                    $this->renderLayout();
            //$this->_forward('edit');
    }

	/**
     * WYSIWYG editor action for ajax request
     *
     */
    public function wysiwygAction()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $content = $this->getLayout()->createBlock('adminhtml/catalog_helper_form_wysiwyg_content', '', array(
            'editor_element_id' => $elementId,
            'store_id'          => $storeId,
            'store_media_url'   => $storeMediaUrl,
        ));

        $this->getResponse()->setBody($content->toHtml());
    }
	
	/**
     * Get tree node (Ajax version)
     */
    public function categoriesJsonAction()
    {
        if ($this->getRequest()->getParam('expand_all')) {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(true);
        } else {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(false);
        }
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('blog/adminhtml_category_tree')
                    ->getTreeJson($category)
            );
        }
    }
	
	/**
     * Category save
     */
    public function saveAction()
    {
        if (!$category = $this->_initCategory()) {
            return;
        }

        $storeId = $this->getRequest()->getParam('store');
        $refreshTree = 'false';
        if ($data = $this->getRequest()->getPost()) {
			if(!$data['general']['url_key'])
				$url = Mage::helper('blog/post')->friendlyURL($data['general']['name']);
			else
				$url = Mage::helper('blog/post')->friendlyURL($data['general']['url_key']);
			$data['general']['url_key'] = $url;	
			
            $category->addData($data['general']);
            if (!$category->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
				
                if (!$parentId) {
                    if ($storeId) {
                        $parentId = Mage::helper('blog/category')->getRootCategory()->getId();
                    }
                    else {
                        $parentId = EM_Blog_Model_Category::TREE_ROOT_ID;
                    }
                }
                $parentCategory = Mage::getModel('blog/category')->load($parentId);
                $category->setPath($parentCategory->getPath());
            }

            /**
             * Process "Use Config Settings" checkboxes
             */
            if ($useConfig = $this->getRequest()->getPost('use_config')) {
                foreach ($useConfig as $attributeCode) {
                    $category->setData($attributeCode, null);
                }
            }

            /**
             * Create Permanent Redirect for old URL key
             */
            if ($category->getId() && isset($data['general']['url_key_create_redirect']))
            // && $category->getOrigData('url_key') != $category->getData('url_key')
            {
                $category->setData('save_rewrites_history', (bool)$data['general']['url_key_create_redirect']);
            }

            //$category->setAttributeSetId($category->getDefaultAttributeSetId());
            if (isset($data['category_products']) &&
                !$category->getProductsReadonly()) {
                $posts = array();
                parse_str($data['category_products'], $posts);
                $category->setPostedPosts($posts);
            }

/*            Mage::dispatchEvent('catalog_category_prepare_save', array(
                'category' => $category,
                'request' => $this->getRequest()
            ));*/
			
			/**
             * Proceed with $_POST['use_config']
             * set into category model for proccessing through validation
             */
            $category->setData("use_post_data_config", $this->getRequest()->getPost('use_config'));

            try {
                $validate = $category->validate();
                if ($validate !== true) {
                    foreach ($validate as $code => $error) {
                        if ($error === true) {
                            Mage::throwException(Mage::helper('catalog')->__('Attribute "%s" is required.', $category->getResource()->getAttribute($code)->getFrontend()->getLabel()));
                        }
                        else {
                            Mage::throwException($error);
                        }
                    }
                }

                /**
                 * Check "Use Default Value" checkboxes values
                 */
                if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                    foreach ($useDefaults as $attributeCode) {
                        $category->setData($attributeCode, false);
                    }
                }

                /**
                 * Unset $_POST['use_config'] before save
                 */
                $category->unsetData('use_post_data_config');
				
				$category->save()->saveUrlRewrite(true);
				
	
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('The category has been saved.'));
                $refreshTree = 'true';
            }
            catch (Exception $e){
                $this->_getSession()->addError($e->getMessage())
                    ->setCategoryData($data);
                $refreshTree = 'false';
            }
        }
        $url = $this->getUrl('*/*/edit', array('_current' => true, 'id' => $category->getId()));
        $this->getResponse()->setBody(
            '<script type="text/javascript">parent.updateContent("' . $url . '", {}, '.$refreshTree.');</script>'
        );
    }

    /**
     * Move category action
     */
    public function moveAction()
    {
        $category = $this->_initCategory();
        if (!$category) {
            $this->getResponse()->setBody(Mage::helper('catalog')->__('Category move error'));
            return;
        }
		
		/**
         * New parent category identifier
         */
        $parentNodeId   = $this->getRequest()->getPost('pid', false);
        /**
         * Category id after which we have put our category
         */
        $prevNodeId     = $this->getRequest()->getPost('aid', false);
		
		/* Validate url path before move */
		if($category->getParentId() != $parentNodeId){
			$parentCategory = Mage::getModel('blog/category')->load($parentNodeId);
			if($parentPathUrl = $parentCategory->getPathUrl())
				$newUrlPath = sprintf('%s/%s.html',$parentPathUrl,$category->getUrlKey());
			else
				$newUrlPath = sprintf('%s.html',$category->getUrlKey());
			$urlInstance = Mage::getModel('blog/url');
			if($urlInstance->validate($newUrlPath))
				$this->getResponse()->setBody(Mage::helper('blog')->__("The path %s is unique",$newUrlPath));
			else{
				try {
					$category->move($parentNodeId, $prevNodeId)->saveUrlRewrite(true);
					$this->getResponse()->setBody("SUCCESS");
				}
				catch (Mage_Core_Exception $e) {
					$this->getResponse()->setBody($e->getMessage());
				}
				catch (Exception $e){
					$this->getResponse()->setBody(Mage::helper('catalog')->__('Category move error'.$e));
					Mage::logException($e);
				}
			}
		}
		else{
			try {
				$category->move($parentNodeId, $prevNodeId);
				$this->getResponse()->setBody("SUCCESS");
			}
			catch (Mage_Core_Exception $e) {
				$this->getResponse()->setBody($e->getMessage());
			}
			catch (Exception $e){
				$this->getResponse()->setBody(Mage::helper('catalog')->__('Category move error'.$e));
				Mage::logException($e);
			}
		}	
    }

    /**
     * Delete category action
     */
    public function deleteAction()
    {
        if ($id = (int) $this->getRequest()->getParam('id')) {
            try {
                $category = Mage::getModel('blog/category')->load($id);
				
				/* Remove Product in Category */
				$category->setPostedPosts(array())->save();
				
                //Mage::dispatchEvent('catalog_controller_category_delete', array('category'=>$category));

                Mage::getSingleton('admin/session')->setDeletedPath($category->getPath());

                $category->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('blog')->__('The category has been deleted.'));
            }
            catch (Mage_Core_Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current'=>true)));
                return;
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('An error occurred while trying to delete the category.'));
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current'=>true)));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current'=>true, 'id'=>null)));
    }

    /**
     * Grid Action
     * Display list of posts related to current category
     *
     * @return void
     */
    public function gridAction()
    {
        if (!$category = $this->_initCategory(true)) {
            return;
        }
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('blog/adminhtml_category_edit_tab_post', 'category.post.grid')
                ->toHtml()
        );
    }

    /**
    * Build response for refresh input element 'path' in form
    */
    public function refreshPathAction()
    {
        if ($id = (int) $this->getRequest()->getParam('id')) {
            $category = Mage::getModel('blog/category')->load($id);
            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(array(
                   'id' => $id,
                   'path' => $category->getPath(),
                ))
            );
        }
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('blog/categories');
    }
	
	public function validateAction()
    {
        $categoryData = $this->getRequest()->getPost('general');
		if(!$categoryData['url_key'])
			$url = Mage::helper('blog/post')->friendlyURL($categoryData['name']);
		else
			$url = Mage::helper('blog/post')->friendlyURL($categoryData['url_key']);
	
		$response = new Varien_Object();
		$newUrlPath = '';
		$id = (int) $this->getRequest()->getParam('id',false);
		/* Category exists */
		if($id){
			$category = Mage::getModel('blog/category')->load($id);
			if($category->getLevel() == 1){
				$response->setError(false);
			}
			else{
				$oldUrlPathArray = explode('/',$category->getPathUrl());
				if($url == $oldUrlPathArray[count($oldUrlPathArray)-1])// url_key is not change
					$response->setError(false);
				else{
					/* Create new url path to validate */
					unset($oldUrlPathArray[count($oldUrlPathArray)-1]);
					$oldUrlPathArray[] = $url;
					$newUrlPath = sprintf('%s.html',implode('/',$oldUrlPathArray));
				}	
			}
		}
		// Create new category
		else{ 
			$parentId = $this->getRequest()->getParam('parent');
			if (!$parentId) {
				if ($storeId = $this->getRequest()->getParam('store')) {
					$parentId = Mage::helper('blog/category')->getRootCategory()->getId();
				}
				else {
					$parentId = EM_Blog_Model_Category::TREE_ROOT_ID;
				}
			}
			if($parentId == EM_Blog_Model_Category::TREE_ROOT_ID) // Create new root category
				$response->setError(false);
			else{
				$parent = Mage::getModel('blog/category')->load($parentId);
				if($parent->getPathUrl())
					$newUrlPath = sprintf('%s/%s.html',$parent->getPathUrl(),$url);
				else
					$newUrlPath = sprintf('%s.html',$url);
			}	
		}
		/* Validate new url path */
		if($newUrlPath){
			$urlInstance = Mage::getModel('blog/url');
			if($urlInstance->validate($newUrlPath,'category_id',$id)){
				$response->setError(true);
				$response->setAttribute("url_key");
				$response->setMessage(Mage::helper('blog')->__("The path %s is unique",$newUrlPath));
				$response->setData('url_key',$url );
			}
			else{
				$response->setError(false);
			}			
		}
		
        $this->getResponse()->setBody($response->toJson());
    }
}
