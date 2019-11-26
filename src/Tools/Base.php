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
}