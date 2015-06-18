<?php
class EM_Blog_Block_Tag_Tagcloud extends Mage_Core_Block_Template
{
    public function getTagCloud()
    {
         $tag = Mage::getModel('blog/tag');
         $tags = $tag->getTagCloud();
         $maxQty = $tag->getMaxQty();
         $minQty = $tag->getMinQty();


         $maxSize = (int)Mage::getStoreConfig('blog/info/maxsize'); // max font size in pixels
         $minSize = (int)Mage::getStoreConfig('blog/info/minsize'); // min font size in pixels


         $spread = $maxQty - $minQty;
         if ($spread == 0) { // we don't want to divide by zero
                $spread = 1;
         }
         // set the font-size increment
         $step = ($maxSize - $minSize) / ($spread);
         for($i = 0;$i<count($tags);$i++) {
                  // calculate font-size
                  // find the $value in excess of $min_qty
                  // multiply by the font-size increment ($size)
                  // and add the $min_size set above
                  $tags[$i]['size'] = round($minSize + (($tags[$i]['qty'] - $minQty) * $step));
            }
         return $tags;
    }

}

