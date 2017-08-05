<?php

/**
 *
 * @author        chenfenghua <843958575@qq.com>
 * @copyright     Copyright (c) 2007-2014 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       mall.model
 * @license       http://www.octmami.com/license
 * @version       v1.2.0
 */
class AppProduct extends B2cModel
{
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
     * app获取该款号的所有尺寸详细信息
     * @param $model_sn
     * @param $purchase_id
     * @param $customer_id
     * @return array
     */
    public function getProductListsInfo($model_sn, $purchase_id, $customer_id)
    {
        $size_list = $this->tableValue('size', 'size_name', 'size_id');
        $color_list = $this->tableValue('color', 'color_name', 'color_id');

        $product_list = Yii::app()->cache->get("app-get-size-color-product_sn-" . $model_sn);
        if (!$product_list) {
            $sql = "SELECT size_id,color_id,product_sn,product_id FROM {{product}} WHERE model_sn = '{$model_sn}' AND purchase_id='{$purchase_id}' AND disabled='false' ";
            $res = $this->ModelQueryAll($sql);
            foreach ($res as $v) {
                $product_list[$v['size_id'] . "_" . $v['color_id']] = $v;
            }
            Yii::app()->cache->set("app-get-size-color-product_sn-" . $model_sn, $product_list, 3600);
        }

        $size_tran_list = Yii::app()->cache->get("app-get-size_id-by-purchase-id-" . $model_sn);
        if (!$size_tran_list) {
            $sql = "SELECT size_id FROM {{product}} WHERE model_sn='{$model_sn}' AND purchase_id='{$purchase_id}' AND disabled='false' GROUP BY size_id";
            $all_size = $this->ModelQueryAll($sql);
            foreach ($all_size as $size_trans) {
                $size_tran_list[$size_trans['size_id']] = $size_trans;
            }
            Yii::app()->cache->set("app-get-size_id-by-purchase-id-" . $model_sn, $size_tran_list, 3600);
        }

        $sql = "SELECT i.nums,i.product_sn FROM {{order_items}} AS i LEFT JOIN {{order}} AS o ON o.order_id=i.order_id WHERE o.customer_id='{$customer_id}' AND i.disabled='false'";
        $customers_product = $this->ModelQueryAll($sql);

        foreach ($customers_product as $v) {
            $item[$v['product_sn']] = $v['nums'];
        }

        $all_color = Yii::app()->cache->get("app-get-color_id-by-purchase-id-" . $model_sn);
        if (!$all_color) {
            $sql = "SELECT color_id FROM {{product}} WHERE model_sn='{$model_sn}' AND purchase_id='{$purchase_id}' AND disabled='false' GROUP BY color_id";
            $all_color = $this->ModelQueryAll($sql);
            Yii::app()->cache->set("app-get-color_id-by-purchase-id-" . $model_sn, $all_color, 3600);
        }
        $arr = array();

        foreach ($all_color as $v) {
            $product_info['color_id'] = $v['color_id'];
            $product_info['color_name'] = $color_list[$v['color_id']];
            foreach ($size_tran_list as &$val) {
                $items[$val['size_id']]['product_sn'] = isset($product_list[$val['size_id'] . "_" . $v['color_id']]['product_sn']) ? $product_list[$val['size_id'] . "_" . $v['color_id']]['product_sn'] : 0;
                $items[$val['size_id']]['size_id'] = $val['size_id'];
                $items[$val['size_id']]['size_name'] = $size_list[$val['size_id']];
                $items[$val['size_id']]['product_id'] = isset($product_list[$val['size_id'] . "_" . $v['color_id']]['product_id']) ? $product_list[$val['size_id'] . "_" . $v['color_id']]['product_id'] : 0;
                if (isset($items[$val['size_id']]['product_sn']) && !empty($items[$val['size_id']]['product_sn'])) {
                    $items[$val['size_id']]['nums'] = isset($item[$items[$val['size_id']]['product_sn']]) ? $item[$items[$val['size_id']]['product_sn']] : 0;
                } else {
                    $items[$val['size_id']]['nums'] = 0;
                }
            }
            ksort($items);
            $product_info['size_list'] = array_values($items);
            $arr[] = $product_info;
        }
        return array_values($arr);
    }


    public function getOnlyBroughtModel($model_sn, $purchase_id, $customer_id)
    {
        $size_list = $this->tableValue('size', 'size_name', 'size_id');
        $color_list = $this->tableValue('color', 'color_name', 'color_id');

        $product_list = Yii::app()->cache->get("app-get-size-color-product_sn-" . $model_sn);
        if (!$product_list) {
            $sql = "SELECT size_id,color_id,product_sn,product_id FROM {{product}} WHERE model_sn = '{$model_sn}' AND purchase_id='{$purchase_id}' AND disabled='false' ";
            $res = $this->ModelQueryAll($sql);
            foreach ($res as $v) {
                $product_list[$v['size_id'] . "_" . $v['color_id']] = $v;
            }
            Yii::app()->cache->set("app-get-size-color-product_sn-" . $model_sn, $product_list, 3600);
        }

        $size_tran_list = Yii::app()->cache->get("app-get-size_id-by-purchase-id-" . $model_sn);
        if (!$size_tran_list) {
            $sql = "SELECT size_id FROM {{product}} WHERE model_sn='{$model_sn}' AND purchase_id='{$purchase_id}' AND disabled='false' GROUP BY size_id";
            $all_size = $this->ModelQueryAll($sql);
            foreach ($all_size as $size_trans) {
                $size_tran_list[$size_trans['size_id']] = $size_trans;
            }
            Yii::app()->cache->set("app-get-size_id-by-purchase-id-" . $model_sn, $size_tran_list, 3600);
        }

        $sql = "SELECT i.nums,i.product_sn FROM {{order_items}} AS i LEFT JOIN {{order}} AS o ON o.order_id=i.order_id WHERE o.customer_id='{$customer_id}' AND i.disabled='false'";
        $customers_product = $this->ModelQueryAll($sql);

        foreach ($customers_product as $v) {
            $item[$v['product_sn']] = $v['nums'];
        }

        $all_color = Yii::app()->cache->get("app-get-color_id-by-purchase-id-" . $model_sn);
        if (!$all_color) {
            $sql = "SELECT color_id,model_sn,name,wave_id,cost_price FROM {{product}} WHERE model_sn='{$model_sn}' AND purchase_id='{$purchase_id}' AND disabled='false' GROUP BY color_id";
            $all_color = $this->ModelQueryAll($sql);
            Yii::app()->cache->set("app-get-color_id-by-purchase-id-" . $model_sn, $all_color, 3600);
        }
        $arr = array();
        $order_num = 0;
        foreach ($all_color as $v) {
            $product_info['color_id'] = $v['color_id'];
            $product_info['color_name'] = $color_list[$v['color_id']];
            $num = 0;
            foreach ($size_tran_list as &$val) {
                $items[$val['size_id']]['product_sn'] = isset($product_list[$val['size_id'] . "_" . $v['color_id']]['product_sn']) ? $product_list[$val['size_id'] . "_" . $v['color_id']]['product_sn'] : 0;
                $items[$val['size_id']]['size_id'] = $val['size_id'];
                $items[$val['size_id']]['size_name'] = $size_list[$val['size_id']];
                $items[$val['size_id']]['product_id'] = isset($product_list[$val['size_id'] . "_" . $v['color_id']]['product_id']) ? $product_list[$val['size_id'] . "_" . $v['color_id']]['product_id'] : 0;
                if (isset($items[$val['size_id']]['product_sn']) && !empty($items[$val['size_id']]['product_sn'])) {
                    $items[$val['size_id']]['nums'] = isset($item[$items[$val['size_id']]['product_sn']]) ? $item[$items[$val['size_id']]['product_sn']] : 0;
                } else {
                    $items[$val['size_id']]['nums'] = 0;
                }
                $num += $items[$val['size_id']]['nums'];
                $order_num += $items[$val['size_id']]['nums'];
            }
            if ($num == 0) {
                continue;
            } else {
                ksort($items);
                $product_info['nums'] = $num;
                $product_info['size_list'] = array_values($items);
                $arr[] = $product_info;
            }
        }
        $result['product_list'] = array_values($arr);
        $result['order_num'] = $order_num;
        return $result;
    }

    /**
     * 获取当前该款号总数
     *
     * @param $customer_id
     * @param $model_sn
     * @param string $serial_num
     * @return int
     */
    public function getThisModelOrdered($customer_id, $model_sn, $serial_num = "")
    {
        $style_sn = "";
        if (!empty($serial_num)) {
            $sty_res = $this->ModelQueryRow("SELECT style_sn FROM {{product}} WHERE serial_num='{$serial_num}' AND disabled='false'");
            $style_sn = " AND i.style_sn='" . $sty_res['style_sn']."' ";
        }
        $sql = "SELECT COALESCE(SUM(i.nums),0) AS num FROM {{order}} AS o LEFT JOIN {{order_items}} AS i ON i.order_id=o.order_id WHERE o.customer_id='{$customer_id}' AND i.model_sn='{$model_sn}' " . $style_sn . " AND i.disabled='false'";
        $num = $this->ModelQueryRow($sql);
        if (empty($num)) return 0;
        return $num['num'];
    }

    /**
     * 点击预订
     *
     * @param $order_items
     * @param $purchase_id
     * @param $customer_id
     * @param $customer_name
     * @return bool
     */
    public function orderSubmit($order_items, $purchase_id, $customer_id, $customer_name)
    {
        $create_time = time();
        $order = new Order();
        //会员订单是否生成
        $order_list = $order->orderItems($purchase_id, $customer_id);//已经订购的产品
        //订单order_id获取
        if (isset($order_list['order_row']) && $order_list['order_row']) $order_id = $order_list['order_row']['order_id'];
        else $order_id = $order->build_order_no();

        //订单商品列表
        $item_list = $this->orderToDo($order_items, $order_id, $order_list['item_list']);

        //订单主表
        if (isset($order_list['order_row']) && $order_list['order_row']) {
            $order_sql = "UPDATE meet_order SET edit_time = {$create_time} WHERE order_id = {$order_list['order_row']['order_id']}";
        } else {
            $order_sql = "INSERT INTO meet_order (order_id,purchase_id,status,customer_id,customer_name,cost_item,create_time) VALUE
            ({$order_id},{$purchase_id},'active',{$customer_id},'{$customer_name}','{$item_list['total']}',{$create_time})";
        }

        //事务处理
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $this->ModelExecute($order_sql);
            $this->ModelExecute($item_list['sql']);
            $transaction->commit();
            //更新订单缓存
            $order = new Order();
            $order->orderCache($purchase_id, $customer_id);
            return true;
        } catch (Exception $e) {
            $transaction->rollback();
            return false;
        }
    }

    /**
     * 在预订前检查此用户是否存在
     *
     * @param $customer
     * @param $purchase_id
     * @return mixed
     */
    public function checkThisCustomerIsValid($customer, $purchase_id){
        $sql = "SELECT * FROM {{customer}} WHERE customer_id='{$customer}' AND purchase_id='{$purchase_id}'  AND disabled='false'";
        return $this->ModelQueryRow($sql);
    }

    /**
     * 订货准备
     * @param $order_items
     * @param $order_id
     * @param $item_list
     * @return array
     */
    public function orderToDo($order_items, $order_id, $item_list)
    {
        $productModel = new Product();
        $product_list = $productModel->listcacheId();
        $sql = '';
        $order_product_list = isset($item_list) && $item_list ? $item_list : array();//已定单 数量等
        foreach ($order_items as $k => $v) {
            $num = isset($v[1]) ? $v[1] : '';
            if ($num == 0) {
                $num = '';
            }
            $res = $productModel->checkThisProductIsDown($v[0]);
            if(empty($res)){
                $delete_data_arr[] = "UPDATE  meet_order_items SET disabled='true' WHERE order_id = {$order_id} AND product_id = '" . $v[0] . "' AND disabled='false';";
                continue;
            }
            if ($num == '') {
                if (isset($product_list[$v[0]]) && $product_list[$v[0]])
                    $delete_data_arr[] = "UPDATE  meet_order_items SET disabled='true' WHERE order_id = {$order_id} AND product_id = '" . $v[0] . "' AND disabled='false';";
                continue;
            }
            $product_sn = $product_list[$v[0]]['product_sn'];
            $style_sn = $product_list[$v[0]]['style_sn'];
            $model_sn = $product_list[$v[0]]['model_sn'];
            $name = $product_list[$v[0]]['name'];
            $price = $product_list[$v[0]]['cost_price'];
            $amount = sprintf('%.2f', $price * $num);
            if (isset($order_product_list[$v[0]]) && $order_product_list[$v[0]]) {
                $update_data_arr[] = "UPDATE meet_order_items SET price = '{$price}',amount = '{$amount}',nums = {$num} WHERE order_id = {$order_id} AND product_id = {$v[0]}  AND disabled='false';";
            } else {
                $insert_data_arr[] = "({$order_id},{$v[0]},'{$product_sn}','{$style_sn}','{$model_sn}','{$name}','{$price}','{$amount}',{$num})";
            }
        }
        //新增商品
        if (isset($insert_data_arr) && $insert_data_arr)
            $sql .= 'INSERT INTO {{order_items}} (order_id,product_id,product_sn,style_sn,model_sn,`name`,price,amount,nums) VALUES ' . implode(',', $insert_data_arr) . ';';
        //更新商品
        if (isset($update_data_arr) && $update_data_arr)
            $sql .= implode('', $update_data_arr);
        //删除商品
        if (isset($delete_data_arr) && $delete_data_arr) {
            $sql .= implode('', $delete_data_arr);
        }
        return array('sql' => $sql, 'total' => 0);
    }

    /**
     * 此流水号的商品规格参数
     *
     * @param $serial_num
     * @param $purchase_id
     * @param $customer_id
     * @return array]
     */
    public function getThisProductInfo($serial_num, $purchase_id, $customer_id)
    {
        $sql = "SELECT model_sn FROM {{product}} WHERE serial_num='{$serial_num}' AND disabled='false'";
        $result = $this->ModelQueryRow($sql);
        if (empty($result)) {
            return array();
        }
        $sql = "SELECT name,model_sn,wave_id,cost_price FROM {{product}} WHERE serial_num='{$serial_num}' AND disabled='false'";
        $data = $this->ModelQueryRow($sql);
        $data['product_list'] = $this->getProductListsInfo($result['model_sn'], $purchase_id, $customer_id);
        $data['order_num'] = $this->getThisModelOrdered($customer_id, $result['model_sn']);
        $have = $this->getThisModelOrdered($customer_id, $result['model_sn'], $serial_num);
        if ($have >= 1) {
            $data['is_order'] = 1;
        } else {
            $data['is_order'] = 2;
        }
        return $data;
    }

    /**
     * 按季节排序
     * @param $orderList
     * @return mixed
     */
    public function orderSeasonTable($orderList)
    {
        $productModel = new Product();
        //配置第一个季节ID
        $season_spring_one = Yii::app()->params['season_one'];
        //配置第二个季节ID
        $season_spring_two = Yii::app()->params['season_two'];

        $listProducts = $productModel->productListCache();
        $all_list = array();
        foreach ($listProducts as $list) {
            $all_list[$list['product_id']] = $list;
        }

        $big = $this->tableValue('cat_big', 'cat_name', 'big_id');
        $small = $this->tableValue('cat_small', 'cat_name', 'small_id');

        $total = 0;
        $amount = 0;
        $season_spring = 0;
        $season_summer = 0;
        $model_num = array();

        foreach ($orderList['item_list'] as $trans) {
            if (!isset($all_list[$trans['product_id']])) continue;
            $product_items = $all_list[$trans['product_id']];
            $cat_big = $product_items['cat_b'];
            $cat_small = $product_items['cat_s'];
            //大类id
            $item[$cat_big]['cat_id'] = $cat_big;

            //大类名称
            $item[$cat_big]['cat_name'] = $big[$cat_big];

            //小类ID
            $season[$cat_small]['cat_id'] = $cat_small;
            $lists['cat_id'] = $season[$cat_small]['cat_id'];

            //小类名称
            $season[$cat_small]['cat_name'] = $small[$cat_small];
            $lists['cat_name'] = $season[$cat_small]['cat_name'];

            $model_num[] = $trans['model_sn'];

            //小类款号数量
            $season[$cat_big."_".$cat_small]['model_sn'][] = $trans['model_sn'];
            $season[$cat_big."_".$cat_small]['model_sn_nums'] = count(array_unique($season[$cat_big."_".$cat_small]['model_sn']));
            $lists['model_count'] = $season[$cat_big."_".$cat_small]['model_sn_nums'];

            //season_id = 1数量
            if (!isset($season[$cat_big."_".$cat_small]['spring'])) $season[$cat_big."_".$cat_small]['spring'] = 0;
            if (!isset($item[$cat_big]['season_spring_count'])) $item[$cat_big]['season_spring_count'] = 0;
            if ($product_items['season_id'] == $season_spring_one) {
                $season_spring += $trans['nums'];
                $season[$cat_big."_".$cat_small]['spring'] += $trans['nums'];
                $item[$cat_big]['season_spring_count'] += $trans['nums'];
            }
            $lists['season_spring'] = $season[$cat_big."_".$cat_small]['spring'];

            //season_id = 2数量
            if (!isset($season[$cat_big."_".$cat_small]['summer'])) $season[$cat_big."_".$cat_small]['summer'] = 0;
            if (!isset($item[$cat_big]['season_summer_count'])) $item[$cat_big]['season_summer_count'] = 0;
            if ($product_items['season_id'] == $season_spring_two) {
                $season_summer += $trans['nums'];
                $season[$cat_big."_".$cat_small]['summer'] += $trans['nums'];
                $item[$cat_big]['season_summer_count'] += $trans['nums'];
            }
            $lists['season_summer'] = $season[$cat_big."_".$cat_small]['summer'];

            //小类总件数
            if (!isset($season[$cat_big."_".$cat_small]['cat_nums'])) $season[$cat_big."_".$cat_small]['cat_nums'] = 0;
            $season[$cat_big."_".$cat_small]['cat_nums'] += $trans['nums'];
            $lists['cat_nums'] = $season[$cat_big."_".$cat_small]['cat_nums'];
            $total += $trans['nums'];

            //小类总金额
            if (!isset($season[$cat_big."_".$cat_small]['amount'])) $season[$cat_big."_".$cat_small]['amount'] = 0;
            $season[$cat_big."_".$cat_small]['amount'] += $trans['amount'];
            $lists['cat_amount'] = $season[$cat_big."_".$cat_small]['amount'];
            $amount += $trans['amount'];

            //大类总件数
            if (!isset($item[$cat_big]['all_num'])) $item[$cat_big]['all_num'] = 0;
            $item[$cat_big]['all_num'] += $trans['nums'];

            //大类总金额
            if (!isset($item[$cat_big]['all_amount'])) $item[$cat_big]['all_amount'] = 0;
            $item[$cat_big]['all_amount'] += $trans['amount'];
            $item[$cat_big]['all_list'][$cat_small] = $lists;
        }
        //总体
        $products['total'] = $total;
        $products['total_percent'] = "100%";
        $products['amount'] = $amount;
        $products['amount_percent'] = "100%";
        $products['spring'] = $season_spring;
        $products['summer'] = $season_summer;
        $products['model'] = count(array_unique($model_num));

        if (empty($item)) return array();

        //再算占比
        $productItem = array();
        foreach ($item as $cat_b) {
            $all_model = 0;
            $cat_list = array();
            foreach ($cat_b['all_list'] as $cat_s) {

                //大类款号总数
                $all_model += $cat_s['model_count'];

                //小类数量占比
                if (empty($products['total'])) {
                    $cat_s['cat_num_percent'] = 0;
                } else {
                    $cat_s['cat_num_percent'] = (round($cat_s['cat_nums'] / $products['total'], 3) * 100) . "%";
                }

                //小类金额占比
                if (empty($products['amount'])) {
                    $cat_s['cat_amount_percent'] = 0;
                } else {
                    $cat_s['cat_amount_percent'] = (round($cat_s['cat_amount'] / $products['amount'], 3) * 100) . "%";
                }

                $cat_list[] = $cat_s;
            }
            unset($cat_b['all_list']);
            if (empty($products['total'])) {
                $cat_b['all_num_percent'] = 0;
            } else {
                $cat_b['all_num_percent'] = (round($cat_b['all_num'] / $products['total'], 3) * 100) . "%";
            }
            if (empty($products['amount'])) {
                $cat_b['all_amount_percent'] = 0;
            } else {
                $cat_b['all_amount_percent'] = (round($cat_b['all_amount'] / $products['amount'], 3) * 100) . "%";
            }
            $cat_b['all_list'] = $cat_list;
            $cat_b['all_model'] = $all_model;
            $productItem[] = $cat_b;
        }
        $info['all_list'] = $productItem;
        $info['total_list'] = $products;
        return $info;
    }


    /**
     * 获取该价格的价格带
     */
    public function getThisCostPrice()
    {
        return array(
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
    }

    /**
     * 根据价格带
     * @param $orderList
     * @return array
     */
    public function orderPriceTable($orderList)
    {
        $productModel = new Product();
        $listProducts = $productModel->productListCache();
        foreach ($listProducts as $list) {
            $all_list[$list['product_id']] = $list;
        }

        $big = $this->tableValue('cat_big', 'cat_name', 'big_id');
        $priceList = $this->getThisCostPrice();
        $totals = 0;
        $amount = 0;
        $model = array();
        foreach ($orderList['item_list'] as $v) {
            if (!isset($all_list[$v['product_id']])) continue;
            //购买的商品信息
            $productInfo = $all_list[$v['product_id']];

            $item[$productInfo['cat_b']]['cat_id'] = $productInfo['cat_b'];
            //大类名称
            $item[$productInfo['cat_b']]['cat_name'] = $big[$productInfo['cat_b']];

            //价格带的款数
            $price[$productInfo['cat_b'].$priceList[$productInfo['price_level_id']]]['model_sn'][] = $v['model_sn'];
            $price[$productInfo['cat_b'].$priceList[$productInfo['price_level_id']]]['model_count'] = count(array_unique($price[$productInfo['cat_b'].$priceList[$productInfo['price_level_id']]]['model_sn']));

            //大类总款数
            $item[$productInfo['cat_b']]['model_sn'][] = $v['model_sn'];

            //大类总数量
            if (!isset($item[$productInfo['cat_b']]['all_num'])) $item[$productInfo['cat_b']]['all_num'] = 0;
            $item[$productInfo['cat_b']]['all_num'] += $v['nums'];

            $totals += $v['nums'];
            $amount += $v['amount'];
            $model[] = $v['model_sn'];
            //大类总金额
            if (!isset($item[$productInfo['cat_b']]['all_amount'])) $item[$productInfo['cat_b']]['all_amount'] = 0;
            $item[$productInfo['cat_b']]['all_amount'] += $v['amount'];

            //此价格带的数量
            if (!isset($price[$productInfo['cat_b'].$priceList[$productInfo['price_level_id']]]['cat_nums'])) $price[$productInfo['cat_b'].$priceList[$productInfo['price_level_id']]]['cat_nums'] = 0;
            $price[$productInfo['cat_b'].$priceList[$productInfo['price_level_id']]]['cat_nums'] += $v['nums'];

            //此价格带的金额
            if (!isset($price[$productInfo['cat_b'].$priceList[$productInfo['price_level_id']]]['cat_amount'])) $price[$productInfo['cat_b'].$priceList[$productInfo['price_level_id']]]['cat_amount'] = 0;
            $price[$productInfo['cat_b'].$priceList[$productInfo['price_level_id']]]['cat_amount'] += $v['amount'];

            //此价格带的名称
            $price[$productInfo['cat_b'].$priceList[$productInfo['price_level_id']]]['cat_name'] = $priceList[$productInfo['price_level_id']];

            $item[$productInfo['cat_b']]['all_list'][$productInfo['cat_b'].$priceList[$productInfo['price_level_id']]] = $price[$productInfo['cat_b'].$priceList[$productInfo['price_level_id']]];
        }
        $total['total'] = $totals;
        $total['amount'] = $amount;
        $total['total_percent'] = "100%";
        $total['amount_percent'] = "100%";
        $total['model'] = count(array_unique($model));
        $total['model_percent'] = "100%";

        if (empty($item)) return array();

        foreach ($item as $val) {
            $val['all_model'] = count(array_unique($val['model_sn']));
            unset($val['model_sn']);
            $cat_list = array();
            foreach ($val['all_list'] as $info) {
                unset($info['model_sn']);
                if (empty($total['model'])) {
                    $info['cat_model_percent'] = 0;
                } else {
                    $info['cat_model_percent'] = (round($info['model_count'] / $total['model'], 3) * 100) . "%";
                }

                if (empty($total['amount'])) {
                    $info['cat_amount_percent'] = 0;
                } else {
                    $info['cat_amount_percent'] = (round($info['cat_amount'] / $total['amount'], 3) * 100) . "%";
                }

                if (empty($total['total'])) {
                    $info['cat_num_percent'] = 0;
                } else {
                    $info['cat_num_percent'] = (round($info['cat_nums'] / $total['total'], 3) * 100) . "%";
                }
                $cat_list[] = $info;
            }
            if (empty($total['total'])) {
                $val['all_num_percent'] = 0;
            } else {
                $val['all_num_percent'] = (round($val['all_num'] / $total['total'], 3) * 100) . "%";
            }
            if (empty($total['model'])) {
                $val['all_model_percent'] = 0;
            } else {
                $val['all_model_percent'] = (round($val['all_model'] / $total['model'], 3) * 100) . "%";
            }
            if (empty($total['amount'])) {
                $val['all_amount_percent'] = 0;
            } else {
                $val['all_amount_percent'] = (round($val['all_amount'] / $total['amount'], 3) * 100) . "%";
            }
            unset($val['all_list']);
            $val['all_list'] = $cat_list;
            $result[] = $val;
        }
        $results['all_list'] = $result;
        $results['total_list'] = $total;
        return $results;
    }

    /**
     * 检查此用户是否是提交了
     *
     * @param $purchase_id
     * @param $customer_id
     * @return string
     */
    public function checkThisSubmit($purchase_id, $customer_id)
    {
        $sql = "SELECT status FROM {{order}} WHERE customer_id='{$customer_id}' AND purchase_id='{$purchase_id}' AND disabled='false'";
        $res = $this->ModelQueryRow($sql);
        if (empty($res)) $res['status'] = "active";
        return $res['status'];
    }

    /**
     * 提交订单
     *
     * @param $purchase_id
     * @param $customer_id
     * @return mixed
     */
    public function submitOrder($purchase_id, $customer_id)
    {
        $this->ModelExecute("UPDATE meet_order SET status = 'confirm' WHERE purchase_id = {$purchase_id} AND customer_id = {$customer_id}");
        //更新订单缓存
        $order = new Order();
        $order->orderCache($purchase_id, $customer_id);
        return "confirm";
    }

    /**
     * 订单撤销
     *
     * @param $purchase_id
     * @param $customer_id
     * @return mixed
     */
    public function orderRepeal($purchase_id, $customer_id)
    {
        $order_row = $this->ModelQueryRow("SELECT status FROM meet_order WHERE purchase_id = {$purchase_id} AND customer_id = {$customer_id}");
        if ($order_row['status'] != 'confirm') {
            return $order_row['status'];
        } else {
            $this->ModelExecute("UPDATE meet_order SET status = 'active' WHERE purchase_id = {$purchase_id} AND customer_id = {$customer_id}");
            //更新订单缓存
            $order = new Order();
            $order->orderCache($purchase_id, $customer_id);
            return "active";
        }
    }
}