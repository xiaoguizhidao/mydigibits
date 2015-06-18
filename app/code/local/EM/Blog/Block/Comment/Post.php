<?php
class EM_Blog_Block_Comment_Post extends Mage_Core_Block_Template
{
    protected $sum = 0 ;
    public function  _prepareLayout() {
        //init toolbar
        $toolbar = $this->getLayout()->createBlock('blog/post_list_toolbar', 'Toolbar');
        
        $toolbar->setDefaultOrder('time');
        if(!$this->getRequest()->getParam('limit')){
            $limit = Mage::getStoreConfig('blog/info/pagesize');
            $_limit = 0;
            foreach($toolbar->getAvailableLimit() as $value){
                    if($limit < $value) break;
                    $_limit = $value;
            }

            Mage::getSingleton('catalog/session')->setLimitPage($_limit);
            $this->getRequest()->setParam('limit',$_limit);
        }
        $toolbar->setLimit((int)Mage::getStoreConfig('blog/info/limit_comment_pagination'));
        $toolbar->setChild('em_blog_pager',$this->getLayout()->createBlock('blog/post_list_toolbar_pager','em_blog_pager'));
        $toolbar->disableExpanded();
        
    	$this->setToolbar($toolbar);
        parent::_prepareLayout();
    }

    public function setSum($sum)
    {
        $this->sum = $sum;
        return $this;
    }

    public function getSum()
    {
        return $this->sum;
    }

    public function getPost()
    {
        return Mage::registry('current_post');
    }

    public function frontTreeComment()
    {
     
       
       $postId = $this->getRequest()->getParam('id');
       $commentList = Mage::getModel('blog/comment')->getCollection()
                        ->addFieldToFilter('post_id',$postId)
                        ->addFieldToFilter('status_comment',array('gt'=>1-Mage::getStoreConfig('blog/info/show_comment_pending')));
     
        $this->setSum($commentList->count());
        $data = $commentList->getData();
        
        $comment = array();
        foreach($data as $d)
        {
           if($d['parent_id'] == $d['id'])
            {
                //$comment[$d['id']]['firstname_comment'] = $d['firstname_comment'];
                //$comment[$d['id']]['lastname_comment'] = $d['lastname_comment'];
                $comment[$d['id']]['comment_content'] = $d['comment_content'];
                $comment[$d['id']]['time_comment'] = $d['time'];
                $comment[$d['id']]['username'] = $d['username'];
                $comment[$d['id']]['id'] = $d['id'];
            }
            else
            {
                if(!isset($comment[$d['parent_id']]['child']))
                    $comment[$d['parent_id']]['child'] = array();
                //$comment[$d['id']]['firstname_comment'] = $d['firstname_comment'];
                //$comment[$d['id']]['lastname_comment'] = $d['lastname_comment'];
                $comment[$d['id']]['comment_content'] = $d['comment_content'];
                $comment[$d['id']]['time_comment'] = $d['time'];
                $comment[$d['id']]['username'] = $d['username'];
                $comment[$d['id']]['id'] = $d['id'];
                $comment[$d['id']]['parent_id'] = $d['parent_id'];
                $comment[$d['parent_id']]['child'][] = $d['id'];
            }
                      

                  
        }
        
        return $comment;

    }

    public function getCommentByPost()
    {
        //Get comment list with the items have level 1
       
       
        $postId = $this->getRequest()->getParam('id');
        $commentList = Mage::getModel('blog/comment')->getCollection()
                ->addFieldToFilter('post_id',$postId)
                ->addFieldToFilter('status_comment',array('gt'=>1-Mage::getStoreConfig('blog/info/show_comment_pending')));
     
        $commentList -> getSelect()->where("main_table.id=main_table.parent_id");
       
        $commentList->setOrder('time','desc');
       
        $commentList->setPageSize((int)Mage::getStoreConfig('blog/info/limit_comment_pagination'));
        $commentList->setCurPage($this->getRequest()->getParam('page',1));
        $this->setCollection($commentList);
        $this->setSize($commentList->count());
        return $commentList;
       
                

       
    }


    public function createHtmlCommentChild($comment,$level,$root)
    {
        $textIndent = $level*20;
        $date = new DateTime($comment['time']);

		$isParent = isset($comment['child']) ? 'parent' : '';
        $html = "<div  class='level$level reply comment-item $isParent'>
                     <div class='comment-item-header'><span>".$comment['username']."</span>
                             <div class='comment-status'>
                                    <small class='date'>".$date->format('l, M j Y, h:iA')."</small>
                                    <div class='comment-button'>
                                            <button name='reply'  class='button' value='Reply' com_id='".$comment['id']."' onclick='addCommentBox(".$comment['id'].");' parent_id='".$comment['parent_id']."'><span><span>".$this->__('Reply')."</span></span></button>
                                    </div>
                             </div>
                     </div>
                     <div class='comment-body'>".$comment['comment_content']."</div>
                     
                     <div id='".$comment['id']."_comment_box'></div>";



        if($isParent)
        {
            foreach($comment['child'] as $c)
            {
                $html .= $this->createHtmlCommentChild($root[$c],$level+1,$root);
            }

        }
        return $html .= "</div>";
    }
}