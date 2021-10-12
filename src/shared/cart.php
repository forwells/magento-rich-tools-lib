<?php

/**
 * 获取购物车产品
 */

if (!function_exists('cart_items')) {
    function cart_items()
    {
        $data = [];
        $cart = class_manager()->get(\Magento\Checkout\Model\Cart::class);
        $quote = $cart->getQuote();
        $items_visible = $quote->getAllvisibleItems();

        foreach ($items_visible as $item) {
            $data['list'][] = [
                'item_id' => $item->getId(),
                'pid' => $item->getProductId(),
                'sku' => $item->getSku(),
                'qty' => $item->getQty(),
                'price' => $item->getPrice()
            ];
        }

        $data['items_count'] = $quote->getItemsCount();
        $data['total_qty'] = $quote->getItemsQty();
        $data['subtotal'] = $quote->getSubtotal();
        $data['grand_total'] = $quote->getGrandTotal();
        $data['billing_addr'] = $quote->getBillingAddress()->getData();
        $data['shipping_addr'] = $quote->getShippingAddress()->getData();
    }
}
