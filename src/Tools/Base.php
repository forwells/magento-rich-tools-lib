<?php 

namespace Tools;

use Magento\Framework\App\ObjectManager;

class Base{

	protected $manager;
	public function __construct(
		
	)
	{
		$this->manager = ObjectManager::getInstance();
	}

	public function getLayout()
	{
		return $this->manager->get('Magento\Framework\View\Layout');
	}

	public function getRegistry()
	{
		return $this->manager->get('Magento\Framework\Registry');
	}

	public function getStoreManager()
	{
		return $this->manager->get('Magento\Store\Model\StoreManagerInterface');
	}

	public function getQuote()
	{
		return $this->manager->get('');
	}
}