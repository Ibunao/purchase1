<?php
/**
 * "meet_cat_big_small" 数据表模型类.
 *
 * @author        chenfenghua <843958575@qq.com>
 * @copyright     Copyright (c) 2007-2014 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       mall.model
 * @license       http://www.octmami.com/license
 * @version       v1.0.0
 */
class CatBigSmall extends B2cModel
{
    /**
     * 大、小分类列表
     *
     * @return mixed
     */
    public function items()
    {
        $purchase_id = Yii::app()->session['purchase_id'];

        $items = Yii::app()->cache->get('cat_big_small_list-'.$purchase_id);
        if (!$items) {
            //分类
            $sql = "SELECT * FROM {{cat_big_small}}";
            $trans = array();
            $list = $this->ModelQueryAll($sql);
            foreach($list as $k => $val){
                $trans[$val['big_id']."_".$val['small_id']] = $val;
            }
            //所有的不重复的 style_sn 的产品
            $small = $this->ModelQueryAll("SELECT cat_b,cat_s,style_sn FROM {{product}} WHERE disabled='false' AND purchase_id = {$purchase_id} AND is_down='0' GROUP BY style_sn");
            //统计大分类下小分类的数量
            foreach ($small as $v) {
                if (!isset($smallNum[$v['cat_b']][$v['cat_s']])) $smallNum[$v['cat_b']][$v['cat_s']] = 1;
                else $smallNum[$v['cat_b']][$v['cat_s']]++;
            }
            foreach ($trans as $v) {
                $items[$v['big_id']]['id'] = $v['big_id'];
                $items[$v['big_id']]['name'] = $v['big_cat_name'];
                $itemChild['id'] = $v['small_id'];
                $itemChild['name'] = $v['small_cat_name'];
                $itemChild['num'] = isset($smallNum[$v['big_id']][$v['small_id']])?$smallNum[$v['big_id']][$v['small_id']]:0;
                $items[$v['big_id']]['child'][] = $itemChild;
            }
            Yii::app()->cache->set('cat_big_small_list-'.$purchase_id,$items,$this->cart_expire);
        }
        return $items;
    }
} 