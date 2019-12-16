<?php 

namespace Tools\Store;

class Store extends \Tools\Base
{
	public function getStoreBaseUrl()
	{
<<<<<<< HEAD
		$store = $this->manager->get('Magento\Store\Model\StoreManagerInterface')->getStore(0)->getBaseUrl();
=======
		$store = $this->getMagentoStore()->getStore(0)->getBaseUrl();
>>>>>>> ignore .gitee
		return $store;
	}

	public function getLogo()
	{
		$logo = $this->manager->get('Magento\Theme\Block\Html\Header\Logo');
		$data = [
			'src' 	=> $logo->getLogoSrc(),
			'home' 	=> $logo->isHomePage(),
			'alt' 	=> $logo->getLogoAlt(),
			'width' => $logo->getLogoWidth(),
			'height' => $logo->getLogoHeight()
		];
		return $data;
	}

	public function getSearch()
	{
		return $this->getLayout()->createBlock('Magento\Framework\View\Element\Template')->setTemplate('Magento_Search::form.mini.phtml')->toHtml();
	}
<<<<<<< HEAD
=======

	public function getMagentoStore()
	{
		return $this->manager->get('Magento\Store\Model\StoreManagerInterface');
	}
>>>>>>> ignore .gitee
}