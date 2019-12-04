<?php 

namespace Tools\Router;


class Router extends \Tools\Base
{
	public function getCurrentRoute()
	{
		$request = $this->manager->get(\Magento\Framework\App\Request\Http::class);
		$module = $request->getRouteName();
		$controller = $request->getControllerName();
		$action = $request->getActionName();
		return $module . $controller . $action;
	}
}