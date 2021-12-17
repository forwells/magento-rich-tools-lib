<?php

/**
 * author tommy
 */

/**
 * d: 打印美化 
 */
if (!function_exists('dd')) {
	function dd($needle = '')
	{
		if (function_exists('dump')) {
			dump($needle);
			exit;
		} else {
			echo "<pre>";
			var_dump($needle);
			echo "</pre>";
			exit;
		}
	}
}
if (!function_exists('d')) {
	function d($needle = "")
	{
		echo "<pre>";
		var_dump($needle);
		echo "</pre>";
		exit;
	}
}
/**
 * js debug info : 控制台错误信息
 */
if (!function_exists('console_log')) {
	function console_log($params = "")
	{
		if (!$params) {
			echo "<script>console.log('debug params is null or disabled!')</script>";
		}
		echo "<script>console.log(" . $params . ")</script>";
	}
}
/**
 * store: 快捷获取Store实例
 */
if (!function_exists('store')) {
	function store()
	{
		return new \Tools\Store\Store;
	}
}

/**
 * account: 快捷获取customer account实例
 */
if (!function_exists('account')) {
	function account()
	{
		return new \Tools\Account\Account;
	}
}

/**
 * view_call: 快捷调用内置模板
 */
if (!function_exists('view_call')) {
	function view_call()
	{
		return new \Tools\View\Call;
	}
}

/**
 * route 返回当前页面的路由地址
 */
if (!function_exists('route')) {
	function route()
	{
		return new \Tools\Router\Router;
	}
}

/**
 * current_product: 直接获取当前页面的产品实例
 * 适用于产品详情页
 */
if (!function_exists('current_product')) {
	function current_product()
	{
		$product = new \Tools\Product\Product;
		return $product->getCurrentProduct();
	}
}
/**
 * Block : 快捷在phtml使用Block
 */
if (!function_exists('block')) {
	function block($templateName = "", $class = "Magento\Framework\View\Element\Template")
	{
		$block = new \Tools\View\Block;
		return $block->insertBlock($templateName, $class);
	}
}

/**
 * Data: 当前产品的评论评分平均分
 */
if (!function_exists('average_review_rating')) {
	function average_review_rating()
	{
		$rating = new \Tools\Review\Summary;
		return $rating->getAverageReviewVote();
	}
}

/**
 * isMobile: 当前请求是否是移动端请求
 */

if (!function_exists('is_mobile')) {
	function is_mobile()
	{
		$helper = new \Tools\Helper\Server;
		return $helper->isMobile();
	}
} else {
	throw new \Exception("The Method is_mobile is exists", 1);
}

/**
 * remove_element: 移除节点
 */
if (!function_exists('remove_element')) {
	function remove_element($parentName, $alias)
	{
		$base = new \Tools\Base;
		return $base->getLayout()->unsetChild($parentName, $alias);
	}
}

/**
 * add_element: 添加节点
 */
if (!function_exists('add_element')) {
	function add_element($parentName, $elementName, $alias)
	{
		$base = new \Tools\Base;
		return $base->getLayout()->setChild($parentName, $elementName, $alias);
	}
}

/**
 * get_node_parent_name: 添加节点
 */
if (!function_exists('get_node_parent_name')) {
	function get_node_parent_name($childName)
	{
		$base = new \Tools\Base;
		return $base->getLayout()->getParentName($childName);
	}
}

/**
 * add container: 添加容器
 */
if (!function_exists('add_container')) {
	function add_container($name, $label, array $options = [], $parent = '', $alias = '')
	{
		$base = new \Tools\Base;
		return $base->getLayout()->addContainer($name, $label, $options, $parent, $alias);
	}
}

/**
 * get_node_parent_name: 添加节点
 */
if (!function_exists('set_element_order')) {
	function set_element_order($parentName, $childName, $offsetOrSibling, $after = true)
	{
		$base = new \Tools\Base;
		return $base->getLayout()->reorderChild($parentName, $childName, $offsetOrSibling, $after = true);
	}
}

/**
 * cms_block 后台Cms block
 */
if (!function_exists('cms_block')) {
	function cms_block($blockName = "")
	{
		$view = new \Tools\View\Block;
		return $view->cmsBlock($blockName);
	}
}

/**	
 * catalog_url
 */
if (!function_exists('catalog_url')) {
	function catalog_url()
	{
		return store()->getMediaUrl() . 'catalog/product';
	}
}

/**	
 * get_helper
 */
if (!function_exists('get_helper')) {
	function get_helper($helper_class)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		return $objectManager->get($helper_class);
	}
}

/**	
 * object_manager
 */
if (!function_exists('class_manager')) {
	function class_manager()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		return $objectManager;
	}
}

/**	
 * product_advance: 遗留
 */
if (!function_exists('product_advance')) {
	function product_advance($sku)
	{
		$sku = trim($sku);
		$sql = "select available_stock,presell_status,presell_residue_num,presell_create_time,presell_end_time from fa_item WHERE sku='{$sku}' and is_del = 1;";
		$result = class_manager()->get(\Tools\Database\Glass::class)->query($sql);
		$qty = 0;
		$status = 0; //0售罄，1在售，2预售
		if (!empty($result->row)) {
			$row = $result->row;
			if ($row['available_stock'] <= 0) {
				$presell_create_time = strtotime($row['presell_create_time']);
				$presell_end_time = strtotime($row['presell_end_time']);
				if ($row['presell_status'] == 1 && $row['presell_residue_num'] > 0 && $presell_create_time < time() && $presell_end_time > time()) {
					$status = 2;
					$qty = $row['presell_residue_num'];
				}
			} else {
				$status = 1;
				$qty = $row['available_stock'];
			}
		}
		return array('status' => $status, 'true_qty' => $qty);
	}
}

/**	
 * browser info
 */
if (!function_exists('browse_info')) {
	function browse_info()
	{
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

if (!function_exists('ts_price')) {
	/**
	 * 价格转换
	 */
	function ts_price($price = 0, $from = 'USD', $to = '', $type = 1)
	{
		$currency_processor = class_manager()->get(\Magento\Directory\Model\CurrencyFactory::class)->create();

		$rate = $currency_processor->load($from)->getAnyRate($to);

		$converted_price = $price * $rate;
		return pf($converted_price);
	}
}

if (!function_exists('get_symbol')) {
	function get_symbol($code = null)
	{
		$symbol = '$';
		if ($code) {
			$currency = class_manager()->get(\Magento\Directory\Model\CurrencyFactory::class)->create()->load($code);
			$symbol = $currency->getCurrencySymbol();
		}

		return $symbol;
	}
}

if (!function_exists('current_store')) {
	/**
	 * 获取当前请求的商店
	 *
	 * @return array
	 */
	function current_store($store_code = 'default')
	{
		$store_instance = store()->getStoreManager()->getStores(true, true);
		if (isset($store_instance[$store_code]) && $store_instance[$store_code]) {
			$store_id = $store_instance[$store_code]->getId();
		} else {
			$store_id = 1;
		}

		$current_store = store()->getStoreManager()->getStore($store_id);
		$current_code = $current_store->getCurrentCurrency()->getCode();

		return [
			"store" => $current_store,
			"code" => $current_code
		];
	}
}

if (!function_exists('ass_unique')) {
	/** 二维数组去重 */
	function ass_unique($arr = [])
	{
		$result = array_map('unserialize', array_unique(array_map('serialize', $arr)));

		return $result;
	}
}

if (!function_exists('logger')) {
	function logger($info = null, $type = 'info', $filename = 'common.log')
	{
		$path = BP . '/var/log/' . $filename;
		$file_driver = class_manager()->get(\Magento\Framework\Filesystem\Driver\File::class);
		$file_system = class_manager()->get(\Magento\Framework\Filesystem::class);
		$directory_list = class_manager()->get(\Magento\Framework\App\Filesystem\DirectoryList::class);
		$log_dir = $file_system->getDirectoryWrite($directory_list::LOG);
		if (!$file_driver->isExists($path)) {
			$log_dir->writeFile($filename, "");
		}
		if ($info) {
			$writer = new \Zend\Log\Writer\Stream($path);
			$logger = new \Zend\Log\Logger();
			$logger->addWriter($writer);
			switch ($type) {
				case 'info':
					$logger->info($info);
					break;
				case 'error':
					$logger->err($info);
					break;

				default:
					$logger->info($info);
					break;
			}
		}
	}
}

if (!function_exists('env')) {
	function env($key = '')
	{
		$value = '';

		$config_loader = class_manager()->get(\Magento\Framework\App\DeploymentConfig::class);

		if ($key) {
			$value = $config_loader->get($key);
		}

		return $value;
	}
}

if (!function_exists('customer_session')) {
	function customer_session()
	{
		return class_manager()->get(\Magento\Customer\Model\SessionFactory::class)->create();
	}
}

if (!function_exists('wishlist_resource')) {
	function wishlist_resource()
	{
		return class_manager()->get(\Magento\Wishlist\Model\ResourceModel\Wishlist::class);
	}
}

if (!function_exists('wishlist_items')) {
	function wishlist_items()
	{
		$wishlist = class_manager()->get(\Magento\Wishlist\Model\WishlistFactory::class)->create();
		$data = [];
		$customer = customer_session();
		$request = class_manager()->get(\Magento\Framework\App\RequestInterface::class);

		if ($customer->isLoggedIn()) {
			$customer_id = $customer->getCustomer()->getId();
			$wishlist = $wishlist->loadByCustomerId($customer_id, true);
			$items = $wishlist->getItemCollection()->getData();

			$store_code = $request->getParam('store', 'default');
			$store = current_store($store_code);

			foreach ($items as $item) {
				$product = product_getter()->load($item['product_id']);
				$data[] = [
					'item_id' => $item['wishlist_item_id'],
					'id' => $item['wishlist_id'],
					'product' => [
						'id' => $product->getId(),
						'title' => spg($product, 'name'),
						'short_title' => spg($product, 'short_name'),
						'description' => spg($product, 'description'),
						'price_mix' => config_price($product, $store['code']),
						'short_description' => spg($product, 'short_description'),
						'type' => spg($product, 'type_id'),
						'review_mix' => review_mix($product),
						'question_mix' => question_mix($product),
						'sku' => spg($product, 'sku'),
						'weight' => pf(spg($product, 'weight')),
						'custom_lens_able' => (int) spg($product, 'addtional_type'),
						'has_options' => spg($product, 'has_options'),
						'meta_title' => spg($product, 'meta_title'),
						'meta_keyword' => spg($product, 'meta_keyword'),
						'meta_description' => spg($product, 'meta_description'),
						'image' => spg($product, 'image', 'a', true),
						'url_key' => spg($product, 'url_key'),
						'material' => spg($product, 'material', 'b'),
						'gender' => spg($product, 'gender', 'b'),
						'shape' => spg($product, 'shape', 'b'),
						'fit' => spg($product, 'fit', 'b'),
						'rim' => spg($product, 'rim', 'b'),
						'stripe_sub_interval' => spg($product, 'stripe_sub_interval'),
						'stripe_sub_enabled' => spg($product, 'stripe_sub_enabled'),
						'status' => spg($product, 'status'),
						'ae_buy_one_get_one' => (int) spg($product, 'ae_buy_one_get_one'),
						'visibility' => spg($product, 'visibility'),
						'quantity' => config_qty($product),
						'status' => spg($product, 'status'),
						'scale_images' => [
							spg($product, 'sd_180_1', 'a', true),
							spg($product, 'sd_180_2', 'a', true),
							spg($product, 'sd_180_3', 'a', true),
							spg($product, 'sd_180_4', 'a', true),
							spg($product, 'sd_180_5', 'a', true),
							spg($product, 'sd_180_6', 'a', true),
							spg($product, 'sd_180_7', 'a', true),
							spg($product, 'sd_180_8', 'a', true),
							spg($product, 'sd_180_9', 'a', true),
							spg($product, 'sd_180_10', 'a', true),
							spg($product, 'sd_180_11', 'a', true),
						],
						'gallery' => gallery($product)
					]
				];
			}
		}

		return $data;
	}
}

if (!function_exists('product_getter')) {
	function product_getter()
	{
		return class_manager()->get(\Magento\Catalog\Model\ProductFactory::class)->create();
	}
}
