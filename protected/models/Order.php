<?php

/**
 * 订单模型类.
 *
 * @author        chenfenghua <843958575@qq.com>
 * @copyright     Copyright (c) 2007-2014 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       mall.model
 * @license       http://www.octmami.com/license
 * @version       v1.0.0
 */
class Order extends B2cModel
{
    /**
     * 订单生成
     *
     * @param $product
     * @param $purchase_id
     * @param $customer_id
     * @param $customer_name
     * @return bool
     */
    public function Add($product, $purchase_id, $customer_id, $customer_name)
    {
        $create_time = time();

        //会员订单是否生成
        $order_list = $this->orderItems($purchase_id, $customer_id);
        //订单order_id获取
        if (isset($order_list['order_row']) && $order_list['order_row']) $order_id = $order_list['order_row']['order_id'];
        else $order_id = $this->build_order_no();

        //订单商品列表
        $item_list = $this->itemList($product, $order_id, $order_list['item_list']);

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
            $this->orderCache($purchase_id, $customer_id);
            return true;
        } catch (Exception $e) {
            $transaction->rollback();
            return false;
        }
    }


    public function AddAjax($product, $purchase_id, $customer_id, $customer_name)
    {
        $create_time = time();
        //如果该会员已经生成订单，在原来的订单上添加订购的商品
        //会员订单是否生成
        $order_list = $this->orderItems($purchase_id, $customer_id);//已经订购的产品
        //订单order_id获取
        if (isset($order_list['order_row']) && $order_list['order_row']) $order_id = $order_list['order_row']['order_id'];
        else $order_id = $this->build_order_no();

        //订单商品列表
        $item_list = $this->itemListAjax($product, $order_id, $order_list['item_list']);

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
            $this->orderCache($purchase_id, $customer_id);
            return true;
        } catch (Exception $e) {
            $transaction->rollback();
            return false;
        }
    }

    /**
     * 订单商品列表
     *
     * @param $product
     * @param $order_id
     * @param $item_list
     * @return string
     */
    public function itemList($product, $order_id, $item_list)
    {
        $productModel = new Product();
        $product_list = $productModel->listcacheId();
        $sql = '';
        $order_product_list = isset($item_list) && $item_list ? $item_list : array();//已定单 数量等
        foreach ($product as $k => $v) {
            $num = isset($v[1]) ? $v[1] : '';
            if ($num == 0) {
                $num = '';
            }
            if ($num == '') {
                if (isset($product_list[$v[0]]) && $product_list[$v[0]])
                    $delete_data_arr[] = "UPDATE  meet_order_items SET disabled='true' WHERE order_id = {$order_id} AND product_id = '" . $v[0] . "' AND disabled='false'";
                continue;
            }
            $product_sn = $product_list[$v[0]]['product_sn'];
            $style_sn = $product_list[$v[0]]['style_sn'];
            $model_sn = $product_list[$v[0]]['model_sn'];
            $name = $product_list[$v[0]]['name'];
            $price = $product_list[$v[0]]['cost_price'];
            $amount = sprintf('%.2f', $price * $num);
            if (isset($order_product_list[$v[0]]) && $order_product_list[$v[0]]) {
                $update_data_arr[] = "UPDATE meet_order_items SET price = '{$price}',amount = '{$amount}',nums = {$num} WHERE order_id = {$order_id} AND product_id = {$v[0]}  AND disabled='false'";
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


    public function itemListAjax($product, $order_id, $item_list)
    {
        $productModel = new Product();
        $product_list = $productModel->listcacheId();
        $sql = '';
        $order_product_list = isset($item_list) && $item_list ? $item_list : array();//已定单 数量等
        foreach ($product as $k => $v) {
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
var_dump($product_list);exit;
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
     * 订单缓存
     *
     * @param $purchase_id
     * @param $customer_id
     * @return mixed
     */
    public function orderCache($purchase_id, $customer_id)
    {
        $model['order_row'] = $this->ModelQueryRow("SELECT * FROM meet_order WHERE purchase_id = {$purchase_id} AND customer_id = {$customer_id} AND disabled='false'");
        if (!isset($model['order_row']) || !$model['order_row']) return array('order_row' => array(), 'item_list' => array());

        $item_list = $this->ModelQueryAll("SELECT * FROM meet_order_items WHERE order_id = {$model['order_row']['order_id']} AND disabled='false'");
        $model['item_list'] = array();
        $total_num = 0;
        $cost_item = 0.00;
        //这里没必要加更新
        if (!$item_list) {
            $this->ModelExecute("UPDATE meet_order SET cost_item = 0.00 WHERE purchase_id = {$purchase_id} AND customer_id = {$customer_id}");
            $model['order_row']['cost_item'] = $cost_item;
            $model['order_row']['total_num'] = $total_num;
            return array('order_row' => $model['order_row'], 'item_list' => array());
        }
        $res = $this->getThisProductIsDown();
        foreach ($item_list as $v) {
            $model['item_list'][$v['product_id']] = $v;
            $model['item_list'][$v['product_id']]['is_down'] = isset($res[$v['product_id']]) ? $res[$v['product_id']] : 0;
            $total_num += $v['nums'];
            $cost_item += $v['nums'] * $v['price'];
        }
        //更新订单总价
        $this->ModelExecute("UPDATE meet_order SET cost_item = {$cost_item} WHERE purchase_id = {$purchase_id} AND customer_id = {$customer_id}");
        $model['order_row']['cost_item'] = $cost_item;
        $model['order_row']['total_num'] = $total_num;
        return $model;
    }

    /**
     * 根据product_id 转 是否下架
     *
     * @return mixed
     */
    public function getThisProductIsDown(){
        $result = Yii::app()->cache->get("product_list_is_down_".Yii::app()->session['purchase_id']);
        if(!$result){
            $product = new Product();
            $res = $product->productListCache();
            foreach($res as $val){
                $result[$val['product_id']] = $val['is_down'];
            }
            Yii::app()->cache->set("product_list_is_down_".Yii::app()->session['purchase_id'], $result, 86400);
        }
        return $result;
    }

    /**
     * 删除商品  作废
     *
     * @param $order_id
     * @param $model_sn
     * @param $purchase_id
     * @param $customer_id
     * @return bool
     */
    public function deleteItems($order_id, $model_sn, $purchase_id, $customer_id)
    {
        die;
        $item_list = $this->ModelQueryAll("SELECT * FROM meet_order_items WHERE order_id = {$order_id} AND model_sn = '{$model_sn}'");
        $amount = 0.00;
        foreach ($item_list as $v) {
            $amount += $v['nums'] * $v['price'];
        }
        $time = time();
        //事务处理
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $this->ModelExecute("DELETE FROM {{order_items}} WHERE order_id = {$order_id} AND model_sn = '{$model_sn}'");
            $this->ModelExecute("UPDATE meet_order SET cost_item = cost_item - {$amount}, edit_time = {$time} WHERE order_id = {$order_id}");
            $transaction->commit();
            //更新订单缓存
            $this->orderCache($purchase_id, $customer_id);
            return true;
        } catch (Exception $e) {
            $transaction->rollback();
            return false;
        }
    }

    /**
     * 订单详情
     *
     * @param $purchase_id
     * @param $customer_id
     * @return mixed
     */
    public function orderItems($purchase_id, $customer_id)
    {
        $cache_name = 'order-items-' . $purchase_id . '_' . $customer_id;
        $model = Yii::app()->cache->get($cache_name);
        if (!$model) {
            $model = $this->orderCache($purchase_id, $customer_id);
        } else {
            $order_row = $this->ModelQueryRow("SELECT a.status,b.is_down FROM {{order}} AS a LEFT JOIN {{product}} AS b ON a.product_id=b.product_id WHERE a.purchase_id = {$purchase_id} AND customer_id = {$customer_id}");
            if ($order_row['status'] != $model['order_row']['status']) {
                $model = $this->orderCache($purchase_id, $customer_id);
            }
        }
        return $model;
    }

    /**
     * 商品列表
     *
     * @param $order_id
     * @param $page
     * @internal param $purchase_id
     * @internal param $customer_id
     * @return array
     */
    public function orderItemItem($order_id, $page)
    {
        $start = ($page - 1) * 8;
        $item_list = $this->ModelQueryAll("SELECT model_sn FROM meet_order_items WHERE  disabled='false'  AND  order_id = {$order_id} ORDER BY item_id DESC");

        $model_sn_items = array();
        foreach ($item_list as $v) {
            $model_sn_items[$v['model_sn']] = $v['model_sn'];
        }
        $model_sn = array_keys($model_sn_items);
        $model_sn = array_slice($model_sn, $start, 8);
        return $model_sn;
    }

    /**
     * 商品列表
     *
     * @param $purchase_id
     * @param $customer_id
     * @param $page
     * @internal $page
     * @return array
     */
    public function orderItemList($purchase_id, $customer_id, $page)
    {
        $model['order_row'] = $this->ModelQueryRow("SELECT order_id FROM meet_order WHERE purchase_id = {$purchase_id} AND customer_id = {$customer_id}");

        if (!isset($model['order_row']) || !$model['order_row']) return array('order_row' => array(), 'item_list' => array());
        $model_sn = $this->orderItemItem($model['order_row']['order_id'], $page);

        if (!$model_sn) return array('order_row' => $model['order_row'], 'item_list' => array());
        $model_sn_str = implode(',', $model_sn);
        $item_list = $this->ModelQueryAll("SELECT * FROM meet_order_items WHERE order_id = {$model['order_row']['order_id']}   AND disabled='false' AND model_sn IN ($model_sn_str) order by field(model_sn,$model_sn_str) ASC");

        $res = $this->getThisProductTrans($purchase_id);
        foreach ($item_list as $v) {
            $model['item_list'][$v['product_id']] = $v;
            $model['item_list'][$v['product_id']]['is_down'] = isset($res[$v['product_id']]['is_down']) ? $res[$v['product_id']]['is_down'] : 0;
            $model['item_list'][$v['product_id']]['type_id'] = isset($res[$v['product_id']]['type_id']) ? $res[$v['product_id']]['type_id'] : '';
            $model['item_list'][$v['product_id']]['wave_id'] = isset($res[$v['product_id']]['wave_id']) ? $res[$v['product_id']]['wave_id'] : '' ;
            $model['item_list'][$v['product_id']]['img_url'] = isset($res[$v['product_id']]['img_url']) ? $res[$v['product_id']]['img_url'] : '';
            $model['item_list'][$v['product_id']]['name'] = isset($res[$v['product_id']]['name']) ? $res[$v['product_id']]['name'] : '';
            $model['item_list'][$v['product_id']]['memo'] = isset($res[$v['product_id']]['memo']) ? $res[$v['product_id']]['memo'] : '';
            $model['item_list'][$v['product_id']]['product_sn'] = isset($res[$v['product_id']]['product_sn']) ? $res[$v['product_id']]['product_sn'] : '';
            $model['item_list'][$v['product_id']]['product_id'] = isset($res[$v['product_id']]['product_id']) ? $res[$v['product_id']]['product_id'] : '';
            $model['item_list'][$v['product_id']]['cost_price'] = isset($res[$v['product_id']]['cost_price']) ? $res[$v['product_id']]['cost_price'] : '';
            $model['item_list'][$v['product_id']]['serial_num'] = isset($res[$v['product_id']]['serial_num']) ? $res[$v['product_id']]['serial_num'] : '';
        }
        return $model;
    }

    public function getThisProductTrans($purchase_id){
        $res = Yii::app()->cache->get("getThisProductTrans_".$purchase_id);
        if(!$res){
            $sql = "SELECT * FROM {{product}} WHERE disabled='false' AND purchase_id='{$purchase_id}'";
            $product_info = $this->ModelQueryAll($sql);
            foreach($product_info as $val){
                $res[$val['product_id']] = $val;
            }
            Yii::app()->cache->set("getThisProductTrans_".$purchase_id, $res, 86400);
        }
        return $res;
    }

    /**
     * 订单提交
     *
     * @param $purchase_id
     * @param $customer_id
     * @return mixed
     */
    public function orderSubmit($purchase_id, $customer_id)
    {
        $result = $this->ModelExecute("UPDATE meet_order SET status = 'confirm' WHERE purchase_id = {$purchase_id} AND customer_id = {$customer_id}");
        //更新订单缓存
        $this->orderCache($purchase_id, $customer_id);

        return $result;
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
        if ($order_row['status'] != 'confirm') return false;
        $result = $this->ModelExecute("UPDATE meet_order SET status = 'active' WHERE purchase_id = {$purchase_id} AND customer_id = {$customer_id}");
        //更新订单缓存
        $this->orderCache($purchase_id, $customer_id);

        return $result;
    }

    /**
     * 订单撤销
     *
     * @param $purchase_id
     * @param $customer_id
     * @return mixed
     */
    public function orderRepealCheck($purchase_id, $customer_id)
    {
        $order_row = $this->ModelQueryRow("SELECT status FROM meet_order WHERE purchase_id = {$purchase_id} AND customer_id = {$customer_id}");
        if ($order_row['status'] != 'confirm') return false;

        return true;
    }

    /**
     * 订单号生成
     */
    public function build_order_no()
    {
        return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

    /**
     * 获取下线订单ID
     *
     * @param $customer_id
     * @return bool|mixed
     */
    public function getUserDownUsers($customer_id)
    {
        $customer_info = $this->ModelQueryRow('SELECT code FROM `meet_customer` WHERE customer_id=\''.$customer_id.'\'');
        if(!$customer_info) return false;
        $result = $this->ModelQueryAll('SELECT c.name, c.code, c.target, o.cost_item, c.customer_id, c.purchase_id FROM `meet_customer` AS c LEFT JOIN `meet_order` AS o ON c.customer_id=o.customer_id WHERE c.parent_id=\'0\' AND c.agent=\''.$customer_info['code'].'\' AND o.disabled=\'false\' AND c.disabled=\'false\'');
        if(!$result) return array();
        else{
            foreach($result as &$v)
            {
                $v['percent'] = $v['target'] == 0 ?  '0%'  : round($v['cost_item'] / $v['target'] * 100 , 2). '%';
                $v['left_cost'] = $v['target'] > $v['cost_item'] ? $v['target'] - $v['cost_item'] : 0;
            }
            return $result;
        }
    }

    /**
     * 获取用户的基本信息
     *
     * @param $customer_id
     * @return mixed
     */
    public function userBaseInfo($customer_id)
    {
        return $this->ModelQueryRow('SELECT `code`,`name` FROM {{customer}} WHERE customer_id=\''.$customer_id.'\'');
    }
} 