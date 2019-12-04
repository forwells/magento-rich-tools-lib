<?php 
/**
 * author tommy
 */


if(!function_exists('d')){
	function d($needle = ""){
		echo "<pre>";
		var_dump($needle);
		echo "</pre>";
	}
}


if(!function_exists('store')){
	function store()
	{
		return new \Tools\Store\Store;
	}
}

if(!function_exists('account')){
	function account()
	{
		return new \Tools\Account\Account;
	}
}

if(!function_exists('view_call')){
	function view_call()
	{
		return new \Tools\View\Call;
	}
}

if(!function_exists('route')){
	function route()
	{
		return new \Tools\Router\Router;
	}
}

if(!function_exists('current_product')){
	function current_product()
	{
		$product = new \Tools\Product\Product;
		return $product->getCurrentProduct();
	}
}
/**
 * Block : 快捷在phtml使用Block
 */
if(!function_exists('block')){
	function block($templateName = "", $class = "Magento\Framework\View\Element\Template")
	{
		$block = new \Tools\View\Block;
		return $block->insertBlock($templateName, $class);
	}
}

/**
 * Data: 当前产品的评论评分平均分
 */
if(!function_exists('average_review_rating')){
	function average_review_rating()
	{
		$rating = new \Tools\Review\Summary;
		return $rating->getAverageReviewVote();
	}
}