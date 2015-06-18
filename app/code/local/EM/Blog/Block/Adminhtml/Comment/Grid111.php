<?php

class EM_Blog_Block_Adminhtml_Comment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
        //exit('abc');
      parent::__construct();
      $this->setId('commentGrid');
      $this->setDefaultSort('id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
   
          
      $collection = Mage::getResourceModel('blog/post_collection');
      //$collection->addAttributeToSelect('name');      
      $collection->getSelect()->join(array('customer' => 'customer_entity'), 'customer.entity_id = post_by', array(
                'email'             => 'email',
                ));
     
      /*$firstname = Mage::getResourceSingleton('customer/customer')->getAttribute('firstname');
      //$lastname = Mage::getResourceSingleton('customer/customer')->getAttribute('lastname');  
      $collection = Mage::getModel('blog/post')->getCollection();
      $collection->getSelect()
            ->joinLeft(
                array('customer_email_table'=>$email->getBackend()->getTable()),
                'customer_email_table.entity_id=main_table.post_by
                 AND customer_email_table.attribute_id = '.(int) $email->getAttributeId() . '
                 ',
                array('email'=>'value')
             );/*->joinLeft(
                array('customer_firstname_table'=>$firstname->getBackend()->getTable()),
                'customer_firstname_table.entity_id=main_table.post_by
                 AND customer_firstname_table.attribute_id = '.(int) $firstname->getAttributeId() . '
                 ',
                array('firstname'=>'value')
             );*/
   
      //$blogModel->getListPost($filter);
      //print_r($tmp);exit;
     //print_r($collection->getSelect());exit;
     //print_r($collection);exit;
      //print_r((string)$collection->getSelect());exit;      
      $this->setCollection($collection);
     
      //print_r( $this->getCollection());exit;
      return parent::_prepareCollection();
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
      
      $this->addColumn('content', array(
          'header'    => Mage::helper('blog')->__('Content'),
          'align'     =>'left',
          'index'     => 'content',
      ));
      
      $this->addColumn('link', array(
          'header'    => Mage::helper('blog')->__('Link'),
          'align'     =>'left',
          'index'     => 'link',
      ));
      
      $this->addColumn('email', array(
          'header'    => Mage::helper('blog')->__('Email'),
          'align'     =>'left',
          'index'     => 'email',
      ));
       /*$this->addColumn('lastname', array(
          'header'    => Mage::helper('blog')->__('Last Name'),
          'align'     =>'left',
          'index'     => 'lastname',
      ));*/
       $this->addColumn('post_on', array(
          'header'    => Mage::helper('blog')->__('Post On'),
          'align'     =>'left',
          'index'     => 'post_on',
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
          'index'     => 'allow_comment',
      ));
      

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('blog')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      /*$this->addColumn('status', array(
          'header'    => Mage::helper('blog')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('blog')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('blog')->__('Edit'),
                        'url'       => array('base'=> '*///*/edit'),
                        /*'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));*/
		
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

        $statuses = Mage::getSingleton('blog/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
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