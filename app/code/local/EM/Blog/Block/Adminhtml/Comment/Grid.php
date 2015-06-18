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
  	  
      $collection = Mage::getResourceModel('blog/comment_collection');
      $attributeId = Mage::getResourceModel('eav/entity_attribute')
		->getIdByCode('blog_post','title');
      $collection->getSelect()->joinLeft(array('bp' => $collection->getTable('blog/post_entity_varchar')), 'bp.entity_id = main_table.post_id AND bp.attribute_id='.$attributeId.' AND bp.store_id=0', array(
                'title'             => 'bp.value',
                ));
      
      //print_r((string)$collection->getSelect());exit;  
	  $this->setCollection($collection);
	  
     
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
      $this->addColumn('comment_content', array(
          'header'    => Mage::helper('blog')->__('Comment'),
          'align'     =>'left',
          'index'     => 'comment_content',
      ));
      $this->addColumn('time', array(
          'header'    => Mage::helper('blog')->__('Time'),
          'align'     =>'left',
          'index'     => 'time',
      	  'type'      =>'date',
      ));

      $this->addColumn('email', array(
          'header'    => Mage::helper('blog')->__('Post by'),
          'align'     =>'left',
          'index'     => 'email',
          'default'   => 'GUESS',
      ));
       $this->addColumn('title', array(
          'header'    => Mage::helper('blog')->__('Post On'),
          'align'     =>'left',
          'index'     => 'title',
      ));
      
       $this->addColumn('status_comment', array(
          'header'    => Mage::helper('blog')->__('Status'),
          'align'     =>'left',
          'index'     => 'status_comment',
  	  'type'	=> 'options',
    	  'options' => array('0'=>'Disabled','1'=>'Pending','2'=>'Approved')
      ));
		$this->addExportType('*/*/exportCsv', Mage::helper('blog')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('blog')->__('XML'));

      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
    		
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('comment');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('blog')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('blog')->__('Are you sure?')
        ));

/*        $statuses = Mage::getSingleton('blog/status')->getOptionArray();
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        print_r($statuses);die;
*/
        $statuses = array(
        				array('label'=>'Disabled', 'value'=>'0'),
        				array('label'=>'Pending', 'value'=>'1'),
        				array('label'=>'Approved', 'value'=>'2'),
        );       
         //print_r($statuses);die;
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