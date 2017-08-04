<?php

/**
 * 商品模型类.
 *
 * @author        chenfenghua <843958575@qq.com>
 * @copyright     Copyright (c) 2007-2014 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       mall.model
 * @license       http://www.octmami.com/license
 * @version       v1.0.0
 */
class Product extends B2cModel
{
    public $purchase_id;

    /**
     * id转名称
     *
     * @param $table
     * @param $select
     * @param $where_id
     * @return array
     */
    public function tableValue($table, $select, $where_id)
    {
        $items = Yii::app()->cache->get($table . '-id-list');
        if (!$items) {
            $sql = "SELECT {$where_id},{$select} FROM {{{$table}}} ORDER BY p_order ASC";
            $list = $this->ModelQueryAll($sql);
            $items = array();
            foreach ($list as $v) {
                $items[$v[$where_id]] = $v[$select];
            }
            Yii::app()->cache->set($table . '-id-list', $items, 3600 * 24);
        }
        return $items;
    }

    /**
     * 尺码
     *
     * @param $sizes
     * @param $size_list
     * @return string
     */
    public function size($sizes, $size_list)
    {
        $sizeArr = implode(',', $sizes);
        $str = '';
        $str .= isset($size_list[$sizeArr[0]]) ? $size_list[$sizeArr[0]] : '';
        $str .= isset($size_list[$sizeArr[1]]) ? $size_list[$sizeArr[1]] : '';
        $str .= isset($size_list[$sizeArr[2]]) ? $size_list[$sizeArr[2]] : '';
        if (isset($size_list[$sizeArr[3]])) $str .= '...';
        return $str;
    }

    /**
     * 商品列表
     *
     * @param $conditionArr
     * @param int $start
     * @param int $pagesize
     * @return array
     */
    public function items($conditionArr, $start = 0, $pagesize = 8)
    {
        $size_list = $this->tableValue('size', 'size_name', 'size_id');

        $condtions = implode(' AND ', $conditionArr);

        $sql = "SELECT serial_num FROM {{product}} WHERE $condtions GROUP BY serial_num ORDER BY product_id ASC LIMIT {$start},{$pagesize}";
        $list_serial_list = $this->ModelQueryAll($sql);
        $list_serial_numArr = array();
        foreach ($list_serial_list as $v) {
            $list_serial_numArr[] = $v['serial_num'];
        }

        if (!$list_serial_numArr) return array();
        $list_serial_nums = implode(',', $list_serial_numArr);
        $list = $this->ModelQueryAll("SELECT * FROM {{product}} WHERE serial_num IN ({$list_serial_nums})");
        //var_dump($list);die;
        foreach ($list as $v) {
            if (isset($items[$v['serial_num']]) && $items[$v['serial_num']]) {
                $items[$v['serial_num']]['size_name'] .= ',' . $size_list[$v['size_id']];
            } else {
                $items[$v['serial_num']] = $v;
                $items[$v['serial_num']]['size_name'] = $size_list[$v['size_id']];
            }
        }
        return $items;
    }

    /**
     * 商品搜索
     * @param $conArr  搜索条件
     * @param $serial   搜索型号
     * @param $params   小条件
     * @param int $price  价格排序
     * @param int $page  页码
     * @param int $pagesize
     * @return array
     */
    public function newitems($conArr, $serial, $params, $price = 1, $page = 1, $pagesize = 8){
        //根据输入框的长度来判断是否是 model_sn型号 还是 serial_num 流水号查询 出去重的 style_sn 款号
        if(strlen($serial) >4){
            //获取查询的去重的款号 的型号  
            $row = $this->ModelQueryAll("SELECT DISTINCT(style_sn) FROM {{product}} WHERE model_sn LIKE '" . $serial . "%'  AND disabled = 'false' and is_down='0' AND purchase_id = {$params['purchase_id']}");
            if (!$row) return array();
            //根据查询出的款号 和 搜索条件 获取商品的详细信息
            $items = $this->listModelSn($row, $params, $conArr);
        }else{
            if (!empty($serial)) {
                //流水号
                $sql = "SELECT DISTINCT(style_sn) FROM {{product}} WHERE serial_num  ='{$serial}' AND disabled = 'false' AND is_down='0' AND purchase_id = {$params['purchase_id']} ORDER BY serial_num ASC";
                $row = $this->ModelQueryAll($sql);
                if (!$row) return array();
                $items = $this->listSerials($row, $params, $conArr);
            }else{
                $row = "";
                $items = $this->listSerial($row, $params, $conArr);
            }
        }
        //人气排序 1:降序  2:升序
        $hits_sort = array();
        if ($params['hits'] && !empty($items)) {
            //根据下单数量来定义人气
            $order_item_list = $this->ModelQueryAll("SELECT style_sn,SUM(nums) AS num FROM {{order_items}} WHERE disabled = 'false' GROUP BY style_sn");
            foreach ($order_item_list as $v) {
                $order_item_list[$v['style_sn']] = $v['num'];
            }

            foreach ($items as $k => $v) {
                $num = isset($order_item_list[$v['style_sn']]) ? $order_item_list[$v['style_sn']] : 0;
                $items[$k]['hit_num'] = $num;
                $hits_sort[$k] = $num;
            }

            $sort2 = $params['hits'] == 2 ? SORT_ASC : SORT_DESC;
            array_multisort($hits_sort, $sort2, $items);
        }

        //价格升降排序 1:升序  2:降序
        $price_sort = array();
        if ($price && !empty($items)) {
            foreach ($items as $k => $v) {
                $price_sort[$k] = $v['cost_price'];
            }
            $sort1 = $price == 2 ? SORT_ASC : SORT_DESC;
            array_multisort($price_sort, $sort1, $items);
        }
        //分页超出
        if (($page - 1) * $pagesize > count($items)) return array();
        //从数组中取出指定分页需要的数据
        return array_slice($items, ($page - 1) * $pagesize, $pagesize);
    }

    /**
     * 按流水号查询列表
     *
     * @param string $model_sn
     * @param $params
     * @param $conArr
     * @return mixed
     */
    public function listSerials($model_sn = '', $params, $conArr)
    {
        //所有的size
        $size_list = $this->tableValue('size', 'size_name', 'size_id');
        $list = $this->listcache();
        $order_row = $this->getThisOrderedInfo($params['purchase_id'], $params['customer_id']);
        $item_list = isset($order_row) ? $order_row : array();
        $items_model_sn = array();
        foreach ($item_list as $v) {
            $items_model_sn[] = $v['style_sn'];
        }
        $items = array();
        foreach ($model_sn as $s) {
            foreach ($list as $v) {
                //流水筛选
                if ($s['style_sn'] && ($v['style_sn'] != $s['style_sn'])) continue;


                //已定/未定
                if ($params['or'] == 1 && !in_array($v['style_sn'], $items_model_sn)) continue;
                if ($params['or'] == 2 && in_array($v['style_sn'], $items_model_sn)) continue;

                $item = $v;
                //筛选条件
                $item['search_id'] = array(
                    's_id_' . $v['cat_b'],
                    'c_id_' . $v['cat_s'],
                    'sd_' . $v['season_id'],
                    'wv_' . $v['wave_id'],
                    'lv_' . $v['level_id'],
                    'plv_' . $v['price_level_id'],
                );

                //数组筛选
                if (array_intersect($conArr, $item['search_id']) != $conArr) continue;

                //商品是否已定
                $item['is_order'] = isset($items_model_sn) && in_array($v['style_sn'], $items_model_sn) ? 1 : 2;
                //尺码
                if (isset($items[$v['style_sn']])) {
                    $item['size'] = $items[$v['style_sn']]['size'];
                    $item['size_item'] = $items[$v['style_sn']]['size_item'];
                }
                if (!isset($item['size']) || !in_array($size_list[$v['size_id']], $item['size'])) {
                    $item['size'][$v['size_id']] = $size_list[$v['size_id']];
                }
//            if($v['disabled']=='false'){
//                $sql="SELECT COUNT(nums) FROM `meet_order_items`  WHERE product_id='".$v['product_id']."'";
//                $jss=$this->ModelQueryAll($sql);
//                if($jss[0]['COUNT(nums)']>0){
//                    $row['is_down']=1;
//                }
//            }
                $row['product_id'] = $v['product_id'];
                $row['product_sn'] = $v['product_sn'];
                $row['size_name'] = $size_list[$v['size_id']];
                $item['size_item'][] = $row;
                $items[$v['style_sn']] = $item;
            }
        }
        return $items;
    }

    /**
     * 流水号查询列表
     *
     * @param string $model_sn
     * @param $params
     * @param $conArr
     * @return mixed
     */
    public function listSerial($model_sn = '', $params, $conArr)
    {
        $size_list = $this->tableValue('size', 'size_name', 'size_id');
        $list = $this->listcache();
        $order_row = $this->getThisOrderedInfo($params['purchase_id'], $params['customer_id']);
        $item_list = isset($order_row) ? $order_row : array();
        $items_model_sn = array();
        foreach ($item_list as $v) {
            $items_model_sn[] = $v['style_sn'];
        }
        $items = array();
        foreach ($list as $v) {

            //流水筛选
            if ($model_sn && ($v['style_sn'] != $model_sn)) continue;

            //已定/未定
            if ($params['or'] == 1 && !in_array($v['style_sn'], $items_model_sn)) continue;
            if ($params['or'] == 2 && in_array($v['style_sn'], $items_model_sn)) continue;

            $item = $v;
            //筛选条件
            $item['search_id'] = array(
                's_id_' . $v['cat_b'],
                'c_id_' . $v['cat_s'],
                'sd_' . $v['season_id'],
                'wv_' . $v['wave_id'],
                'lv_' . $v['level_id'],
                'plv_' . $v['price_level_id'],
            );

            //数组筛选
            if (array_intersect($conArr, $item['search_id']) != $conArr) continue;

            //商品是否已定
            $item['is_order'] = isset($items_model_sn) && in_array($v['style_sn'], $items_model_sn) ? 1 : 2;
            //尺码
            if (isset($items[$v['style_sn']])) {
                $item['size'] = $items[$v['style_sn']]['size'];
                $item['size_item'] = $items[$v['style_sn']]['size_item'];
            }
            if (!isset($item['size']) || !in_array($size_list[$v['size_id']], $item['size'])) {
                $item['size'][$v['size_id']] = $size_list[$v['size_id']];
            }
//            if($v['disabled']=='false'){
//                $sql="SELECT COUNT(nums) FROM `meet_order_items`  WHERE product_id='".$v['product_id']."'";
//                $jss=$this->ModelQueryAll($sql);
//                if($jss[0]['COUNT(nums)']>0){
//                    $row['is_down']=1;
//                }
//            }
            $row['product_id'] = $v['product_id'];
            $row['product_sn'] = $v['product_sn'];
            $row['size_name'] = $size_list[$v['size_id']];
            $item['size_item'][] = $row;
            $items[$v['style_sn']] = $item;
        }
        return $items;
    }
    //指定型号下的商品搜索
    /**
     * 按流水号列表
     * @param array $model_sn   去重的 搜索指定型号的 款号
     * @param $params
     * @param $conArr
     * @return array]
     */
    public function listModelSn($model_sn = array(), $params, $conArr)
    {
        //尺码表获取所有的尺码  
        $size_list = $this->tableValue('size', 'size_name', 'size_id');
        //获取所有的商品
        $list = $this->listcache();

        //获取客户订单详细信息
        $order_row = $this->getThisOrderedInfo($params['purchase_id'], $params['customer_id']);

        $item_list = isset($order_row) ? $order_row : array();
        $items_model_sn = array();
        //记录客户下单的款号style_sn
        foreach ($item_list as $v) {
            $items_model_sn[] = $v['style_sn'];
        }
        $items = array();
        // style_sn款号的处理
        foreach ($model_sn as $s) {
            foreach ($list as $v) {
                //款号筛选
                if ($s['style_sn'] && ($v['style_sn'] != $s['style_sn'])) continue;

                //搜索已订条件的产品
                if ($params['or'] == 1 && !in_array($v['style_sn'], $items_model_sn)) continue;
                //搜索未订购条件的产品
                if ($params['or'] == 2 && in_array($v['style_sn'], $items_model_sn)) continue;

                $item = $v;
                //筛选条件
                $item['search_id'] = array(
                    's_id_' . $v['cat_b'],
                    'c_id_' . $v['cat_s'],
                    'sd_' . $v['season_id'],
                    'wv_' . $v['wave_id'],
                    'lv_' . $v['level_id'],
                    'plv_' . $v['price_level_id'],
                );

                //根据筛选条件进行筛选  
                //根据该条记录拼接数来的筛选条件和用户传过来的筛选条件进行交集，看是否等于用户的筛选条件，如果等于则符合用户筛选
                if (array_intersect($conArr, $item['search_id']) != $conArr) continue;

                //该商品是否已订 
                $item['is_order'] = isset($items_model_sn) && in_array($v['style_sn'], $items_model_sn) ? 1 : 2;
                //尺码
                if (isset($items[$v['style_sn']])) {
                    $item['size'] = $items[$v['style_sn']]['size'];
                    $item['size_item'] = $items[$v['style_sn']]['size_item'];
                }
                if (!isset($item['size']) || !in_array($size_list[$v['size_id']], $item['size'])) {
                    $item['size'][$v['size_id']] = $size_list[$v['size_id']];
                }
//            if($v['disabled']=='false'){
//                $sql="SELECT COUNT(nums) FROM `meet_order_items`  WHERE product_id='".$v['product_id']."'";
//                $jss=$this->ModelQueryAll($sql);
//                if($jss[0]['COUNT(nums)']>0){
//                    $row['is_down']=1;
//                }
//            }
                $row['product_id'] = $v['product_id'];
                $row['product_sn'] = $v['product_sn'];
                $row['size_name'] = $size_list[$v['size_id']];//尺码
                $item['size_item'][] = $row;
                $items[$v['style_sn']] = $item;//款号的信息
            }
        }
        return $items;
    }

    /**
     * 获取商品详情
     *
     * @internal param $product_id
     * @return array
     */
    public function listcacheId()
    {
        $list = Yii::app()->cache->get('id-product-list');
        if (!$list) {
            foreach ($this->listcache() as $v) {
                $list[$v['product_id']] = $v;
            }
        }
        return $list;
    }

    /**
     * 商品缓存 不含下架
     *
     * @return mixed
     */
    public function listcache()
    {
        $list = Yii::app()->cache->get('all-product-list-' . Yii::app()->session['purchase_id']);
        if (!$list) {
            $purchase_id = Yii::app()->session['purchase_id'];
            $sql = "SELECT * FROM {{product}} WHERE purchase_id = {$purchase_id} AND disabled = 'false' AND is_down='0' ORDER BY serial_num ASC";
            $list = $this->ModelQueryAll($sql);
            Yii::app()->cache->set('all-product-list-' . Yii::app()->session['purchase_id'], $list, 3600 * 24);
        }
        return $list;
    }

    /**
     * 商品缓存 包含下架
     *
     * @return mixed
     */
    public function productListCache()
    {
        $list = Yii::app()->cache->get('all-product-list-without-down-' . Yii::app()->session['purchase_id']);
        if (!$list) {
            $purchase_id = Yii::app()->session['purchase_id'];
            $sql = "SELECT * FROM {{product}} WHERE purchase_id = {$purchase_id} AND disabled = 'false' ORDER BY serial_num ASC";
            $list = $this->ModelQueryAll($sql);
            Yii::app()->cache->set('all-product-list-without-down-' . Yii::app()->session['purchase_id'], $list, 86400);
        }
        return $list;
    }

    /**
     * 按款号缓存
     *
     * @param $model_sn
     * @return mixed
     */
    public function listModelCache($model_sn)
    {
        $size_list = $this->tableValue('size', 'size_name', 'size_id');
        $color_list = $this->tableValue('color', 'color_name', 'color_id');
        $purchaseId = Yii::app()->session['purchase_id'];
        $items = Yii::app()->cache->get('model-product-list-' . $purchaseId);
        if (!$items) {
            $sql = "SELECT * FROM {{product}} WHERE disabled = 'false' AND purchase_id = {$purchaseId}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $item = $v;
                $product_item['product_id'] = $v['product_id'];
                $product_item['product_sn'] = $v['product_sn'];
                $product_item['cost_price'] = $v['cost_price'];
                $product_item['size_id'] = $v['size_id'];
                $product_item['color_id'] = $v['color_id'];
                $product_item['is_down'] = $v['is_down'];
                $size_item['size_id'] = $v['size_id'];
                $size_item['size_name'] = $size_list[$v['size_id']];
                $color_item['color_id'] = $v['color_id'];
                $color_item['color_name'] = $color_list[$v['color_id']];
                if (isset($items[$v['model_sn']])) {
                    $items[$v['model_sn']]['product_list'][$v['size_id'] . '_' . $v['color_id']] = $product_item;
                    if (!in_array($size_item, $items[$v['model_sn']]['size_list']))
                        $items[$v['model_sn']]['size_list'][$v['size_id']] = $size_item;
                    if (!in_array($color_item, $items[$v['model_sn']]['color_list']))
                        $items[$v['model_sn']]['color_list'][] = $color_item;
                    continue;
                }
                $item['product_list'][$v['size_id'] . '_' . $v['color_id']] = $product_item;
                $item['size_list'][$v['size_id']] = $size_item;
                $item['color_list'][] = $color_item;
                $items[$v['model_sn']] = $item;
            }
            Yii::app()->cache->set('model-product-list-' . Yii::app()->session['purchase_id'], $items, 3600 * 24);
        }
        return $items[$model_sn];
    }

    /**
     * 判断此商品是否为下架，下架否则取消订单
     * @param $product_id
     * @return string
     */
    public function checkThisProductIsDown($product_id){
        $result = $this->ModelQueryRow("SELECT is_down FROM {{product}} WHERE product_id='{$product_id}' AND disabled='false'");
        if($result['is_down'] == '1'){
            return "";
        }else{
            return $product_id;
        }
    }

    /**
     * 商品详情
     *
     * @param $model_sn
     * @return mixed
     */
    public function detail($model_sn)
    {
        $list = $this->listModelCache($model_sn);
        return $list;
    }

    public function smallBig()
    {
        $items = Yii::app()->cache->get('cat_small_big_list');
        if (!$items) {
            $sql = "SELECT * FROM {{cat_big_small}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $item['b_id'] = $v['big_id'];
                $item['b_name'] = $v['big_cat_name'];
                $item['s_id'] = $v['small_id'];
                $item['s_name'] = $v['small_cat_name'];

                $items[$v['small_id']][] = $item;
            }
        }

        return $items;
    }

    /**
     * 订单商品统计
     *
     * @param $item_list
     * @return array
     */
    public function orderItems($item_list)
    {
        //按product_id 排列数组
        foreach ($this->listcache() as $v) {
            $all_list[$v['product_id']] = $v;
        }
        $big = $this->tableValue('cat_big', 'cat_name', 'big_id');
        $small = $this->tableValue('cat_small', 'cat_name', 'small_id');
        $items = array();
        $model = array();
        $total_nums = 0;
        $js = array();
        $amount = 0.00;
        foreach ($item_list as $v) {
            if (!isset($all_list[$v['product_id']])) continue;
            $product_item = $all_list[$v['product_id']];
            $item['product_id'] = $v['product_id'];
            $item['s_id'] = $product_item['cat_s'];
            $item['s_name'] = $small[$product_item['cat_s']];
            $item['nums'] = $v['nums'];
            //总计
            $total_nums += $v['nums'];
            $model[] = $v['model_sn'];
            $amount += $v['amount'];

            $items[$product_item['cat_b']]['b_id'] = $product_item['cat_b'];
            $items[$product_item['cat_b']]['b_name'] = $big[$product_item['cat_b']];
            //大分类款号
            $items[$product_item['cat_b']]['model'][] = $v['model_sn'];

            //大分类商品数量
            if (!isset($items[$product_item['cat_b']]['nums'])) $items[$product_item['cat_b']]['nums'] = 0;
            $items[$product_item['cat_b']]['nums'] += $v['nums'];
            //大分类商品总金额数量
            if (!isset($items[$product_item['cat_b']]['amount'])) $items[$product_item['cat_b']]['amount'] = 0;
            $items[$product_item['cat_b']]['amount'] += $v['amount'];


            $items[$product_item['cat_b']]['small'][$item['s_id']]['s_id'] = $item['s_id'];
            $items[$product_item['cat_b']]['small'][$item['s_id']]['name'] = $small[$item['s_id']];
            //小分类款号
            $items[$product_item['cat_b']]['small'][$item['s_id']]['model'][] = $v['model_sn'];

            //小分类商品数量
            if (!isset($items[$product_item['cat_b']]['small'][$item['s_id']]['nums'])) $items[$product_item['cat_b']]['small'][$item['s_id']]['nums'] = 0;
            $items[$product_item['cat_b']]['small'][$item['s_id']]['nums'] += $v['nums'];
            //小分类商品总金额数量
            if (!isset($items[$product_item['cat_b']]['small'][$item['s_id']]['amount'])) $items[$product_item['cat_b']]['small'][$item['s_id']]['amount'] = 0;
            $items[$product_item['cat_b']]['small'][$item['s_id']]['amount'] += $v['amount'];
            $js[] = $v['model_sn'];
        }
        $all = count(array_unique($js));
        return array('list' => $items, 'total_nums' => $total_nums, 'model' => $model, 'amount' => $amount, 'all' => $all);
    }

    public function orderSpringItems($item_list)
    {
        //按product_id 排列数组
        foreach ($this->listcache() as $v) {
            $all_list[$v['product_id']] = $v;
        }

        $big = $this->tableValue('cat_big', 'cat_name', 'big_id');
        //var_dump($big);die;
        $small = $this->tableValue('cat_small', 'cat_name', 'small_id');
        //var_dump($small);die;
        $items = array();
        $model = array();
        $total_nums = 0;
        $season_1 = 0;
        $js = array();
        $amount = 0.00;
        foreach ($item_list as $v) {
            if (!isset($all_list[$v['product_id']])) continue;
            $product_item = $all_list[$v['product_id']];
            if ($product_item['season_id'] == '1') {
                $item['product_id'] = $v['product_id'];
                $item['s_id'] = $product_item['cat_s'];
                $item['s_name'] = $small[$product_item['cat_s']];
                $item['nums'] = $v['nums'];

                //总计
                $total_nums += $v['nums'];
                $model[] = $v['model_sn'];
                $amount += $v['amount'];

                $items[$product_item['cat_b']]['b_id'] = $product_item['cat_b'];
                $items[$product_item['cat_b']]['b_name'] = $big[$product_item['cat_b']];
                //大分类款号
                $items[$product_item['cat_b']]['model'][] = $v['model_sn'];

                //大分类商品数量
                if (!isset($items[$product_item['cat_b']]['nums'])) $items[$product_item['cat_b']]['nums'] = 0;
                $items[$product_item['cat_b']]['nums'] += $v['nums'];
                //大分类商品总金额数量
                if (!isset($items[$product_item['cat_b']]['amount'])) $items[$product_item['cat_b']]['amount'] = 0;
                $items[$product_item['cat_b']]['amount'] += $v['amount'];

                //大分类季节商品数量
                if (!isset($items[$product_item['cat_b']]['season_id_1'])) $items[$product_item['cat_b']]['season_id_1'] = 0;
                if ($product_item['season_id'] == 1) {
                    $items[$product_item['cat_b']]['season_id_1'] += $v['nums'];
                    $season_1 += $v['nums'];
                }
                $items[$product_item['cat_b']]['small'][$item['s_id']]['s_id'] = $item['s_id'];
                $items[$product_item['cat_b']]['small'][$item['s_id']]['name'] = $small[$item['s_id']];
                //小分类款号
                $items[$product_item['cat_b']]['small'][$item['s_id']]['model'][] = $v['model_sn'];

                //小分类商品数量
                if (!isset($items[$product_item['cat_b']]['small'][$item['s_id']]['nums'])) $items[$product_item['cat_b']]['small'][$item['s_id']]['nums'] = 0;
                $items[$product_item['cat_b']]['small'][$item['s_id']]['nums'] += $v['nums'];
                //小分类商品总金额数量
                if (!isset($items[$product_item['cat_b']]['small'][$item['s_id']]['amount'])) $items[$product_item['cat_b']]['small'][$item['s_id']]['amount'] = 0;
                $items[$product_item['cat_b']]['small'][$item['s_id']]['amount'] += $v['amount'];
                $js[] = $v['model_sn'];
                //小分类季节商品数量
                if (!isset($items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_1'])) $items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_1'] = 0;
                if ($product_item['season_id'] == 1) $items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_1'] += $v['nums'];
            }
        }
        $all = count(array_unique($js));
        return array('list' => $items, 'total_nums' => $total_nums, 'season_1' => $season_1, 'model' => $model, 'amount' => $amount, 'all' => $all);
    }


    public function orderSummerItems($item_list)
    {
        //按product_id 排列数组 显示所有商品$all_list
        foreach ($this->listcache() as $v) {
            $all_list[$v['product_id']] = $v;
        }
        //var_dump($all_list);die;
        $big = $this->tableValue('cat_big', 'cat_name', 'big_id');
        //var_dump($big);die;
        $small = $this->tableValue('cat_small', 'cat_name', 'small_id');
        //var_dump($small);die;
        $items = array();
        $model = array();
        $total_nums = 0;
        $season_2 = 0;
        $amount = 0.00;
        $js = array();
        //var_dump($item_list);die;
        //显示购买的商品 $item_list
        foreach ($item_list as $v) {
            if (!isset($all_list[$v['product_id']])) continue;
            $product_item = $all_list[$v['product_id']];
//                var_dump($product_item);die;
            if ($product_item['season_id'] == '2') {
                $item['product_id'] = $v['product_id'];
                $item['s_id'] = $product_item['cat_s'];
                $item['s_name'] = $small[$product_item['cat_s']];
                $item['nums'] = $v['nums'];
                //总计
                $total_nums += $v['nums'];
                $model[] = $v['model_sn'];
                $amount += $v['amount'];

                $items[$product_item['cat_b']]['b_id'] = $product_item['cat_b'];
                $items[$product_item['cat_b']]['b_name'] = $big[$product_item['cat_b']];
                //大分类款号
                $items[$product_item['cat_b']]['model'][] = $v['model_sn'];

                //大分类商品数量
                if (!isset($items[$product_item['cat_b']]['nums'])) $items[$product_item['cat_b']]['nums'] = 0;
                $items[$product_item['cat_b']]['nums'] += $v['nums'];
                //大分类商品总金额数量
                if (!isset($items[$product_item['cat_b']]['amount'])) $items[$product_item['cat_b']]['amount'] = 0;
                $items[$product_item['cat_b']]['amount'] += $v['amount'];

                //大分类季节商品数量
                if (!isset($items[$product_item['cat_b']]['season_id_2'])) $items[$product_item['cat_b']]['season_id_2'] = 0;
                if ($product_item['season_id'] == 2) {
                    $items[$product_item['cat_b']]['season_id_2'] += $v['nums'];
                    $season_2 += $v['nums'];
                }
                $items[$product_item['cat_b']]['small'][$item['s_id']]['s_id'] = $item['s_id'];
                $items[$product_item['cat_b']]['small'][$item['s_id']]['name'] = $small[$item['s_id']];
                //小分类款号
                $items[$product_item['cat_b']]['small'][$item['s_id']]['model'][] = $v['model_sn'];

                //小分类商品数量
                if (!isset($items[$product_item['cat_b']]['small'][$item['s_id']]['nums'])) $items[$product_item['cat_b']]['small'][$item['s_id']]['nums'] = 0;
                $items[$product_item['cat_b']]['small'][$item['s_id']]['nums'] += $v['nums'];
                //小分类商品总金额数量
                if (!isset($items[$product_item['cat_b']]['small'][$item['s_id']]['amount'])) $items[$product_item['cat_b']]['small'][$item['s_id']]['amount'] = 0;
                $items[$product_item['cat_b']]['small'][$item['s_id']]['amount'] += $v['amount'];
                $js[] = $v['model_sn'];
                //小分类季节商品数量
                if (!isset($items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_2'])) $items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_2'] = 0;
                if ($product_item['season_id'] == 2) $items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_2'] += $v['nums'];

            }
        }
        $all = count(array_unique($js));
        return array('list' => $items, 'total_nums' => $total_nums, 'season_2' => $season_2, 'model' => $model, 'amount' => $amount, 'all' => $all);
    }

    public function orderSprandSumItems($item_list)
    {
        $season_one = Yii::app()->params['season_one'];
        $season_two = Yii::app()->params['season_two'];
        //按product_id 排列数组
        foreach ($this->productListCache() as $v) {
            $all_list[$v['product_id']] = $v;
        }

        $big = $this->tableValue('cat_big', 'cat_name', 'big_id');
        $small = $this->tableValue('cat_small', 'cat_name', 'small_id');

        $items = array();
        $model = array();
        $total_nums = 0;
        $season_1 = 0;
        $season_2 = 0;
        $amount = 0.00;
        $js = array();
        foreach ($item_list as $v) {
            if (!isset($all_list[$v['product_id']])) continue;
            $product_item = $all_list[$v['product_id']];
            $item['product_id'] = $v['product_id'];
            $item['s_id'] = $product_item['cat_s'];
            $item['s_name'] = $small[$product_item['cat_s']];
            $item['nums'] = $v['nums'];

            //总计
            $total_nums += $v['nums'];
            $model[] = $v['model_sn'];
            $amount += $v['amount'];

            $items[$product_item['cat_b']]['b_id'] = $product_item['cat_b'];
            $items[$product_item['cat_b']]['b_name'] = $big[$product_item['cat_b']];
            //大分类款号
            $items[$product_item['cat_b']]['model'][] = $v['model_sn'];

            //大分类商品数量
            if (!isset($items[$product_item['cat_b']]['nums'])) $items[$product_item['cat_b']]['nums'] = 0;
            $items[$product_item['cat_b']]['nums'] += $v['nums'];
            //大分类商品总金额数量
            if (!isset($items[$product_item['cat_b']]['amount'])) $items[$product_item['cat_b']]['amount'] = 0;
            $items[$product_item['cat_b']]['amount'] += $v['amount'];

            //大分类季节商品数量
            if (!isset($items[$product_item['cat_b']]['season_id_1'])) $items[$product_item['cat_b']]['season_id_1'] = 0;
            if (!isset($items[$product_item['cat_b']]['season_id_2'])) $items[$product_item['cat_b']]['season_id_2'] = 0;
            if ($product_item['season_id'] == $season_one) {
                $items[$product_item['cat_b']]['season_id_1'] += $v['nums'];
                $season_1 += $v['nums'];
            }
            if ($product_item['season_id'] == $season_two) {
                $items[$product_item['cat_b']]['season_id_2'] += $v['nums'];
                $season_2 += $v['nums'];
            }

            $items[$product_item['cat_b']]['small'][$item['s_id']]['s_id'] = $item['s_id'];
            $items[$product_item['cat_b']]['small'][$item['s_id']]['name'] = $small[$item['s_id']];
            //小分类款号
            $items[$product_item['cat_b']]['small'][$item['s_id']]['model'][] = $v['model_sn'];

            //小分类商品数量
            if (!isset($items[$product_item['cat_b']]['small'][$item['s_id']]['nums'])) $items[$product_item['cat_b']]['small'][$item['s_id']]['nums'] = 0;
            $items[$product_item['cat_b']]['small'][$item['s_id']]['nums'] += $v['nums'];
            //小分类商品总金额数量
            if (!isset($items[$product_item['cat_b']]['small'][$item['s_id']]['amount'])) $items[$product_item['cat_b']]['small'][$item['s_id']]['amount'] = 0;
            $items[$product_item['cat_b']]['small'][$item['s_id']]['amount'] += $v['amount'];

            //小分类季节商品数量
            if (!isset($items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_1'])) $items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_1'] = 0;
            if (!isset($items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_2'])) $items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_2'] = 0;
            if ($product_item['season_id'] == $season_one) $items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_1'] += $v['nums'];
            if ($product_item['season_id'] == $season_two) $items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_2'] += $v['nums'];
        }
        $all = count(array_unique($js));
        return array('list' => $items, 'total_nums' => $total_nums, 'season_1' => $season_1, 'season_2' => $season_2, 'model' => $model, 'amount' => $amount, 'all' => $all);
    }


    public function orderJiaGeDaiItems($item_list)
    {
        //按product_id 排列数组
        foreach ($this->productListCache() as $v) {
            $all_list[$v['product_id']] = $v;
        }

        $big = $this->tableValue('cat_big', 'cat_name', 'big_id');
        $jgd = array(
            '1' => '0-99',
            '2' => '100-199',
            '3' => '200-299',
            '4' => '300-399',
            '5' => '400-499',
            '6' => '500-999',
            '7' => '1000-1499',
            '8' => '1500-2000',
            '9' => '2000以上',
        );
        //var_dump($jgd);die;
        $items = array();
        $model = array();
        $total_nums = 0;
        $js = array();
        //$all=0;
        $amount = 0.00;
        foreach ($item_list as $v) { //$item_list所有已定款式
            if (!isset($all_list[$v['product_id']])) continue;
            $product_item = $all_list[$v['product_id']];//$product_item 是循环出来的该产品的product的商品信息
            $item['product_id'] = $v['product_id'];
            $item['s_id'] = $product_item['cat_s'];
            $dpj = $product_item['price_level_id'];
            $item['dpj'] = $jgd[$dpj];
            $item['all'] =
            $item['nums'] = $v['nums'];//购买该产品的数量
            //总计
            $total_nums += $v['nums'];//统计购买该产品的数量
            $model[] = $v['model_sn'];//统计型号
            $amount += $v['amount'];//统计购买此产品的总价格

            $items[$product_item['cat_b']]['b_id'] = $product_item['cat_b'];//该大类的ID号
            $items[$product_item['cat_b']]['b_name'] = $big[$product_item['cat_b']];//大类名称
            $items[$product_item['cat_b']]['model'][] = $v['model_sn'];//此商品的款号

            if (!isset($items[$product_item['cat_b']]['nums'])) $items[$product_item['cat_b']]['nums'] = 0;//判断该大类下是否有该商品
            $items[$product_item['cat_b']]['nums'] += $v['nums'];//统计该大类的数量

            if (!isset($items[$product_item['cat_b']]['amount'])) $items[$product_item['cat_b']]['amount'] = 0;
            $items[$product_item['cat_b']]['amount'] += $v['amount'];//统计该大类商品总金额数量


            $items[$product_item['cat_b']]['dpj'][$item['dpj']]['s_id'] = $item['s_id'];//显示大类名称
            $items[$product_item['cat_b']]['dpj'][$item['dpj']]['name'] = $item['dpj'];//显示吊牌价区间
            //小分类款号
            $items[$product_item['cat_b']]['dpj'][$item['dpj']]['model'][] = $v['model_sn'];

            //小分类商品数量
            if (!isset($items[$product_item['cat_b']]['dpj'][$item['dpj']]['nums'])) $items[$product_item['cat_b']]['dpj'][$item['dpj']]['nums'] = 0;
            $items[$product_item['cat_b']]['dpj'][$item['dpj']]['nums'] += $v['nums'];
            //var_dump($items[$product_item['cat_b']]['dpj'][$item['s_id']]['nums']);die;
            //小分类商品总金额数量
            if (!isset($items[$product_item['cat_b']]['dpj'][$item['dpj']]['amount'])) $items[$product_item['cat_b']]['dpj'][$item['dpj']]['amount'] = 0;
            $items[$product_item['cat_b']]['dpj'][$item['dpj']]['amount'] += $v['amount'];
            $js[] = $v['model_sn'];
//                //小分类季节商品数量
//                if (!isset($items[$product_item['cat_b']]['dpj'][$item['dpj']]['dpj'])) $items[$product_item['cat_b']]['dpj'][$item['dpj']]['dpj'] = 0;
////                if (!isset($items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_2'])) $items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_2'] = 0;
//                if ($product_item['dpj'] == $item['dpj']) $items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_1'] += $v['nums'];
////                if ($product_item['season_id'] == 2) $items[$product_item['cat_b']]['small'][$item['s_id']]['season_id_2'] += $v['nums'];
        }
        $all = count(array_unique($js));
        return array('list' => $items, 'total_nums' => $total_nums, 'model' => $model, 'amount' => $amount, 'all' => $all);
    }

    public function checkStatus($data = '')
    {
        $sql = "SELECT status FROM `meet_order` WHERE customer_id='" . $data . "'";
        return $this->ModelQueryAll($sql);
    }
    /**
     * 获取客户订单详情 
     * @param  [type]  $purchase_id 订货会品牌
     * @param  [type]  $customer    客户id
     * @param  boolean $model_sn    型号
     * @return [type]               用户订单详情以及商品是否下架
     */
    public function getThisOrderedInfo($purchase_id, $customer, $model_sn = false){
        if($model_sn){
            $where = " AND model_sn ='{$model_sn}'";
        }else{
            $where = "";
        }
        //获取客户下的订单 
        $order_id = $this->ModelQueryRow("SELECT order_id FROM {{order}} WHERE customer_id='{$customer}' AND purchase_id='{$purchase_id}' AND disabled='false'");
        if(empty($order_id)) return array();
        //获取订单的详细信息
        $item_list = $this->ModelQueryAll("SELECT nums,product_id,style_sn,model_sn FROM {{order_items}} WHERE order_id='{$order_id['order_id']}' {$where} AND disabled='false'");
        if(empty($item_list)) return array();
        $order = new Order();
        //是否下架
        $res = $order->getThisProductIsDown();
        foreach ($item_list as $v) {
            $model[$v['product_id']] = $v;
            //???不存在为上架？
            $model[$v['product_id']]['is_down'] = isset($res[$v['product_id']]) ? $res[$v['product_id']] : 0;
        }
        return $model;
    }
}
