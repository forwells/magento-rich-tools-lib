<?php 

namespace Tools\View;

class Block extends \Tools\Base
{
	public function insertBlock($template = "", $class = "Magento\Framework\View\Element\Template")
	{
		if($template){
			return $this->getLayout()->createBlock($class)->setTemplate($template)->toHtml();	
		}else{
			throw new Exception("Please Specified The Template You Want To Use!", 1);
		}
		
	}
}