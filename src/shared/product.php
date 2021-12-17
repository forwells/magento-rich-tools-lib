<?php

/** 
 * 20210924 产品助手函数
 */


if (!function_exists('spg')) {
    /**
     * 便捷获取产品属性
     * $product 对象
     * $attr 属性名称
     * $type 获取类型: a 取值 b 取聚合值(适用multi select 属性)
     */
    function spg($product = null, $attr = null, $type = 'a', $image = false)
    {
        if ($product && $attr) {
            if ($type == 'a') {
                $product_image_helper = class_manager()->get(\Magento\Catalog\Helper\ImageFactory::class)->create();
                if ($image) {
                    if ($product->getData($attr) == 'no_selection' || $product->getData($attr) == null) {
                        return $product_image_helper->getDefaultPlaceholderUrl('image');
                    } else {
                        return catalog_url() . $product->getData($attr);
                    }
                } else {
                    return $product->getData($attr);
                }
            } elseif ($type == 'b') {
                // @return 属性值 多选
                return [
                    'value' => explode(',', $product->getData($attr)),
                    'label' => is_array($product->getAttributeText($attr)) ? join(',', $product->getAttributeText($attr)) : $product->getAttributeText($attr)
                ];
            } elseif ($type == 'c') {
                // @return 聚合产品属性值 color/power
                $attribute_id = class_manager()->get(\Magento\Eav\Model\ResourceModel\Entity\Attribute::class)->getIdByCode('catalog_product', $attr);
                $swatch_helper = class_manager()->get(\Magento\Swatches\Helper\Media::class);
                $swatch_collection = class_manager()->get(\Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory::class)->create();
                if ($attr == 'color') {
                    $color_id = $product->getData($attr);
                    $swatch = $swatch_collection->addFieldToFilter('option_id', $color_id)->getFirstItem();
                    switch ($swatch->getType()) {
                        case 1:
                            $sv = $swatch->getValue();
                            break;
                        case 2:
                            $sv = $swatch_helper->getSwatchAttributeImage('swatch_image', $swatch->getValue());
                            break;
                        default:
                            $sv = '';
                            break;
                    }
                    return [
                        'option_id' => $attribute_id,
                        'value_id' => $color_id,
                        'type' => $swatch->getType(),
                        'label' => $product->getAttributeText($attr),
                        'value' => $sv
                    ];
                } elseif ($attr == 'power') {
                    $power_id = $product->getData($attr);
                    $swatch = $swatch_collection->addFieldToFilter('option_id', $power_id)->getFirstItem();
                    return [
                        'option_id' => $attribute_id,
                        'value_id' => $power_id,
                        'type' => $swatch->getType(),
                        'label' => $product->getAttributeText($attr),
                        'value' => $swatch->getValue()
                    ];
                }
            } elseif ($type == 'd') {
                // @return 聚合产品属性值 color/power 以及附加图片
                $attribute_id = class_manager()->get(\Magento\Eav\Model\ResourceModel\Entity\Attribute::class)->getIdByCode('catalog_product', $attr);
                $swatch_helper = class_manager()->get(\Magento\Swatches\Helper\Media::class);
                $swatch_collection = class_manager()->get(\Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory::class)->create();
                if ($attr == 'color') {
                    $color_id = $product->getData($attr);
                    $swatch = $swatch_collection->addFieldToFilter('option_id', $color_id)->getFirstItem();
                    switch ($swatch->getType()) {
                        case 1:
                            $sv = $swatch->getValue();
                            break;
                        case 2:
                            $sv = $swatch_helper->getSwatchAttributeImage('swatch_image', $swatch->getValue());
                            break;
                        default:
                            $sv = '';
                            break;
                    }
                    return [
                        'product_image' => $product->getImage(),
                        'option_id' => $attribute_id,
                        'value_id' => $color_id,
                        'type' => $swatch->getType(),
                        'label' => $product->getAttributeText($attr),
                        'value' => $sv
                    ];
                }
            } else {
                return 'unknow function call';
            }
        } else {
            return '--';
        }
    }
}

if (!function_exists('gallery')) {
    /**
     * 获取产品图片列表
     */
    function gallery($product)
    {
        $gallery = [];
        if ($product) {
            $connection = class_manager()->get(\Magento\Framework\App\ResourceConnection::class)->getConnection();
            $items = $product->getMediaGalleryImages()->toArray();
            $items = $items['items'];
            foreach ($items as $item) {
                $value_id = null;
                $value_id = $item['value_id'];
                $tblSalesOrder3 = $connection->getTableName('catalog_product_entity_media_gallery');
                $is_vm = $connection->fetchAll('SELECT value FROM `' . $tblSalesOrder3 . '` WHERE vm = 1 and value_id =' . $value_id);
                // dump($is_vm);
                if (empty($is_vm)) {
                    $gallery['images'][] = [
                        'src' => $item['url'],
                        'position' => $item['position'],
                        'type' => $item['media_type'],
                        'label' => $item['label'],
                        'label_default' => $item['label_default'],
                        'disabled' => $item['disabled'],
                        'video_type' => $item['video_provider'],
                        'video_url' => $item['video_url'],
                        'video_title' => $item['video_title'],
                        'video_description' => $item['video_description'],
                        'video_metadata' => $item['video_metadata'],
                        'video_type_default' => $item['video_provider_default'],
                        'video_url_default' => $item['video_url_default'],
                        'video_title_default' => $item['video_title_default'],
                        'video_description_default' => $item['video_description_default'],
                        'video_metadata_default' => $item['video_metadata_default'],
                    ];
                } else {
                    $gallery['vm_images'][] = [
                        'src' => $item['url'],
                    ];
                }
            }

            return $gallery;
        }

        return $gallery;
    }
}


if (!function_exists('childs')) {
    /** 
     * 获取子类产品
     */
    function childs($product, $to = '')
    {
        $childs = [];
        if ($product) {
            $childs_flow = $product->getTypeInstance()->getUsedProducts($product);
            $has_childs = count($childs_flow);
            if ($has_childs) {
                foreach ($childs_flow as $child) {
                    $childs[] = [
                        'title' => spg($child, 'name'),
                        'short_title' => spg($child, 'short_name'),
                        'description' => spg($child, 'description'),
                        'short_description' => spg($child, 'short_description'),
                        'price_mix' => price($child, $to),
                        'type' => spg($child, 'type_id'),
                        'color' => spg($child, 'color', 'c'),
                        'power' => spg($child, 'power', 'c'),
                        'sku' => spg($child, 'sku'),
                        'weight' => pf(spg($child, 'weight')),
                        'has_options' => spg($child, 'has_options'),
                        'meta_title' => spg($child, 'meta_title'),
                        'meta_keyword' => spg($child, 'meta_keyword'),
                        'meta_description' => spg($child, 'meta_description'),
                        'image' => spg($child, 'image', 'a', true),
                        'size_picture' => spg($child, 'size_picture', 'a', true),
                        'size_params' => [
                            'lens_width' => spg($child, 'lens_width'),
                            'lens_height' => spg($child, 'lens_height'),
                            'lens_frame_width' => spg($child, 'lens_frame_width'),
                            'lens_temple_length' => spg($child, 'lens_temple_length'),
                            'lens_bridge' => spg($child, 'lens_bridge')
                        ],
                        'url_key' => spg($child, 'url_key'),
                        'material' => spg($child, 'material', 'b'),
                        'gender' => spg($child, 'gender', 'b'),
                        'shape' => spg($child, 'shape', 'b'),
                        'fit' => spg($child, 'fit', 'b'),
                        'rim' => spg($child, 'rim', 'b'),
                        'stripe_sub_interval' => spg($child, 'stripe_sub_interval'),
                        'stripe_sub_enabled' => spg($child, 'stripe_sub_enabled'),
                        'status' => spg($child, 'status'),
                        'ae_buy_one_get_one' => spg($child, 'ae_buy_one_get_one'),
                        'visibility' => spg($child, 'visibility'),
                        'quantity' => simple_qty($child),
                        'status' => spg($child, 'status'),
                        'scale_images' => [
                            spg($child, 'sd_180_1', 'a', true),
                            spg($child, 'sd_180_2', 'a', true),
                            spg($child, 'sd_180_3', 'a', true),
                            spg($child, 'sd_180_4', 'a', true),
                            spg($child, 'sd_180_5', 'a', true),
                            spg($child, 'sd_180_6', 'a', true),
                            spg($child, 'sd_180_7', 'a', true),
                            spg($child, 'sd_180_8', 'a', true),
                            spg($child, 'sd_180_9', 'a', true),
                            spg($child, 'sd_180_10', 'a', true),
                            spg($child, 'sd_180_11', 'a', true),
                        ],
                        'gallery' => gallery($child)
                    ];
                }
            }
        }

        return $childs;
    }
}

if (!function_exists('review_mix')) {
    /** 
     * 获取当前产品品论总信息
     */
    function review_mix($product = null)
    {
        $reviewFactory  = class_manager()->get('Magento\Review\Model\ReviewFactory');
        $rating = class_manager()->get('Magento\Review\Model\Rating');
        $pid = $product->getId();
        $ratingSummary = $rating->getEntitySummary($pid);
        $ratingCollection = $reviewFactory->create()->getResourceCollection()
            ->addStoreFilter(
                store()->getStoreManager()->getStore()->getId()
            )->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->addEntityFilter('product', $pid);
        $reviewCount = count($ratingCollection);
        $productRating = $ratingSummary->getCount() ? $ratingSummary->getSum() / $ratingSummary->getCount() : 0;
        $data = [
            'count' => $ratingSummary->getCount() ? $ratingSummary->getCount() : 0,
            'ratingPercent' => $productRating,
            'ratingValue' => $productRating / 20
        ];
        return $data;
    }
}

if (!function_exists('question_mix')) {
    /** 
     * 获取当前产品问答信息
     */
    function question_mix($product = null)
    {
        $questionLoader = class_manager()->get(\Sinoart\Question\Model\QuestionFactory::class)->create();
        $pid = $product->getId();

        $question = $questionLoader->getCollection()->addFieldToFilter('product_id', ['eq' => $pid])->addFieldToFilter('status', ['eq' => 1]);

        return [
            'total' => count($question)
        ];
    }
}

if (!function_exists('config_qty')) {
    /**
     * 获取配置产品库存总数
     */
    function config_qty($product = null)
    {
        $qty = 0;
        if ($product && $product->getTypeID() == 'configurable') {
            $stock_state = class_manager()->get(\Magento\CatalogInventory\Api\StockStateInterface::class);
            $product_type_instance = $product->getTypeInstance();
            $used_products = $product_type_instance->getUsedProducts($product);

            foreach ($used_products as $child) {
                $qty += $stock_state->getStockQty($child->getId(), $child->getStore()->getWebsiteId());
            }
        }

        return $qty;
    }
}

if (!function_exists('simple_qty')) {
    /**
     * 获取简单产品库存总数
     */
    function simple_qty($product = null)
    {
        $qty = 0;
        if ($product && $product->getTypeID() == 'simple') {
            $stock_state = class_manager()->get(\Magento\CatalogInventory\Api\StockStateInterface::class);
            $qty = $stock_state->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
        }

        return $qty;
    }
}

if (!function_exists('config_price')) {
    /** 
     * 获取配置产品的主产品价格
     */
    function config_price($product = null, $to = '')
    {
        // regular_price

        $regular_price_model = $product->getPriceInfo()->getPrice('regular_price');
        $final_price = $product->getPriceInfo()->getPrice('final_price')->getValue();
        $regular_price = $regular_price_model->getValue();
        if ($product->getTypeId() != 'configurable') {
            return [
                'price' => ts_price($regular_price, 'USD', $to),
                'final' => ts_price($final_price, 'USD', $to),
                'percent' => $final_price / $regular_price
            ];
        } else {
            return [
                'min' => ts_price($regular_price_model->getMinRegularAmount()->getValue(), 'USD', $to),
                'max' => ts_price($regular_price_model->getMaxRegularAmount()->getValue(), 'USD', $to),
                'price' => ts_price($regular_price, 'USD', $to),
                'final' => ts_price($final_price, 'USD', $to),
                'percent' => $final_price / $regular_price
            ];
        }
    }
}


if (!function_exists('price')) {
    /** 
     * 获取指定产品价格信息（简单产品）
     */
    function price($product = null, $to)
    {
        $data = [];
        if ($product) {
            $price = $product->getPrice();
            $final_price = $product->getFinalPrice();
            $data = [
                'price' => ts_price($price, 'USD', $to),
                'final_price' => ts_price($final_price, 'USD', $to),
                'percent' => $final_price / $price
            ];
        }

        return $data;
    }
}

if (!function_exists('pf')) {
    /** 
     * 价格数字保留格式化
     * @return number
     */
    function pf($number = 0.00)
    {
        if ($number) {
            $number = number_format($number, 2);
        }
        return $number;
    }
}
