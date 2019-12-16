<?php 
/**
 * author tommy
 */

<<<<<<< HEAD

=======
/**
 * d: 打印美化 
 */
>>>>>>> ignore .gitee
if(!function_exists('d')){
	function d($needle = ""){
		echo "<pre>";
		var_dump($needle);
		echo "</pre>";
	}
}

/**
 * store: 快捷获取Store实例
 */
if(!function_exists('store')){
	function store()
	{
		return new \Tools\Store\Store;
	}
}

/**
 * account: 快捷获取customer account实例
 */
if(!function_exists('account')){
	function account()
	{
		return new \Tools\Account\Account;
	}
}

/**
 * view_call: 快捷调用内置模板
 */
if(!function_exists('view_call')){
	function view_call()
	{
		return new \Tools\View\Call;
	}
}

/**
 * route 返回当前页面的路由地址
 */
if(!function_exists('route')){
	function route()
	{
		return new \Tools\Router\Router;
	}
}

/**
 * current_product: 直接获取当前页面的产品实例
 * 适用于产品详情页
 */
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

/**
 * isMobile: 当前请求是否是移动端请求
 */

if(!function_exists('is_mobile')){
	function is_mobile(){
		$helper = new \Tools\Helper\Server;
		return $helper->isMobile();
	}
}else{
	throw new \Exception("The Method is_mobile is exists", 1);
	
}

/**
 * remove_element: 移除节点
 */
if(!function_exists('remove_element')){
	function remove_element($parentName, $alias)
	{
		$base = new \Tools\Base;
		return $base->getLayout()->unsetChild($parentName, $alias);
	}
}

/**
 * add_element: 添加节点
 */
if(!function_exists('add_element')){
	function add_element($parentName, $elementName, $alias)
	{
		$base = new \Tools\Base;
		return $base->getLayout()->setChild($parentName, $elementName, $alias);
	}
}

/**
 * get_node_parent_name: 添加节点
 */
if(!function_exists('get_node_parent_name')){
	function get_node_parent_name($childName)
	{
		$base = new \Tools\Base;
		return $base->getLayout()->getParentName($childName);
	}
}

/**
 * add container: 添加容器
 */
if(!function_exists('add_container')){
	function add_container($name, $label, array $options = [], $parent = '', $alias = '')
	{
		$base = new \Tools\Base;
		return $base->getLayout()->addContainer($name, $label, $options, $parent, $alias);
	}
}

/**
 * get_node_parent_name: 添加节点
 */
if(!function_exists('set_element_order')){
	function set_element_order($parentName, $childName, $offsetOrSibling, $after = true)
	{
		$base = new \Tools\Base;
		return $base->getLayout()->reorderChild($parentName, $childName, $offsetOrSibling, $after = true);
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