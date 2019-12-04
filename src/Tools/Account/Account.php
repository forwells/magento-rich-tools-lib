<?php 

namespace Tools\Account;


class Account extends \Tools\Base
{
	public function getAccount()
	{
		$customerSession = $this->manager->get('Magento\Customer\Model\Session');
		return $customerSession;
	}
}