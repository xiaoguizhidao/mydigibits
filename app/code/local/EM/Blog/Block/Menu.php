<?php
class EM_Blog_Block_Menu extends Mage_Core_Block_Template
{
	protected $ids = null;
	public function setCurrentCategoryIds(){
		if(!is_array($this->ids)){
			if($curCat = $this->getCurrentCategory())
				$this->ids = $curCat->getPathIds();
			else
				$this->ids = array();
		}
		return $this->ids;
	}
    public function drawMenuBlog($parentCat)
    {
        $childs = $parentCat->getChildrenCategories();

		$html = "";
        $count = 0;
		
		foreach($childs as $c)
        {
            if($c->getChildrenCount() > 0)
                $parenIdClass = "parent";
            if($count == 0)
                $firstLast = "first";
            elseif($count == $childs->count()-1)
                $firstLast = "last";
            $level = $c->getLevel() - 2;
			
			if(!empty($this->ids)){
				
				if(in_array($c->getId(),$this->ids))
				{
					$li = "<li class=' level$level $firstLast $parenIdClass current'>";
					$li .= "<a href='".$c->getUrl()."'><span>".$c->getName()."</span></a>";
				}
				else
				{
					$li = "<li class=' level$level $firstLast $parenIdClass'>";
					$li .= "<a href='".$c->getUrl()."'><span>".$c->getName()."</span></a>";
				}
			}
			else
			{
				$li = "<li class=' level$level $firstLast $parenIdClass'>";
				$li .= "<a href='".$c->getUrl()."'><span>".$c->getName()."</span></a>";
			}
            
            
            if($c->getChildrenCount() > 0)
            {
                $li .= "<ul class='level$level'>";
                $li .= $this->drawMenuBlog($c);
                $li .= "</ul>";
            }
            $li .= "</li>";
            
            $html .= $li;
            $count++;
        }

        return $html;
    }
	
	public function getCurrentCategory()
    {
        return Mage::registry('current_cat');
    }

    public function renderMenuBlog()
    {
        $root = Mage::helper('blog/category')->getRootCategory();
		$this->setCurrentCategoryIds();
        return $this->drawMenuBlog($root);
        
    }
}
