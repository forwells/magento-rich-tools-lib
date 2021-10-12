<?php

/** 
 * 20210924 产品助手函数
 */

/**
 * 便捷获取产品属性
 * $product 对象
 * $attr 属性名称
 * $type 获取类型: a 取值 b 取聚合值(适用multi select 属性)
 */
if (!function_exists('spg')) {
    function spg($product = null, $attr = null, $type = 'a', $image = false)
    {
        if ($product && $attr) {
            if ($type == 'a') {
                return $image ? catalog_url() . $product->getData($attr) : $product->getData($attr);
            } elseif ($type == 'b') {
                return [
                    'value' => explode(',', $product->getData($attr)),
                    'label' => is_array($product->getAttributeText($attr)) ? join(',', $product->getAttributeText($attr)) : $product->getAttributeText($attr)
                ];
            } elseif ($type == 'c') {
                $attribute_id = class_manager()->get(\Magento\Eav\Model\ResourceModel\Entity\Attribute::class)->getIdByCode('catalog_product', $attr);
                return [
                    'option_id' => $attribute_id,
                    'value' => $product->getData($attr),
                    'label' => $product->getAttributeText($attr)
                ];
            } else {
                return 'unknow function call';
            }
        } else {
            return '--';
        }
    }
}

/**
 * 获取产品图片列表
 */
if (!function_exists('gallery')) {
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

/** 
 * 获取子类产品
 */
if (!function_exists('childs')) {
    function childs($product)
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
                        'type' => spg($child, 'type_id'),
                        'color' => spg($child, 'color', 'c'),
                        'power' => spg($child, 'power', 'c'),
                        'sku' => spg($child, 'sku'),
                        'weight' => spg($child, 'weight'),
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

/** 
 * 获取当前产品品论总信息
 */
if (!function_exists('review_mix')) {
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

/** 
 * 获取当前产品问答信息
 */
if (!function_exists('question_mix')) {
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

/** 
 * 获取配置产品的主产品价格
 */
if (!function_exists('config_price')) {
    function config_price($product = null)
    {
        // regular_price
        $regular_price = $product->getPriceInfo()->getPrice('regular_price');

        return [
            'min' => $regular_price->getMinRegularAmount()->getValue(),
            'max' => $regular_price->getMaxRegularAmount()->getValue(),
            'regular' => $regular_price->getValue()
        ];
    }
}
