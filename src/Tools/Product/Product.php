<?php 

namespace Tools\Product;

class Product extends \Tools\Base
{
	public function getCurrentProduct()
	{
		return $this->getRegistry()->registry('current_product');
	}

	public function getProductById($id = ''){
		if($id){
			
		}
	}
}