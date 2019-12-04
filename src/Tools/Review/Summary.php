<?php 

namespace Tools\Review;

class Summary extends \Tools\Base
{
	public function getAverageReviewVote()
	{
		$reviewFactory  = $this->manager->get('Magento\Review\Model\ReviewFactory');
		$rating = $this->manager->get('Magento\Review\Model\Rating');
		
		$product = current_product();

		$ratingSummary = $rating->getEntitySummary($product->getId());
		$ratingCollection = $reviewFactory->create()->getResourceCollection()
													->addStoreFilter(
														$this->getStoreManager()->getStore()->getId()
													)->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
													->addEntityFilter('product', current_product()->getId());
		$reviewCount = count($ratingCollection);
		$productRating = $ratingSummary->getSum() / $ratingSummary->getCount();
		$data = [
			'ratingPercent' => $productRating,
			'ratingValue' => $productRating/20
		];
		return $data;
	}
}