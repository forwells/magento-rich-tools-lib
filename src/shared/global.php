<?php 
/**
 * author tommy
 */
/**
 * d: 打印美化 
 */
if(!function_exists('d')){
	function d($needle = ""){
		echo "<pre>";
		var_dump($needle);
		echo "</pre>";
	}
}
/**
 * js debug info : 控制台错误信息
 */
if(!function_exists('console_log')){
	function console_log($params = ""){
		if(!$params){
			return "<script>console.log('debug params is null or disabled!')</script>";
		}
		return "<script>console.log(".$params.")</script>";
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

/**
 * cms_block 后台Cms block
 */
if(!function_exists('cms_block')){
	function cms_block($blockName = "")
	{
		$view = new \Tools\View\Block;
		return $view->cmsBlock($blockName);
	}
}

/**	
 * catalog_url
 */
if(!function_exists('catalog_url')){
	function catalog_url()
	{
		return store()->getMediaUrl() . 'catalog/product';
	}
}

/**	
 * get_helper
 */
if(!function_exists('get_helper')){
	function get_helper($helper_class)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		return $objectManager->get($helper_class);
	}
}

/**	
 * object_manager
 */
if(!function_exists('class_manager')){
	function class_manager()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		return $objectManager;
	}
}


/**	
 * product_advance
 */
if(!function_exists('product_advance')){
	function product_advance($sku){
		$sku = trim($sku);
		$sql = "select available_stock,presell_status,presell_residue_num,presell_create_time,presell_end_time from fa_item WHERE sku='{$sku}' and is_del = 1;";
		$result = class_manager()->get(\Tools\Database\Glass::class)->query($sql);
		$qty = 0;
		$status = 0;//0售罄，1在售，2预售
		if(!empty($result->row)){
			$row = $result->row;
			if($row['available_stock'] <= 0){
				$presell_create_time = strtotime($row['presell_create_time']);
				$presell_end_time = strtotime($row['presell_end_time']);
				if($row['presell_status'] == 1 && $row['presell_residue_num'] > 0 && $presell_create_time < time() && $presell_end_time > time()){
					$status = 2;
					$qty = $row['presell_residue_num'];
				}
			}else{
				$status = 1;
				$qty = $row['available_stock'];
			}
		}
		return array('status'=>$status,'true_qty'=>$qty);
	}
}

/**	
 * browse_info
 */
if(!function_exists('browse_info')){
	function browse_info() {
		if (!empty($_SERVER['HTTP_USER_AGENT'])) {
			$br = $_SERVER['HTTP_USER_AGENT'];
			if (preg_match('/MSIE/i', $br)) {
				$br = 'MSIE';
			} else if (preg_match('/Firefox/i', $br)) {
				$br = 'Firefox';
			} else if (preg_match('/Chrome/i', $br)) {
				$br = 'Chrome';
			} else if (preg_match('/Safari/i', $br)) {
				$br = 'Safari';
			} else if (preg_match('/Opera/i', $br)) {
				$br = 'Opera';
			} else {
				$br = 'Other';
			}
			return $br;
		} else {
			return 'unknow';
		}
	}
}