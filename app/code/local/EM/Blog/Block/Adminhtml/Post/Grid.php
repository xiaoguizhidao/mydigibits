<?php
class EM_Blog_Block_Adminhtml_Post_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('postGrid');
      $this->setDefaultSort('id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
		$collection = Mage::getModel('blog/post')->getCollection()
				->addStoreFilter($this->getRequest()->getParam('store',0))->addAttributeToSelect('*');
		
		$this->setCollection($collection);
     
		parent::_prepareCollection();
		//$this->getCollection()->addWebsiteNamesToResult();
		return $this;
  }

  protected function _prepareColumns()
  {
      $this->addColumn('id', array(
          'header'    => Mage::helper('blog')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('blog')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));
      
      /*$this->addColumn('post_content', array(
          'header'    => Mage::helper('blog')->__('Content'),
          'align'     =>'left',
          'index'     => 'post_content',
      ));*/
      
      $this->addColumn('post_identifier', array(
          'header'    => Mage::helper('blog')->__('Identifier'),
          'align'     =>'left',
          'index'     => 'post_identifier',
      ));

      /*$this->addColumn('image',
            array(
                'header'=> Mage::helper('catalog')->__('Image'),
                'width' => '75px',
                'index' => 'image',
                'filter'    => false,
                'sortable'  => false,
                'renderer'  => 'blog/renderer_image',
        ));*/

		$this->addColumn('author_id', array(
			'header'    => Mage::helper('blog')->__('Author'),
			'align'     =>'left',
			'type'      =>'options',
			'index'     => 'author_id',
			'options'   => Mage::getSingleton('blog/post_attribute_source_author')->getOptionsArray()
		));
       $this->addColumn('created_at', array(
          'header'    => Mage::helper('blog')->__('Created Date'),
          'align'     =>'left',
          'index'     =>'created_at',
          'type'      =>'date',    
      ));
      
       $this->addColumn('status', array(
          'header'    => Mage::helper('blog')->__('Status'),
          'align'     =>'left',
          'type'      =>'options',  
          'index'     =>'status',
          'options'   =>array(1 => 'Enable',0 => 'disable'),  
      ));
       $this->addColumn('allow_comment', array(
          'header'    => Mage::helper('blog')->__('Allow Comment'),
          'align'     =>'left',
          'index'     =>'allow_comment',
          'type'      =>'options',
          'options'   =>array(0 => 'only login user',1 => 'every one',2 => 'stop')
      ));
       /*if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id',
                array(
                    'header'=> Mage::helper('catalog')->__('Websites'),
                    'width' => '100px',
                    'sortable'  => false,
                    'index'     => 'website_id',
                    'type'      => 'options',
                    'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
            ));
        }*/
      

		
		$this->addExportType('*/*/exportCsv', Mage::helper('blog')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('blog')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('post');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('blog')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('blog')->__('Are you sure?')
        ));

        //$statuses = Mage::getSingleton('blog/status')->getOptionArray();
        //$statuses = array(0=>'disable',1=>'Enable');
        //array_unshift($statuses, array('label'=>'', 'value'=>''));
        $statuses = array(
                        array('label'=>'Disabled', 'value'=>'0'),
                        array('label'=>'Enable', 'value'=>'1'),
        );  
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('blog')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('blog')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
  	
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}