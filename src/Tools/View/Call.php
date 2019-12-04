<?php 

namespace Tools\View;

class Call extends \Tools\Base
{
	public function getWidget($class="")
	{
		if($class){
			return $widgetHtml = $this->getLayout()->createBlock($class)->toHtml();
		}
		
		return "";
	}
}