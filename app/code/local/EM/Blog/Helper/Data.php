<?php
class EM_Blog_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED     		= 'blog/blog/enabled';
    const XML_PATH_TITLE       		= 'blog/blog/title';
    const XML_PATH_MENU_LEFT   		= 'blog/blog/menuLeft';
    const XML_PATH_MENU_RIGHT  		= 'blog/blog/menuRoght';
    const XML_PATH_FOOTER_ENABLED   = 'blog/blog/footerEnabled';
    const XML_PATH_LAYOUT      		= 'blog/blog/layout';

    public function isEnabled()
    {
        return Mage::getStoreConfig( self::XML_PATH_ENABLED );
    }
	
	public function isTitle()
    {
        return Mage::getStoreConfig( self::XML_PATH_TITLE );
    }
	
	public function isMenuLeft()
    {
        return Mage::getStoreConfig( self::XML_PATH_MENU_LEFT );
    }
	
	public function isMenuRight()
    {
        return Mage::getStoreConfig( self::XML_PATH_MENU_RIGHT );
    }
	
	public function isFooterEnabled()
    {
        return Mage::getStoreConfig( self::XML_PATH_FOOTER_ENABLED );
    }
	
	public function isLayout()
    {
        return Mage::getStoreConfig( self::XML_PATH_LAYOUT );
    }
	
	public function getUserName()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        return trim("{$customer->getFirstname()} {$customer->getLastname()}");
    }

    public function getRoute(){
        $route = Mage::getStoreConfig('blog/blog/route');
        if (!$route){
                $route = "blog";
        }
        return $route;
    }

    public function getUserEmail()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        return $customer->getEmail();
    }
    
    public function saveAndUpdateUrl($dataBlogUrl,$id,$name)//$flag de nhan biet la update hay cap nhat du lieu,$name= Tag hoac Post
    {

        $modelBlogUrl = Mage::getModel('blog/url');
        $tmp = $modelBlogUrl->getCollection()
                        ->addFieldToFilter($name.'_id',$id)
                        ->getFirstItem();
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $write->query('SET FOREIGN_KEY_CHECKS=0;');
        $modelBlogUrl = Mage::getModel('blog/blogurl')->setData($dataBlogUrl)->setId($tmp->getId())->save();
    }
    public function getNewPageUrl()
    {
       return 'http://localhost/magento_new_2/new-page';
    }
   public function resizeImage($imageName, $width=NULL, $height=NULL, $imagePath=NULL,$type)
   {
       $imagePath = str_replace("/", DS, $imagePath);
	   $imageName = str_replace('emblog/post/','',$imageName);
	  
       $imagePathFull = Mage::getBaseDir('media') . DS . $imagePath . DS . $imageName;

       if($width == NULL && $height == NULL) {
          $width = 100;
          $height = 100;
       }
       $resizePath = $width . 'x' . $height;
       $resizePathFull = Mage::getBaseDir('media') . DS . $imagePath . DS . $type. DS . $resizePath . DS . $imageName;

       if (file_exists($imagePathFull) && !file_exists($resizePathFull)) {
          $imageObj = new Varien_Image($imagePathFull);
          $imageObj->constrainOnly(TRUE);
          //$imageObj->keepAspectRatio(TRUE);
          $imageObj->resize($width,$height);
          $imageObj->save($resizePathFull);
       }

       $imagePath=str_replace(DS, "/", $imagePath);
       return Mage::getBaseUrl("media") . $imagePath . "/" . $type . "/" . $resizePath . "/"  . $imageName;
    }

    public function getStaticBlocks()
    {
        $collection = Mage::getModel('cms/block')->getCollection()->addFieldToFilter('is_active',1);
        $res = array(array('value'=>0,'label'=>$this->__('Please select a static block ...')));
        foreach($collection as $c)
        {
            $res[] = array('value'=>$c->getBlockId(),'label'=>$c->getTitle());
        }
        return $res;
    }

    public function createTreeSelect($parentId,$id,$introText)
    {
       $tree = array();
       $collection = Mage::getModel('blog/category')->getCollection()
                ->addFieldToFilter('level',array('gt'=>0))
                ->addFieldToFilter('parent_id',$parentId);

        if($id)
            $collection->addFieldToFilter('id',array('neq'=>$id))
                       ->addFieldToFilter('path',array('nlike'=>"%/$id/%"));
     
        if($collection->count() > 0)
        {
            foreach($collection as $c)
            {
                $tree[]=array('value'=>$c->getId(),'label'=>$introText.' '.$c->getCatName());
                $tree = array_merge($tree, $this->createTreeSelect($c->getId(), $id,$introText.'-'));
            }
        }
        return $tree;
    }

    public function getCategories($id)
    {
        $res = array(array('value'=>"",'label'=>$this->__('Please select a category ...')));
        $res = array_merge($res, $this->createTreeSelect(1, $id, ''));
        return $res;
    }

	public function editWysiwyg(){
		$config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
		$configPlugins = Mage::getModel('blog/variable_config')->getWysiwygPluginSettings($config);
		$config->setData('plugins',$configPlugins);
		$config->setData('widget_window_url',Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index'));
		$config->setData('directives_url',Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive'));
		$config->setData('directives_url_quoted', preg_quote($config->getData('directives_url')));
		if (Mage::getSingleton('admin/session')->isAllowed('cms/media_gallery')) {
			$config->setData('files_browser_window_url',Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'));
		}
		return $config;
	}
	
	public function setTheme($design,$customLayouUpdateXML,$customLayout,$controller)
    {
          //$design = $tag->getData('custom_design');
          //========= set theme ===========
		  if(empty($design)){
				$design = Mage::getStoreConfig('design/package/name').'/'.Mage::getStoreConfig('design/theme/template');
		  }
          if($design)
          {
              list($package,$theme) = split('[/]', $design);
          }
          else
          {
              $package = 'default';
              $theme = 'default';
          }
          Mage::getSingleton('core/design_package')
              ->setPackageName($package)
              ->setTheme($theme);
          //====================
          $update = Mage::app()->getLayout()->getUpdate();
          $update->addHandle('default');
          $controller->addActionLayoutHandles();
          $controller->loadLayoutUpdates();
          $update->addUpdate($customLayouUpdateXML);
          $controller->generateLayoutXml()->generateLayoutBlocks();
          //======= set template page (1column,2column ...) ============
          //$layoutCode = $tag->getData('custom_layout');
          if($customLayout)
          {
              $layout = Mage::getSingleton('page/config')->getPageLayout($customLayout);
              $template = $layout->getTemplate();
          
              $root = $controller->getLayout()->getBlock('root');
              $root->setTemplate($template);
          }
    }
}

    