<?php

/**
 * 获取购物车产品
 */

if (!function_exists('cart_items')) {
    function cart_items($quote_id = null)
    {
        $data = null;

        if ($quote_id) {
            $customer_session = class_manager()->get(\Magento\Customer\Model\SessionFactory::class)->create();
            $quote = class_manager()->get(\Magento\Quote\Model\QuoteFactory::class)->create()->load($quote_id);
            $cart_interface = class_manager()->get(\Magento\Quote\Api\Data\CartInterface::class);
            $coupon_model = class_manager()->get(\Magento\SalesRule\Model\CouponFactory::class)->create();
            $rule_model = class_manager()->get(\Magento\SalesRule\Model\RuleFactory::class)->create();

            if ($quote->getData() && (int) $quote->getItemsQty()) {
                trans_quote_currency($quote);

                $quote = class_manager()->get(\Magento\Quote\Model\QuoteFactory::class)->create()->load($quote_id);
                $items_visible = $quote->getAllvisibleItems();
                $shipping_address = $quote->getShippingAddress()->getData();
                $billing_address = $quote->getBillingAddress()->getData();
                $discount_amout = 0.00;
                $discount_rule_name = '';
                $data['list'] = [];
                // 用户登录状态处理
                if ($customer_session->isLoggedIn()) {
                    // 设置结账方式
                    $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER);
                    // 获取用户地址列表
                    $customer = $customer_session->getCustomer();
                    $customer_addresses = $customer->getAddresses();
                    $customer_default_shipping = $customer->getDefaultShipping();
                    $customer_default_billing = $customer->getDefaultBilling();
                    $data['customer_email'] = $customer->getEmail();
                    $data['customer_address_id'] = $shipping_address['customer_address_id'];
                    $data['customer_addresses'] = [];
                    foreach ($customer_addresses as $address_item) {
                        $address_item_data = $address_item->getData();
                        $data['customer_addresses'][] = [
                            'id' => $address_item_data['entity_id'],
                            'firstname' => $address_item_data['firstname'],
                            'lastname' => $address_item_data['lastname'],
                            'country_id' => $address_item_data['country_id'],
                            'country' => country_name($address_item_data['country_id']),
                            'region_id' => $address_item_data['region_id'],
                            'region' => $address_item_data['region'],
                            'city' => $address_item_data['city'],
                            'postcode' => $address_item_data['postcode'],
                            'street' => $address_item_data['street'],
                            'telephone' => $address_item_data['telephone'],
                            'is_default_shipping' => $customer_default_shipping == $address_item_data['entity_id'] ? 1 : 0,
                            'is_default_billing' => $customer_default_shipping == $address_item_data['entity_id'] ? 1 : 0,
                        ];
                    }

                    // 获取用户积分
                    $data['points_summary'] = get_points($customer->getId());
                } else {
                    $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST)
                        ->setCustomerId(null)
                        ->setCustomerEmail($quote->getBillingAddress()->getEmail())
                        ->setCustomerIsGuest(true)
                        ->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
                }

                if ($items_visible) {
                    foreach ($items_visible as $item) {
                        $product_image_helper = class_manager()->get(\Magento\Catalog\Helper\Image::class);
                        $itemOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                        if ($item->getProductType() == 'configurable') {
                            $child_product = class_manager()->get(\Magento\Catalog\Model\ProductFactory::class)->create()->loadByAttribute('sku', $itemOptions['simple_sku']);
                            $extra_options = $item->getOptionByCode('additional_options');
                            $additional_data = [];
                            if ($extra_options) {
                                $additional_data = trans_additional_option($extra_options->getData(), $quote->getQuoteCurrencyCode());
                            }
                            // dd($additional_data);
                            $data['list'][] = [
                                'item_id' => $item->getId(),
                                'pid' => $item->getProductId(),
                                'type' => $item->getProductType(),
                                'name' => $item->getName(),
                                'description' => $item->getDescription(),
                                'image' => $child_product->getBaseImageUrl() ?? $product_image_helper->getDefaultPlaceholderUrl('image'),
                                'simple_name' => $itemOptions['simple_name'],
                                'simple_sku' => $itemOptions['simple_sku'],
                                'shipment_type' => $itemOptions['shipment_type'],
                                'options' => $itemOptions['attributes_info'],
                                'sku' => $item->getSku(),
                                'qty' => $item->getQty(),
                                'applied_rules' => get_rules_applied($item->getAppliedRuleIds()),
                                'additional_options' => $additional_data ? json_decode($additional_data['value'], true) : '',
                                'weight' => $item->getWeight(),
                                'price' => pf($item->getPrice()),
                                'base_price' => pf($item->getBasePrice()),
                                'custom_price' => pf($item->getCustomPrice()),
                                'initial_price' => pf($item->getInitialPrice()),
                                'initial_currency' => pf($item->getInitialCurrency()),
                                'discount_percent' => $item->getDiscountPercent(),
                                'discount_amount' => pf($item->getDiscountAmount()),
                                'base_discount_amount' => pf($item->getBaseDiscountAmount()),
                                'row_total' => pf($item->getRowTotal()),
                                'base_row_total' => pf($item->getBaseRowTotal()),
                                'row_total_with_discount' => pf($item->getRowTotalWithDiscount()),
                                'row_weight' => pf($item->getRowWeight()),
                                'free_shipping' => $item->getFreeShipping(),
                                'reward_points_amount' => $item->getAwRewardPointsAmount(),
                                'reward_points' => $item->getAwRewardPoints()
                            ];
                        }
                        // 礼品卡
                        if ($item->getProductType() == 'aw_giftcard') {
                            // dd($itemOptions);
                            $data['list'][] = [
                                'item_id' => $item->getId(),
                                'pid' => $item->getProductId(),
                                'type' => $item->getProductType(),
                                'name' => $item->getName(),
                                'description' => $item->getDescription(),
                                'image' => store()->getMediaUrl() . $item->getProduct()->getImage(),
                                'options' => [],
                                'sku' => $item->getSku(),
                                'qty' => $item->getQty(),
                                'applied_rules' => get_rules_applied($item->getAppliedRuleIds()),
                                'additional_data' => $item->getAdditionalData(),
                                'weight' => $item->getWeight(),
                                'sender_name' => $itemOptions['aw_gc_sender_name'],
                                'recipient_name' => $itemOptions['aw_gc_recipient_name'],
                                'sender_email' => $itemOptions['aw_gc_sender_email'],
                                'recipient_email' => $itemOptions['aw_gc_recipient_email'],
                                'price' => pf($item->getPrice()),
                                'base_price' => pf($item->getBasePrice()),
                                'custom_price' => pf($item->getCustomPrice()),
                                'initial_price' => pf($item->getInitialPrice()),
                                'initial_currency' => pf($item->getInitialCurrency()),
                                'discount_percent' => $item->getDiscountPercent(),
                                'discount_amount' => pf($item->getDiscountAmount()),
                                'base_discount_amount' => pf($item->getBaseDiscountAmount()),
                                'row_total' => pf($item->getRowTotal()),
                                'base_row_total' => pf($item->getBaseRowTotal()),
                                'row_total_with_discount' => pf($item->getRowTotalWithDiscount()),
                                'row_weight' => pf($item->getRowWeight()),
                                'free_shipping' => $item->getFreeShipping(),
                                'reward_points_amount' => $item->getAwRewardPointsAmount(),
                                'reward_points' => $item->getAwRewardPoints()
                            ];
                        }
                    }
                }

                if ($quote->getCouponCode()) {
                    $rule_id = $coupon_model->loadByCode($quote->getCouponCode())->getRuleId();
                    $rule = $rule_model->load($rule_id);
                    $discount_rule_name = $rule->getName();
                }

                $data['customer_logged'] = $customer_session->isLoggedIn();

                $discount_amout = $shipping_address['discount_amount'];
                $data['discount'] = [
                    'discount_code' => $quote->getCouponCode(),
                    'applied_rules' => get_rules_applied($quote->getAppliedRuleIds()),
                    'amount' => pf(abs($discount_amout)),
                    'name' => $discount_rule_name
                ];
                $data['id'] = $quote_id;
                $data['base_currency_code'] = $quote->getBaseCurrencyCode();
                $data['base_currency_symbol'] = get_symbol($quote->getBaseCurrencyCode());
                $data['store_currency_code'] = $quote->getStoreCurrencyCode();
                $data['store_currency_symbol'] = get_symbol($quote->getStoreCurrencyCode());
                $data['quote_currency_code'] = $quote->getQuoteCurrencyCode();
                $data['quote_currency_symbol'] = get_symbol($quote->getQuoteCurrencyCode());
                $data['quote_points_used_count'] = $quote->getAwRewardPoints();
                $data['points_discount'] = pf(abs($quote->getAwRewardPointsAmount()));
                $data['giftcard_applied'] = get_giftcard_applied($quote_id);
                $data['giftcard_discount'] = pf($quote->getAwGiftcardAmount());
                $data['items_count'] = $quote->getItemsCount();
                $data['total_qty'] = (int)$quote->getItemsQty();
                $data['subtotal'] = pf($quote->getSubtotal());
                $data['subtotal_with_discount'] = pf($quote->getSubtotalWithDiscount());
                $data['grand_total'] = number_format($quote->getGrandTotal(), 2);
                $data['billing_addr'] = [
                    'aid' => $billing_address['address_id'],
                    'qid' => $billing_address['quote_id'],
                    'customer_id' => $billing_address['customer_id'],
                    'address_type' => $billing_address['address_type'],
                    'save_in_book' => $billing_address['save_in_address_book'],
                    'shipping_method' => $billing_address['save_in_address_book'],
                    'shipping_description' => $billing_address['shipping_description'],
                    'subtotal' => pf($billing_address['subtotal']),
                    'base_subtotal' => pf($billing_address['base_subtotal']),
                    'subtotal_with_discount' => pf($billing_address['subtotal_with_discount']),
                    'base_subtotal_with_discount' => pf($billing_address['base_subtotal_with_discount']),
                    'shipping_amount' => pf($billing_address['shipping_amount']),
                    'base_shipping_amount' => pf($billing_address['base_shipping_amount']),
                    'discount_amount' => pf($billing_address['discount_amount']),
                    'discount_description' => $billing_address['discount_description'],
                    'shipping_discount_amount' => pf($billing_address['shipping_discount_amount']),
                    'base_shipping_discount_amount' => pf($billing_address['base_shipping_discount_amount']),
                    'base_discount_amount' => pf($billing_address['base_discount_amount']),
                    'grand_total' => pf($billing_address['grand_total']),
                    'base_grand_total' => pf($billing_address['base_grand_total']),
                    'customer_notes' => $billing_address['customer_notes'],
                    'free_shipping' => $billing_address['free_shipping'],
                    'aw_giftcard_amount' => pf($billing_address['aw_giftcard_amount']),
                    'base_aw_giftcard_amount' => pf($billing_address['base_aw_giftcard_amount']),
                    'use_reward_points' => $billing_address['aw_use_reward_points'],
                    'reward_points_amount' => pf($billing_address['aw_reward_points_amount']),
                    'base_reward_points_amount' => pf($billing_address['base_aw_reward_points_amount']),
                    'reward_points' => $billing_address['aw_reward_points'],
                    'reward_points_description' => $billing_address['aw_reward_points_description'],
                    'reward_points_shipping_amount' => pf($billing_address['aw_reward_points_shipping_amount']),
                    'base_reward_points_shipping_amount' => pf($billing_address['base_aw_reward_points_shipping_amount']),
                    'reward_points_shipping' => $billing_address['aw_reward_points_shipping']
                ];
                // dd($shipping_address);
                $data['shipping_addr'] = [
                    'aid' => $shipping_address['address_id'],
                    'qid' => $shipping_address['quote_id'],
                    'customer_id' => $shipping_address['customer_id'],
                    'address_type' => $shipping_address['address_type'],
                    'save_in_book' => $shipping_address['save_in_address_book'],
                    'shipping_method' => $shipping_address['shipping_method'],
                    'shipping_description' => $shipping_address['shipping_description'],
                    'subtotal' => pf($shipping_address['subtotal']),
                    'base_subtotal' => pf($shipping_address['base_subtotal']),
                    'subtotal_with_discount' => pf($shipping_address['subtotal_with_discount']),
                    'base_subtotal_with_discount' => pf($shipping_address['base_subtotal_with_discount']),
                    'shipping_amount' => pf($shipping_address['shipping_amount']),
                    'base_shipping_amount' => pf($shipping_address['base_shipping_amount']),
                    'discount_amount' => pf($shipping_address['discount_amount']),
                    'discount_description' => $shipping_address['discount_description'],
                    'shipping_discount_amount' => pf($shipping_address['shipping_discount_amount']),
                    'base_shipping_discount_amount' => pf($shipping_address['base_shipping_discount_amount']),
                    'base_discount_amount' => pf($shipping_address['base_discount_amount']),
                    'grand_total' => pf($shipping_address['grand_total']),
                    'base_grand_total' => pf($shipping_address['base_grand_total']),
                    'customer_notes' => $shipping_address['customer_notes'],
                    'free_shipping' => $shipping_address['free_shipping'],
                    'aw_giftcard_amount' => pf($shipping_address['aw_giftcard_amount']),
                    'base_aw_giftcard_amount' => pf($shipping_address['base_aw_giftcard_amount']),
                    'use_reward_points' => $shipping_address['aw_use_reward_points'],
                    'reward_points_amount' => pf($shipping_address['aw_reward_points_amount']),
                    'base_reward_points_amount' => pf($shipping_address['base_aw_reward_points_amount']),
                    'reward_points' => $shipping_address['aw_reward_points'],
                    'reward_points_description' => $shipping_address['aw_reward_points_description'],
                    'reward_points_shipping_amount' => pf($shipping_address['aw_reward_points_shipping_amount']),
                    'base_reward_points_shipping_amount' => pf($shipping_address['base_aw_reward_points_shipping_amount']),
                    'reward_points_shipping' => $shipping_address['aw_reward_points_shipping']
                ];

                // 是否已经设置地址
                if (
                    $shipping_address['email']
                    && $shipping_address['firstname']
                    && $shipping_address['lastname']
                    && $shipping_address['street']
                    && $shipping_address['region']
                    && $shipping_address['country_id']
                    && $shipping_address['postcode']
                    && $shipping_address['telephone']
                ) {
                    $data['address_been_set'] = 1;
                } else {
                    $data['address_been_set'] = 0;
                }

                if ($shipping_address['email']) {
                    $data['shipping_addr']['address_details'] = [
                        'firstname' => $shipping_address['firstname'],
                        'lastname' => $shipping_address['lastname'],
                        'email' => $shipping_address['email'],
                        'postcode' => $shipping_address['postcode'],
                        'company' => $shipping_address['company'],
                        'street' => $shipping_address['street'],
                        'city' => $shipping_address['city'],
                        'region' => $shipping_address['region'],
                        'region_id' => $shipping_address['region_id'],
                        'telephone' => $shipping_address['telephone'],
                        'country_id' => $shipping_address['country_id'],
                        'shipping_description' => $shipping_address['shipping_description']
                    ];
                }

                $shipping_methods = carriers($quote, $quote->getQuoteCurrencyCode());

                $data['carriers'] = $shipping_methods;
            }
        }
        return $data;
    }
}

if (!function_exists('get_points')) {
    function get_points($customer_id)
    {
        $points_summary = class_manager()->get(\Aheadworks\RewardPoints\Model\PointsSummaryRepository::class);
        try {
            $points_summary = $points_summary->get($customer_id)->getData();

            $data = [
                'points_id' => $points_summary['summary_id'],
                'customer_id' => $customer_id,
                'points' => $points_summary['points'],
                'points_spend' => $points_summary['points_spend'],
                'points_earn' => $points_summary['points_earn'],
                'daily_review_points' => $points_summary['daily_review_points'],
                'daily_share_points' => $points_summary['daily_share_points'],
                'monthly_share_points' => $points_summary['monthly_share_points'],
                'is_awarded_newsletter' => $points_summary['is_awarded_for_newsletter_signup']
            ];
        } catch (\Exception $e) {
            $data = null;
        }


        return $data;
    }
}
if (!function_exists('get_reward_points')) {
    /** 获取购物车结账后可以获得的积分 暂时保留 */
    function get_reward_points($quote_id)
    {
    }
}
if (!function_exists('use_points')) {
    // 使用礼品卡操作
    function use_points($num, $quote_id, $customer_id)
    {
        $spend_num = (int) $num;

        try {
            if ($spend_num < 10) {
                return ['status' => false, 'msg' => 'Your current points are not enough to redeem the minimum amount.'];
            } else {

                $points_cart_service = class_manager()->get(\Aheadworks\RewardPoints\Model\Service\RewardPointsCartService::class);
                $checkout_session = class_manager()->get(\Magento\Checkout\Model\SessionFactory::class)->create();
                $checkout_session->setData('reward_points_use_number', $spend_num);
                $response = $points_cart_service->set($quote_id, false);

                if ($response['custom_attributes']['success']) {
                    return ['status' => true, 'msg' => $response['custom_attributes']['message']->getText()];
                } else {
                    return ['status' => false, 'msg' => $response['custom_attributes']['message']->getText()];
                }
            }
        } catch (\Exception $e) {
            return ['status' => false, 'msg' => $e->getMessage()];
        }
    }
}

if (!function_exists('cancel_points_use')) {
    // 使用礼品卡操作
    function cancel_points_use($quote_id)
    {
        try {
            $points_cart_service = class_manager()->get(\Aheadworks\RewardPoints\Model\Service\RewardPointsCartService::class);
            $response = $points_cart_service->remove($quote_id);
            if ($response) {
                return ['status' => $response, 'msg' => 'Cancel points apply successfully.'];
            } else {
                return ['status' => $response, 'msg' => 'Cancel points apply failed'];
            }
        } catch (\Exception $e) {
            return ['status' => false, 'msg' => $e->getMessage()];
        }
    }
}

if (!function_exists('get_giftcard_applied')) {
    // 使用礼品卡操作
    function get_giftcard_applied($card_id)
    {
        $data = [];
        $giftcard_manager = class_manager()->get(\Aheadworks\Giftcard\Api\GiftcardCartManagementInterface::class);
        $store = store_now();
        $applied_gcs = $giftcard_manager->get($card_id);
        foreach ($applied_gcs as $gc) {
            $data[] = [
                'code' => $gc->getData('giftcard_code'),
                'amount' => ts_price($gc->getData('giftcard_amount'), 'USD', $store['code'])
            ];
        }
        return $data;
    }
}


if (!function_exists('use_giftcard')) {
    // 使用礼品卡操作
    function use_giftcard($card_id, $code)
    {
        if ($code) {
            $giftcard_manager = class_manager()->get(\Aheadworks\Giftcard\Api\GiftcardCartManagementInterface::class);

            $apply = $giftcard_manager->set($card_id, $code);

            if ($apply) {
                return ['status' => true, 'msg' => 'Apply giftcard code ' . $code . ' success.'];
            } else {
                return ['status' => false, 'msg' => 'Apply giftcard ' . $code . ' failed.'];
            }
        }

        return ['status' => false, 'msg' => 'Giftcard code is not valid.'];
    }
}

if (!function_exists('cancel_giftcard')) {
    // 取消礼品卡操作
    function cancel_giftcard($card_id, $code)
    {
        if ($code) {
            $giftcard_manager = class_manager()->get(\Aheadworks\Giftcard\Api\GiftcardCartManagementInterface::class);

            $apply = $giftcard_manager->remove($card_id, $code);

            if ($apply) {
                return ['status' => true, 'msg' => 'Cancel giftcard code ' . $code . ' success.'];
            } else {
                return ['status' => false, 'msg' => 'Cancel giftcard code ' . $code . ' failed.'];
            }
        }

        return ['status' => false, 'msg' => 'Giftcard code is not valid.'];
    }
}

if (!function_exists('trans_quote_currency')) {
    function trans_quote_currency($quote)
    {
        try {
            $currency_processor = class_manager()->get(\Magento\Directory\Model\CurrencyFactory::class)->create();
            $request = class_manager()->get(\Magento\Framework\App\RequestInterface::class);
            $db = class_manager()->get(\Magento\Framework\App\ResourceConnection::class)->getConnection();
            $store = $request->getParam('store', 'default');

            $quote_id = $quote->getId();
            $store = current_store($store);
            $rate = $currency_processor->load('USD')->getAnyRate($store['code']);

            $item_cacl_subtotal = 0;
            foreach ($quote->getAllItems() as $item) {
                $item_id = $item->getId();
                $q_base_price = $item->getBasePrice();
                $q_base_row_total = $item->getBaseRowTotal();
                $q_base_discount_amount = $item->getBaseDiscountAmount();
                $q_base_tax_amount = $item->getBaseTaxAmount();
                $q_base_price_incl_tax = $item->getBasePriceInclTax();
                $q_base_row_total_incl_tax = $item->getBaseRowTotalInclTax();
                $q_base_aw_reward_points_amount = $item->getBaseAwRewardPointsAmount();
                $qty = $item->getQty();
                $q_price = pf($q_base_price * $rate);
                $q_row_total = pf($q_price * $qty);
                $q_discount_amount = pf($q_base_discount_amount * $rate);
                $q_tax_amount = pf($q_base_tax_amount * $rate);
                $q_price_incl_tax = pf($q_base_price_incl_tax * $rate);
                $q_row_total_incl_tax = pf($q_base_row_total_incl_tax * $rate);
                $q_aw_reward_points_amount = pf($q_base_aw_reward_points_amount * $rate);

                $db->query("update quote_item set price=$q_price where item_id=$item_id");
                $db->query("update quote_item set row_total=$q_row_total where item_id=$item_id");
                $db->query("update quote_item set discount_amount=$q_discount_amount where item_id=$item_id");
                $db->query("update quote_item set tax_amount=$q_tax_amount where item_id=$item_id");
                $db->query("update quote_item set price_incl_tax=$q_price_incl_tax where item_id=$item_id");
                $db->query("update quote_item set row_total_incl_tax=$q_row_total_incl_tax where item_id=$item_id");
                $db->query("update quote_item set aw_reward_points_amount=$q_aw_reward_points_amount where item_id=$item_id");

                $item_cacl_subtotal += $q_row_total;
            }

            // quote_address
            $shipping_address = $quote->getShippingAddress();
            $billing_address = $quote->getBillingAddress();
            $shipping_address_id = $shipping_address->getAddressId();
            $billing_address_id = $shipping_address->getAddressId();

            $qs_base_subtotal = $shipping_address->getBaseSubtotal();
            $qs_base_subtotal_with_discount = $shipping_address->getBaseSubtotalWithDiscount();
            $qs_base_tax_amount = $shipping_address->getBaseTaxAmount();
            $qs_base_shipping_amount = $shipping_address->getBaseShippingAmount();
            $qs_base_shipping_tax_amount = $shipping_address->getBaseShippingTaxAmount();
            $qs_base_discount_amount = $shipping_address->getBaseDiscountAmount();
            $qs_base_grand_total = $shipping_address->getBaseGrandTotal();
            $qs_base_shipping_discount_amount = $shipping_address->getBaseShippingDiscountAmount();
            $qs_base_subtotal_incl_tax = $shipping_address->getBaseSubtotalInclTax();
            $qs_base_shipping_incl_tax = $shipping_address->getBaseShippingInclTax();
            $qs_base_aw_reward_points_amount = $shipping_address->getBaseAwRewardPointsAmount();
            $qs_base_aw_giftcard_amount = $shipping_address->getBaseAwGiftcardAmount();

            $qs_subtotal = pf($qs_base_subtotal * $rate);
            $qs_subtotal_with_discount = pf($qs_base_subtotal_with_discount * $rate);
            $qs_tax_amount = pf($qs_base_tax_amount * $rate);
            $qs_shipping_amount = pf($qs_base_shipping_amount * $rate);
            $qs_shipping_tax_amount = pf($qs_base_shipping_tax_amount * $rate);
            $qs_discount_amount = pf($qs_base_discount_amount * $rate);
            $qs_grand_total = pf($qs_base_grand_total * $rate);
            $qs_shipping_discount_amount = pf($qs_base_shipping_discount_amount * $rate);
            $qs_subtotal_incl_tax = pf($qs_base_subtotal_incl_tax * $rate);
            $qs_shipping_incl_tax = pf($qs_base_shipping_incl_tax * $rate);
            $qs_aw_reward_points_amount = pf($qs_base_aw_reward_points_amount * $rate);
            $qs_aw_giftcard_amount = pf($qs_base_aw_giftcard_amount * $rate);

            // shipping
            $db->query("update quote_address set subtotal=$qs_subtotal where address_id=$shipping_address_id");
            $db->query("update quote_address set subtotal_with_discount=$qs_subtotal_with_discount where address_id=$shipping_address_id");
            $db->query("update quote_address set tax_amount=$qs_tax_amount where address_id=$shipping_address_id");
            $db->query("update quote_address set shipping_amount=$qs_shipping_amount where address_id=$shipping_address_id");
            $db->query("update quote_address set shipping_tax_amount=$qs_shipping_tax_amount where address_id=$shipping_address_id");
            $db->query("update quote_address set discount_amount=$qs_discount_amount where address_id=$shipping_address_id");
            $db->query("update quote_address set grand_total=$qs_grand_total where address_id=$shipping_address_id");
            $db->query("update quote_address set shipping_discount_amount=$qs_shipping_discount_amount where address_id=$shipping_address_id");
            $db->query("update quote_address set subtotal_incl_tax=$qs_subtotal_incl_tax where address_id=$shipping_address_id");
            $db->query("update quote_address set shipping_incl_tax=$qs_shipping_incl_tax where address_id=$shipping_address_id");
            $db->query("update quote_address set aw_reward_points_amount=$qs_aw_reward_points_amount where address_id=$shipping_address_id");
            $db->query("update quote_address set aw_giftcard_amount=$qs_aw_giftcard_amount where address_id=$shipping_address_id");

            // quote
            $qt_base_aw_reward_points_amount = $quote->getBaseAwRewardPointsAmount();
            $qt_base_aw_giftcard_amount = $quote->getBaseAwGiftcardAmount();
            $qt_base_subtotal = $quote->getBaseSubtotal();
            $qt_base_subtotal_with_discount = $quote->getBaseSubtotalWithDiscount();
            $qt_base_grand_total = $quote->getBaseGrandTotal();

            $qt_aw_reward_points_amount = pf($qt_base_aw_reward_points_amount * $rate);
            $qt_aw_giftcard_amount = pf($qt_base_aw_giftcard_amount * $rate);
            $qt_subtotal = pf($qt_base_subtotal * $rate);
            $qt_subtotal_with_discount = pf($qt_base_subtotal_with_discount * $rate);
            $qt_grand_total = pf($qt_base_grand_total * $rate);


            $db->query("update quote set aw_reward_points_amount=$qt_aw_reward_points_amount where entity_id=$quote_id");
            $db->query("update quote set aw_giftcard_amount=$qt_aw_giftcard_amount where entity_id=$quote_id");
            $db->query("update quote set quote_currency_code='" . $store['code'] . "' where entity_id=$quote_id");
            // $db->query("update quote set store_currency_code='" . $store['code'] . "' where entity_id=$quote_id");
            $db->query("update quote set base_to_quote_rate=" . $rate . " where entity_id=$quote_id");
            $db->query("update quote set subtotal=" . $qt_subtotal . " where entity_id=$quote_id");
            $db->query("update quote set subtotal_with_discount=" . $qt_subtotal_with_discount . " where entity_id=$quote_id");
            $db->query("update quote set grand_total=" . $qt_grand_total . " where entity_id=$quote_id");

            // store
            $db->query("update quote set store_id='" . $store['store']->getId() . "' where entity_id=$quote_id");

            // 购物车总额补正（因购物车运费+小计在多汇率情况下可能出现细微的大于或小于总金额的问题，这里统一采用购物车总额=已有的运费+小计-优惠 = 总金额 并将更正的总金额存在数据库中）
            // 条件： 如果shipping_amount + subtotal - discount_amount - giftcard_amount - rewardpoint_amout != grand_total
            // dump($qs_shipping_amount + $qs_subtotal);
            // dump($qs_shipping_amount + $qs_subtotal - abs($qs_discount_amount) - abs($qs_aw_giftcard_amount) - abs($qs_aw_reward_points_amount));
            // dump($qs_grand_total);
            // dd('.');
            if ($qs_shipping_amount + $qs_subtotal - abs($qs_discount_amount) - abs($qs_aw_giftcard_amount) - abs($qs_aw_reward_points_amount) != $qs_grand_total) {
                $grand_total_correct = $qs_shipping_amount + $qs_subtotal - abs($qs_discount_amount) - abs($qs_aw_giftcard_amount) - abs($qs_aw_reward_points_amount);

                $db->query("update quote_address set grand_total=$grand_total_correct where address_id=$shipping_address_id"); // quote address
                $db->query("update quote set grand_total=" . $grand_total_correct . " where entity_id=$quote_id"); // quote

                $db->query("insert into quote_correct_record (quote_id, correct_grand_total, origin_grand_total) values($quote_id, $qt_grand_total, $grand_total_correct) on duplicate key update quote_id=$quote_id, correct_grand_total=$qt_grand_total, origin_grand_total=$grand_total_correct");
            }


            // 购物车小计补正 

            $db->query("update quote_address set subtotal=$item_cacl_subtotal where address_id=$shipping_address_id"); // quote address
            $db->query("update quote set subtotal=" . $item_cacl_subtotal . " where entity_id=$quote_id"); // quote

            $grand_total_correct = $qs_shipping_amount + $item_cacl_subtotal - abs($qs_discount_amount) - abs($qs_aw_giftcard_amount) - abs($qs_aw_reward_points_amount);
            // dd($grand_total_correct);
            $db->query("update quote_address set grand_total=$grand_total_correct where address_id=$shipping_address_id"); // quote address
            $db->query("update quote set grand_total=" . $grand_total_correct . " where entity_id=$quote_id"); // quote
            $db->query("insert into quote_correct_record (quote_id, correct_grand_total, origin_grand_total) values($quote_id, $qt_grand_total, $grand_total_correct) on duplicate key update quote_id=$quote_id, correct_grand_total=$qt_grand_total, origin_grand_total=$grand_total_correct");
        } catch (\Exception $e) {
            dd($e->getMessage());
            $quote_id = $quote->getId();
            logger("购物车价格转化：quote_id=$quote_id" . $e->getMessage(), 'error', 'trans_quote_currency.log');
        }
    }
}

if (!function_exists('trans_additional_option')) {
    function trans_additional_option($data, $currency)
    {

        $value = json_decode($data['value'], true);
        $value['value'] = ts_price($value['value'], 'USD', $currency);
        $value['currency'] = $currency;
        $data['value'] = json_encode($value);

        return $data;
    }
}

if (!function_exists('get_rules_applied')) {
    /** 
     * 获取应用的优惠规则
     */
    function get_rules_applied($ids = null)
    {
        $data = [];
        if ($ids) {
            $ids = explode(',', $ids);
            $rule_repo = class_manager()->get(\Magento\SalesRule\Api\RuleRepositoryInterface::class);
            foreach ($ids as $id) {
                $rule = $rule_repo->getById($id);
                $data[] = [
                    'name' => $rule->getName(),
                    'description' => $rule->getDescription()
                ];
            }
        }


        return $data;
    }
}

if (!function_exists('cart_price_transfer')) {
    /** 
     * 购物车价格转化
     */
    function cart_price_transfer($price = 0.00)
    {
        return ts_price($price, 'USD', $to);
    }
}

if (!function_exists('carrriers')) {
    function carriers($quote = null, $currency = '')
    {
        $scope_config = class_manager()->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        // 预定义3种运输方式
        $all_methods = [
            'freeshipping' => [
                'title' => $scope_config->getValue('carriers/freeshipping/title'),
                'name' => $scope_config->getValue('carriers/freeshipping/name'),
                'amount' => 0,
                'code' => 'freeshipping_freeshipping'
            ],
            'flatrate' => [
                'title' => $scope_config->getValue('carriers/flatrate/title'),
                'name' => $scope_config->getValue('carriers/flatrate/name'),
                'amount' => ts_price($scope_config->getValue('carriers/flatrate/price'), 'USD', $currency),
                'code' => 'flatrate_flatrate'
            ],
            'mpcustomshipping' => [
                'title' => $scope_config->getValue('carriers/mpcustomshipping/title'),
                'name' => $scope_config->getValue('carriers/mpcustomshipping/name'),
                'amount' => ts_price($scope_config->getValue('carriers/mpcustomshipping/price'), 'USD', $currency),
                'code' => 'mpcustomshipping_mpcustomshipping'
            ]
        ];

        $freeshipping_limit = $scope_config->getValue('carriers/freeshipping/free_shipping_subtotal');
        // 是否可以免邮
        if ($quote->getBaseSubtotal() < $freeshipping_limit) {
            unset($all_methods['freeshipping']);
        } else {
            // unset($all_methods['flatrate']);
            // unset($all_methods['mpcustomshipping']);
        }

        return $all_methods;
    }
}

if (!function_exists('preset_carriers')) {
    function preset_carriers($quote = null, $currency = '')
    {
        $scope_config = class_manager()->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        // 预定义3种运输方式
        $all_methods = [
            'freeshipping_freeshipping' => [
                'title' => $scope_config->getValue('carriers/freeshipping/title'),
                'name' => $scope_config->getValue('carriers/freeshipping/name'),
                'amount' => 0,
                'code' => 'freeshipping_freeshipping'
            ],
            'flatrate_flatrate' => [
                'title' => $scope_config->getValue('carriers/flatrate/title'),
                'name' => $scope_config->getValue('carriers/flatrate/name'),
                'amount' => ts_price($scope_config->getValue('carriers/flatrate/price'), 'USD', $currency),
                'code' => 'flatrate_flatrate'
            ],
            'mpcustomshipping_mpcustomshipping' => [
                'title' => $scope_config->getValue('carriers/mpcustomshipping/title'),
                'name' => $scope_config->getValue('carriers/mpcustomshipping/name'),
                'amount' => ts_price($scope_config->getValue('carriers/mpcustomshipping/price'), 'USD', $currency),
                'code' => 'mpcustomshipping_mpcustomshipping'
            ]
        ];

        return $all_methods;
    }
}

if (!function_exists('country_name')) {
    function country_name($country_code)
    {
        $name = '';
        if ($country_code) {
            $country_loader = class_manager()->get(\Magento\Directory\Model\CountryFactory::class)->create();
            $country_loader->loadByCode($country_code);
            return $country_loader->getName();
        }
    }
}

if (!function_exists('region_info')) {
    function region_info($region_code, $country_code)
    {
        $id = null;
        if ($region_code && $country_code) {
            $region_loader = class_manager()->get(\Magento\Directory\Model\RegionFactory::class)->create();
            $region_loader->loadByCode($region_code, $country_code);
            $id = $region_loader->getId();
        }

        return $id;
    }
}

if (!function_exists('quote_set_default')) {
    function quote_set_default($quote_id)
    {
        $quote = class_manager()->get(\Magento\Quote\Model\QuoteFactory::class)->create()->load($quote_id);
        $store = current_store('default');
        $quote->setStore($store['store']);
        $quote->collectTotals()->save();
    }
}

if (!function_exists('store_now')) {
    /** 便捷返回当前商店 */
    function store_now()
    {
        $request = class_manager()->get(\Magento\Framework\App\RequestInterface::class);
        $store = $request->getParam('store', 'default');
        $store = current_store($store);

        return $store;
    }
}

/** --------------order */

if (!function_exists('giftcard_invoice')) {
    // 发票矫正: 金额，礼品卡
    function giftcard_invoice($quote_id)
    {
        $db = class_manager()->get(\Magento\Framework\App\ResourceConnection::class)->getConnection();
        $order_loader = class_manager()->get(\Magento\Sales\Model\OrderFactory::class)->create();

        $g_quote = $db->query('select * from aw_giftcard_quote where quote_id=' . $quote_id)->fetch();
        if ($g_quote) {
            $g_id = $g_quote['giftcard_id'];
            $quote = $db->query('select * from quote where entity_id=' . $quote_id)->fetch();
            $order = $order_loader->loadByIncrementId($quote['reserved_order_id']);
            $giftcard = class_manager()->get(\Aheadworks\Giftcard\Model\GiftcardFactory::class)->create()->load($g_id);
            $giftcard_data = $giftcard->getData();
            $order_id = $order->getId();

            $order_base_awgf_amount = $order->getData('base_aw_giftcard_amount');
            $order_awgf_amount = $order->getData('aw_giftcard_amount');
            $order_base_subtotal = $order->getData('base_subtotal');
            $order_subotal = $order->getData('subtotal');
            $order_base_grand_total = $order->getData('base_grand_total');
            $order_grand_total = $order->getData('grand_total');

            $new_balance = null;
            $code_state = 1;
            if ($order_base_awgf_amount == $giftcard['balance']) {
                $new_balance = 0;
            } else {
                $new_balance = $giftcard['balance'] - $order_base_awgf_amount;
            }
            $giftcard->setData('balance', $new_balance);
            $giftcard->save();

            $invoice = [];
            foreach ($order->getInvoiceCollection() as $invoice_item) {
                $iid = $invoice_item->getId();
                $db->query("insert into aw_giftcard_invoice (giftcard_id, invoice_id, order_id, base_giftcard_amount, giftcard_amount) values($g_id, $iid, $order_id, $order_base_awgf_amount, $order_awgf_amount)");
                $db->query("update sales_invoice set 
                    base_grand_total= $order_base_grand_total,
                    grand_total=$order_grand_total,
                    base_subtotal=$order_base_subtotal, subtotal=$order_subotal,
                    base_aw_giftcard_amount=$order_base_awgf_amount, aw_giftcard_amount=$order_awgf_amount where entity_id=$iid");
            }
        }
    }
}

if (!function_exists('giftcard_order')) {
    // 订单礼品卡矫正
    function giftcard_order($quote_id)
    {
        $db = class_manager()->get(\Magento\Framework\App\ResourceConnection::class)->getConnection();
        $order_loader = class_manager()->get(\Magento\Sales\Model\OrderFactory::class)->create();
        $g_quote = $db->query('select * from aw_giftcard_quote where quote_id=' . $quote_id)->fetch();
        if ($g_quote) {
            $g_id = $g_quote['giftcard_id'];

            $quote = $db->query('select * from quote where entity_id=' . $quote_id)->fetch();
            $order = $order_loader->loadByIncrementId($quote['reserved_order_id']);
            $order_id = $order->getId();
            $giftcard = class_manager()->get(\Aheadworks\Giftcard\Model\GiftcardFactory::class)->create()->load($g_id);

            $order_base_awgf_amount = $order->getData('base_aw_giftcard_amount');
            $order_awgf_amount = $order->getData('aw_giftcard_amount');

            // $giftcard->setData('order_id', $order_id)->save();
            $db->query("insert into aw_giftcard_order (giftcard_id, order_id, base_giftcard_amount, giftcard_amount) values($g_id, $order_id, $order_base_awgf_amount, $order_awgf_amount)");
        }
    }
}
if (!function_exists('points_earn_purchase')) {
    function points_earn_purchase($invoice_id, $customerId = null)
    {
        $earn_points = class_manager()->get(\Aheadworks\RewardPoints\Api\CustomerRewardPointsManagementInterface::class);

        $earn_points->addPointsForPurchases($invoice_id, $customerId);
    }
}
