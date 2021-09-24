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
    function spg($product = null, $attr = null, $type = 'a')
    {
        if($product && $attr){
            if($type == 'a'){
                return $product->getData($attr);
            }elseif($type == 'b'){
                return [
                    'value' => explode(',', $product->getData($attr)),
                    'label' => is_array($product->getAttributeText($attr)) ? join(',', $product->getAttributeText($attr)) : $product->getAttributeText($attr);
                ];
            }else{
                return 'unknow function call';
            }
        }else{
            return '--';
        }
    }
}
