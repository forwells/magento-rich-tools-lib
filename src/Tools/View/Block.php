<?php 

namespace Tools\View;

class Block extends \Tools\Base
{
	public function insertBlock($template, $class = "Magento\Framework\View\Element\Template")
	{
		if($template){
			return $this->getLayout()->createBlock($class)->setTemplate($template)->toHtml();	
		}else{
			throw new Exception("Please Specified The Template You Want To Use!", 1);
		}
		
	}

	public function cmsBlock($blockName)
	{
		$layout = $this->getLayout();
		$cmsBlock = $layout->createBlock("Magento\Cms\Block\Block")->setBlockId($blockName)->toHtml();

		return $cmsBlock;
	}
}