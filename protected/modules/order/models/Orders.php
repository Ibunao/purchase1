<?php

/**
 * "sdb_b2c_orders" 数据表模型类.
 *
 * @author        chenfenghua <843958575@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       mall.model
 * @license       http://www.octmami.com/license
 * @version       v1.0.0
 *
 */
class Orders extends BaseModel
{
    public $pay_status_name;
    public $ship_status_name;
    public $member_name;

    /**
     * @return string 相关的数据库表的名称
     */
    public function tableName()
    {
        return '{{order}}';
    }


    /**
     * @return array 对模型的属性验证规则.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }


    /**
     * 返回指定的AR类的静态模型.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Admin the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }


    public function orderList($pageIndex, $pagesize, $params = array())
    {

        if (!empty($params['order'])) {
            $order_by = $params['order'];
        } else {
            $order_by = ' p.serial_num asc';
        }

        $where = '';
        $left = '';
        $select = '';
        if (!isset($params['purchase'])) {
            echo "ding";
        }
        echo isset($params['purchase']);
        var_dump($params);exit;
        if (!empty($params['purchase']) && !empty($params['type'])) {
            $where .= " and c.type= '" . $params['type'] . "' and c.purchase_id= '" . $params['purchase'] . "' ";
            $select .= ", o.purchase_id,o.customer_id, c.`type`";
            $left .= " left join meet_order as o on o.order_id = oi.order_id
                 left join meet_customer as c on c.customer_id = o.customer_id ";
        } else {

            if (!empty($params['purchase']) && empty($params['type'])) {
                $where .= " and c.purchase_id= '" . $params['purchase'] . "' ";
                $select .= ", o.purchase_id,o.customer_id, c.`type`";
                $left .= " left join meet_order as o on o.order_id = oi.order_id
                 left join meet_customer as c on c.customer_id = o.customer_id ";
            }
            //判断顾客类型
            if (!empty($params['type']) && empty($params['purchase'])) {
                $where .= " and c.type= '" . $params['type'] . "' ";
                $select .= " ,c.`type`";
                $left .= " left join meet_order as o on o.order_id = oi.order_id
                 left join meet_customer as c on c.customer_id = o.customer_id ";
            }
        }

        if (!empty($params['style_sn'])) {
            $where .= " and p.style_sn= '" . $params['style_sn'] . "' ";
        }

        if (!empty($params['cat_big'])) {
            $where .= " and p.cat_b= '" . $params['cat_big'] . "' ";
            $select .= ',cb.cat_name as cat_big_name';
            $left .= ' left join meet_cat_big as cb on cb.big_id = p.cat_b';
        }

        if (!empty($params['cat_middle'])) {
            $where .= " and p.cat_m= '" . $params['cat_middle'] . "' ";
            $select .= ',cm.cat_name as cat_middle_name';
            $left .= ' left join meet_cat_middle as cm on cm.middle_id = p.cat_m';
        }

        //修复bug 2017、3、16
        if (!empty($params['cat_small'])) {
            if(empty($params['cat_big'])){
                $where .= " and p.cat_s= '" . $params['cat_small'] . "' ";
                $select .= ',cs.small_cat_name as cat_small_name';
                $left .= ' left join meet_cat_big_small as cs on cs.small_id = p.cat_s';
            }else{
                $where .= " and p.cat_s= '" . $params['cat_small'] . "' AND cs.big_id = '".$params['cat_big']."' ";
                $select .= ',cs.small_cat_name as cat_small_name';
                $left .= ' left join meet_cat_big_small as cs on cs.small_id = p.cat_s';
            }
        }

        if (!empty($params['season'])) {
            $where .= " and p.season_id= '" . $params['season'] . "' ";
        }


        if (!empty($params['level'])) {
            $where .= " and p.level_id= '" . $params['level'] . "' ";
        }

        if (!empty($params['wave'])) {
            $where .= " and p.wave_id= '" . $params['wave'] . "' ";
        }

        if (!empty($params['scheme'])) {
            $where .= " and p.scheme_id= '" . $params['scheme'] . "' ";
        }

        if (!empty($params['price_level_id'])) {
            $where .= " and p.price_level_id= '" . $params['price_level_id'] . "' ";
        }

        if(!empty($params['ptype'])){
            $where .= " and p.type_id = '" . $params['ptype'] . "' ";
        }

        if (!empty($params['name'])) {
            $where .= " and c.name like '%" . $params['name'] . "%' ";
        }

        if (empty($params['download'])) {
            $sql = "select sum(oi.nums)as nums, sum(oi.amount) as amount ,
p.name,p.cost_price,p.style_sn,p.product_id,p.img_url,p.serial_num,
p.cat_b,p.cat_m,p.cat_s,s.size_name,tp.type_name  $select
from meet_order_items  as oi
left join meet_product as p on p.product_id = oi.product_id
left join meet_size as s on s.size_id = p.size_id
left join meet_type as tp on p.type_id = tp.type_id
$left
where oi.disabled ='false' $where
GROUP BY oi.style_sn ORDER BY $order_by ";

        } else {

            $sql = "select sum(oi.nums)as nums, sum(oi.amount) as amount ,
p.name,p.cost_price,p.style_sn,p.product_id,p.product_sn,p.img_url,p.serial_num,tp.type_name,
p.cat_b,p.cat_m,p.cat_s,s.size_name  $select
from meet_order_items  as oi
left join meet_product as p on p.product_id = oi.product_id
left join meet_size as s on s.size_id = p.size_id
left join meet_type as tp on p.type_id = tp.type_id
$left
where oi.disabled ='false' $where
GROUP BY oi.product_id ORDER BY $order_by ";

        }
//        echo $sql;die();
        $count = count($this->QueryAll($sql));

        //判定是否是下载模式
        if (empty($params['download'])) {
            $limit = $pagesize;
            $offset = ($pageIndex - 1) * $pagesize;

            $sql .= " limit $offset,$limit ";
        }
        var_dump($sql);exit;
        $list = $this->QueryAll($sql);

        return array('item' => $list, 'count' => $count);
    }


    //订单数量汇总: 订单金额汇总:
    public function getOrderAmount($product_id, $params)
    {
        if (empty($product_id)) {
            return false;
        }
        $where = '';
        $left = '';
        $select = '';
        if (!empty($params['purchase']) && !empty($params['type'])) {
            $where .= " and c.type= '" . $params['type'] . "' and c.purchase_id= '" . $params['purchase'] . "' ";
            $select .= ", o.purchase_id,o.customer_id, c.`type`";
            $left .= " left join meet_order as o on o.order_id = oi.order_id
                 left join meet_customer as c on c.customer_id = o.customer_id ";
        } else {

            if (!empty($params['purchase']) && empty($params['type'])) {
                $where .= " and c.purchase_id= '" . $params['purchase'] . "' ";
                $select .= ", o.purchase_id,o.customer_id, c.`type`";
                $left .= " left join meet_order as o on o.order_id = oi.order_id
                 left join meet_customer as c on c.customer_id = o.customer_id ";
            }
            //判断顾客类型
            if (!empty($params['type']) && empty($params['purchase'])) {
                $where .= " and c.type= '" . $params['type'] . "' ";
                $select .= " ,c.`type`";
                $left .= " left join meet_order as o on o.order_id = oi.order_id
                 left join meet_customer as c on c.customer_id = o.customer_id ";
            }
        }

        if (!empty($params['style_sn'])) {
            $where .= " and p.style_sn= '" . $params['style_sn'] . "' ";
        }
        if (!empty($params['cat_big'])) {
            $where .= " and p.cat_b= '" . $params['cat_big'] . "' ";
            $select .= ',cb.cat_name as cat_big_name';
            $left .= ' left join meet_cat_big as cb on cb.big_id = p.cat_b';
        }
        if (!empty($params['cat_middle'])) {
            $where .= " and p.cat_m= '" . $params['cat_middle'] . "' ";
            $select .= ',cm.cat_name as cat_middle_name';
            $left .= ' left join meet_cat_middle as cm on cm.middle_id = p.cat_m';
        }
        if (!empty($params['cat_small'])) {
            $where .= " and p.cat_s= '" . $params['cat_small'] . "' ";
            $select .= ',cs.cat_name as cat_small_name';
            $left .= ' left join meet_cat_small as cs on cs.small_id = p.cat_s';
        }

        if (!empty($params['season'])) {
            $where .= " and p.season_id= '" . $params['season'] . "' ";
        }

        if (!empty($params['level'])) {
            $where .= " and p.level_id= '" . $params['level'] . "' ";
        }

        if (!empty($params['wave'])) {
            $where .= " and p.wave_id= '" . $params['wave'] . "' ";
        }

        if (!empty($params['scheme'])) {
            $where .= " and p.scheme_id= '" . $params['scheme'] . "' ";
        }

        if (!empty($params['price_level_id'])) {
            $where .= " and p.price_level_id= '" . $params['price_level_id'] . "' ";
        }

        if (!empty($params['ptype'])) {
            $where .= " and p.type_id= '" . $params['ptype'] . "' ";
        }

        if (!empty($params['name'])) {
            $where .= " and c.name like '%" . $params['name'] . "%' ";
        }

        $sql = "select sum(oi.nums)as nums, sum(oi.amount) as amount  $select
from meet_order_items  as oi
left join meet_product as p on p.product_id = oi.product_id
$left
where oi.disabled ='false' $where  ";

        $result = $this->QueryRow($sql);
        if(Yii::app()->params['is_latest_price']){
            $result['amount'] = $this->getProductListCount($params);
        }
//        var_dump($result);die();

//        $order_amount['amount'] = 0;

//        if(!empty($result)){
//
//
//            foreach($result as $v){
//                $order_id[] = $v['order_id'];
//                $order_amount['amount'] += $v['amount'];
//            }
//        }

//        $order_amount['nums'] = count( array_unique($order_id));
        return $result;
    }

    public function getProductListCount($params){
        $where = '';
        $left = '';
        if (!empty($params['purchase']) && !empty($params['type'])) {
            $where .= " and c.type= '" . $params['type'] . "' and c.purchase_id= '" . $params['purchase'] . "' ";
            $left .= " left join meet_order as o on o.order_id = oi.order_id
                 left join meet_customer as c on c.customer_id = o.customer_id ";
        } else {
            if (!empty($params['purchase']) && empty($params['type'])) {
                $where .= " and c.purchase_id= '" . $params['purchase'] . "' ";
                $left .= " left join meet_order as o on o.order_id = oi.order_id
                 left join meet_customer as c on c.customer_id = o.customer_id ";
            }
            //判断顾客类型
            if (!empty($params['type']) && empty($params['purchase'])) {
                $where .= " and c.type= '" . $params['type'] . "' ";
                $left .= " left join meet_order as o on o.order_id = oi.order_id
                 left join meet_customer as c on c.customer_id = o.customer_id ";
            }
        }

        if (!empty($params['style_sn'])) {
            $where .= " and p.style_sn= '" . $params['style_sn'] . "' ";
        }
        if (!empty($params['cat_big'])) {
            $where .= " and p.cat_b= '" . $params['cat_big'] . "' ";
            $left .= ' left join meet_cat_big as cb on cb.big_id = p.cat_b';
        }
        if (!empty($params['cat_middle'])) {
            $where .= " and p.cat_m= '" . $params['cat_middle'] . "' ";
            $left .= ' left join meet_cat_middle as cm on cm.middle_id = p.cat_m';
        }
        if (!empty($params['cat_small'])) {
            $where .= " and p.cat_s= '" . $params['cat_small'] . "' ";
            $left .= ' left join meet_cat_small as cs on cs.small_id = p.cat_s';
        }

        if (!empty($params['season'])) {
            $where .= " and p.season_id= '" . $params['season'] . "' ";
        }


        if (!empty($params['level'])) {
            $where .= " and p.level_id= '" . $params['level'] . "' ";
        }

        if (!empty($params['wave'])) {
            $where .= " and p.wave_id= '" . $params['wave'] . "' ";
        }

        if (!empty($params['scheme'])) {
            $where .= " and p.scheme_id= '" . $params['scheme'] . "' ";
        }

        if (!empty($params['price_level_id'])) {
            $where .= " and p.price_level_id= '" . $params['price_level_id'] . "' ";
        }

        if (!empty($params['ptype'])) {
            $where .= " and p.type_id= '" . $params['ptype'] . "' ";
        }

        if (!empty($params['name'])) {
            $where .= " and c.name like '%" . $params['name'] . "%' ";
        }
        $sql = "SELECT oi.nums,p.cost_price FROM {{order_items}} AS oi LEFT JOIN {{product}} AS p ON p.product_id=oi.product_id
            $left
            where oi.disabled ='false' AND p.disabled='false' $where";
        $result = $this->QueryAll($sql);
        $counts = 0;
        foreach($result as $val){
            $counts += $val['nums'] * $val['cost_price'];
        }
        return $counts;
    }

    //根据商品查找订单数量
    public function customerOrderByStyleSnCount($style_sn, $params = array())
    {
        $where = '';
        //判断顾客类型
        if (!empty($params['type'])) {
            $where .= " and c.type= '" . $params['type'] . "' ";
        }

        $sql = "select sum(oi.nums) as count,c.type FROM meet_order as o
left join meet_customer as c  on c.customer_id = o.customer_id
left join meet_order_items as oi on oi.order_id = o.order_id
where  oi.style_sn = '" . $style_sn . "' and oi.disabled = 'false' $where group by c.type";
        $result = $this->QueryAll($sql);
        $return['self'] = '0';
        $return['customer'] = '0';
        if ($result) {

            foreach ($result as $v) {
                if ($v['type'] == '直营') {
                    $return['self'] = $v['count'];
                } else if ($v['type'] == '客户') {
                    $return['customer'] = $v['count'];
                }
            }
            return $return;
        } else {
            return false;
        }
    }

//根据商品查找订单数量
    public function customerOrderByProductIdCount($product_id, $params = array())
    {
        $where = '';
        //判断顾客类型
        if (!empty($params['type'])) {
            $where .= " and c.type= '" . $params['type'] . "' ";
        }

        $sql = "select sum(oi.nums) as count,c.type    FROM meet_order as o
left join meet_customer as c  on c.customer_id = o.customer_id
left join meet_order_items as oi on oi.order_id = o.order_id
where  oi.product_id = '" . $product_id . "' and oi.disabled = 'false' $where group by c.type";

        $result = $this->QueryAll($sql);

        $return['self'] = '0';
        $return['customer'] = '0';
        if ($result) {
            foreach ($result as $v) {
                if ($v['type'] == '直营') {
                    $return['self'] = $v['count'];
                } else if ($v['type'] == '客户') {
                    $return['customer'] = $v['count'];
                }
            }
            return $return;
        } else {
            return false;
        }
    }

    /************************************************************************************************************
     *
     */

    public function orderCheckList($pageIndex, $pagesize, $params = array())
    {
        if (!empty($params['order'])) {
            $order_by = $params['order'];
        } else {
            $order_by = ' o.cost_item ';
        }

        $where = '';

        if (!empty($params['purchase'])) {
            $where .= " and c.purchase_id= '" . $params['purchase'] . "' ";
        }
        if (!empty($params['department'])) {
            $where .= " and c.department= '" . $params['department'] . "' ";
        }
        if(!empty($params['status'])){
            $where .= " and o.status= '" . $params['status'] . "' ";
        }
        if (!empty($params['leader'])) {
            $where .= " and c.leader= '" . $params['leader'] . "' ";
        }
        if (!empty($params['name'])) {
            $where .= " and  c.name like '%" . $params['name'] . "%' ";
        }
        if (!empty($params['leader_name'])) {
            $where .= " and  (  c.agent like '%" . $params['leader_name'] . "%' or c.leader_name like  '%" . $params['leader_name'] . "%' )  ";
        }
        if(!empty($params['code'])){
            $where .= " and c.code='{$params['code']}'";
        }
        //判断顾客类型
        if (!empty($params['type'])) {
            $where .= " and c.type= '" . $params['type'] . "' ";
        }
        if (!empty($params['area'])) {
            $where .= ' and c.area= "' . $params['area'] . '"';
        }
        if (!empty($params['login'])) {
            if ($params['login'] == 1) {
                $where .= ' and c.login is not null';
            } elseif ($params['login'] == 2) {
                $where .= ' and c.login is null';
            }
        }
        $sql = "select c.code,c.agent,c.customer_id,c.`name` as customer_name,c.`type`,c.purchase_id,c.province,c.area,c.target,o.order_id,o.`status`,o.cost_item,o.create_time,
        o.cost_item/c.target  as rate,c.parent_id,o.cost_item as count_all
        from  meet_customer as c
        left join  meet_order as o  on c.customer_id= o.customer_id
        where o.disabled='false' $where order by $order_by desc";
        $count = count($this->QueryAll($sql));
        //判断是否是下载状态
        if (empty($params['download'])) {
            $limit = $pagesize;
            $offset = ($pageIndex - 1) * $pagesize;

            $sql .= " limit $offset,$limit ";
        }
        $list = $this->QueryAll($sql);
        foreach($list as $key => $val){
            //只查最新的价格
            $check = $this->getCustomerNewCount($val['order_id'], true);
            $list[$key]['cost_item'] = $this->getCustomerNewCount($val['order_id']);
            $list[$key]['is_diff'] = false;
            if($check != $val['count_all']){
                $list[$key]['is_diff'] = true;
            }
        }
        return array('item' => $list, 'count' => $count);
    }


    public function orderCheckListAmount($params = array())
    {
        if (!empty($params['order'])) {
            $order_by = $params['order'];
        } else {
            $order_by = ' o.cost_item ';
        }

        $where = '';

        if (!empty($params['purchase'])) {
            $where .= " and c.purchase_id= '" . $params['purchase'] . "' ";
        }
        if (!empty($params['department'])) {
            $where .= " and c.department= '" . $params['department'] . "' ";
        }

        if (!empty($params['leader'])) {
            $where .= " and c.leader= '" . $params['leader'] . "' ";
        }
        if (!empty($params['name'])) {
            $where .= " and  c.name like '%" . $params['name'] . "%'";
        }
        if(!empty($params['status'])){
            $where .= " and o.status= '" . $params['status'] . "' ";
        }
        if (!empty($params['leader_name'])) {
            $where .= " and ( c.agent like '%" . $params['leader_name'] . "%' or c.leader_name like  '%" . $params['leader_name'] . "%' )  ";
        }
        if(!empty($params['code'])){
            $where .= " and c.code='{$params['code']}'";
        }
        //判断顾客类型
        if (!empty($params['type'])) {
            $where .= " and c.type= '" . $params['type'] . "' ";
        }
        if (!empty($params['area'])) {
            $where .= ' and c.area= "' . $params['area'] . '"';
        }
        if (!empty($params['login'])) {
            if ($params['login'] == 1) {
                $where .= ' and c.login is not null';
            } elseif ($params['login'] == 2) {
                $where .= ' and c.login is null';
            }
        }
        $sql = "select c.code,c.agent,c.customer_id,c.`name` as customer_name,c.`type`,c.purchase_id,c.province,c.area,c.target,o.order_id,o.`status`,o.cost_item,o.create_time,
        o.cost_item/c.target  as rate
        from  meet_customer as c
        left join  meet_order as o  on c.customer_id= o.customer_id
        where o.disabled='false' and o.cost_item>0  $where order by $order_by desc";
        $list = $this->QueryAll($sql);
        foreach($list as $key => $val){
            $list[$key]['cost_item'] = $this->getCustomerNewCount($val['order_id']);
        }
        return array('item' => $list);
    }


    /**
     * 获得已定货的金额
     * @param string $data
     * @return int
     */
    public function getAlrealdyOrderedCostPrice($data = '')
    {
        $sql = "SELECT a.cost_item FROM {{order}}  AS a LEFT JOIN `meet_customer` AS b ON a.customer_id=b.customer_id WHERE b.agent='" . $data . "'";
        $res = $this->queryAll($sql);
        $s = 0;
        foreach ($res as $v) {
            $s = $v['cost_item'] + $s;
        }
        return $s;
    }

    public function orderCheckListAmountReally($params = array())
    {
        if (!empty($params['order'])) {
            $order_by = $params['order'];
        } else {
            $order_by = ' o.cost_item ';
        }

        $where = " and o.status='finish'";

        if (!empty($params['purchase'])) {
            $where .= " and c.purchase_id= '" . $params['purchase'] . "' ";
        }
        if (!empty($params['department'])) {
            $where .= " and c.department= '" . $params['department'] . "' ";
        }
        if(!empty($params['status'])){
            $where .= " and o.status= '" . $params['status'] . "' ";
        }
        if (!empty($params['leader'])) {
            $where .= " and c.leader= '" . $params['leader'] . "' ";
        }
        if (!empty($params['name'])) {
            $where .= " and  c.name like '%" . $params['name'] . "%'";
        }
        if(!empty($params['code'])){
            $where .= " and c.code='{$params['code']}'";
        }
        if (!empty($params['leader_name'])) {
            $where .= " and  ( c.agent like '%" . $params['leader_name'] . "%' or c.leader_name like  '%" . $params['leader_name'] . "%' )  ";
        }
        //判断顾客类型
        if (!empty($params['type'])) {
            $where .= " and c.type= '" . $params['type'] . "' ";
        }
        if (!empty($params['area'])) {
            $where .= ' and c.area= "' . $params['area'] . '"';
        }
        if (!empty($params['login'])) {
            if ($params['login'] == 1) {
                $where .= ' and c.login is not null';
            } elseif ($params['login'] == 2) {
                $where .= ' and c.login is null';
            }
        }
        $sql = "select c.code,c.agent,c.customer_id,c.`name` as customer_name,c.`type`,c.purchase_id,c.province,c.area,c.target,o.order_id,o.`status`,o.cost_item,o.create_time,
        o.cost_item/c.target  as rate
        from  meet_customer as c
        left join  meet_order as o  on c.customer_id= o.customer_id
        where o.disabled='false' and o.cost_item>0   $where order by $order_by desc";
        $list = $this->QueryAll($sql);
        foreach($list as $key => $val){
            $list[$key]['cost_item'] = $this->getCustomerNewCount($val['order_id']);
        }
        return array('item' => $list);
    }

    public function orderInfo($order_id)
    {
        $sql = "select c.customer_id,c.code,c.`name` as customer_name,c.`type`,c.province,c.area,c.target,o.order_id,o.`status`,
        o.cost_item,o.create_time,mp.purchase_name,c.purchase_id,c.big_1,c.big_2,c.big_3,c.big_4,c.big_6,c.big_1_count,c.big_2_count,c.big_3_count,c.big_4_count,c.big_6_count
from  meet_customer as c left join  meet_order as o  on c.customer_id= o.customer_id
left join meet_purchase as mp  on mp.purchase_id = o.purchase_id
where o.disabled='false' and order_id = '" . $order_id . "' order by cost_item desc";
        $res = $this->QueryRow($sql);
        if ($res) {
            $sql = "select sum(nums) as nums from meet_order_items where order_id='" . $order_id . "' and disabled = 'false'";
            $items = $this->QueryRow($sql);
            $res['nums'] = $items['nums'];
        }
        $res['cost_item'] = $this->getCustomerNewCount($order_id);
        return $res;
    }

    public function orderProductStyleSn($order_id)
    {

        if (empty($order_id)) {
            return array();
        }

        $sql = " select style_sn from meet_order_items where order_id='" . $order_id . "' and disabled='false' group by style_sn ";
        return $this->QueryAll($sql);
    }


    public function orderProductModelSn($order_id)
    {

        if (empty($order_id)) {
            return array();
        }

        $sql = " select model_sn from meet_order_items where order_id='" . $order_id . "' and disabled='false' group by model_sn ";
        return $this->QueryAll($sql);
    }

    private function orderStatus($status)
    {
        return in_array($status, array('finish', 'dead', 'confirm', 'active'));
    }

    public function updateOrderStatus($order_id, $status)
    {
        if ($this->orderStatus($status)) {
            $sql = "update meet_order set status='" . $status . "' where order_id='" . $order_id . "'";
            if ($this->Execute($sql)) {
//                $order_info = $this->orderInfo($order_id);
//                Yii::app()->cache->delete('order-items-'.$order_info['purchase_id'].'_'.$order_info['customer_id']);
                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }

    }

    public function orderItem($order_id)
    {
        $sql = "select oi.*,p.cat_b,p.cat_s,p.season_id  as season ,p.cost_price  from meet_order_items as oi
        left join meet_product as p on p.product_id = oi.product_id  where order_id ='" . $order_id . "' and oi.disabled = 'false'";
        $result =  $this->QueryAll($sql);
        if(Yii::app()->params['is_latest_price']){
            foreach($result as $key => $val){
                $result[$key]['amount'] = $val['cost_price'] * $val['nums'];
            }
        }else{
            foreach($result as $key => $val){
                $result[$key]['price'] = $val['cost_price'];
            }
        }
        return $result;
    }


    public function addLog($order_id, $status, $name, $user_id)
    {
        $time = time();
        $sql = "insert into meet_order_log (order_id,user_id,name,time,status) value ('" . $order_id . "','" . $user_id . "','" . $name . "','" . $time . "','" . $status . "')";
        return $this->Execute($sql);
    }


    public function  getOrderLog($order_id)
    {
        $sql = "select * from meet_order_log where order_id = '" . $order_id . "' order by time desc";
        return $this->QueryRow($sql);
    }

    /**
     * 获取该用户的下线客户的预订金额
     * @param string $code
     * @return int
     */
    public function getAllPriceCount($code = '')
    {
        $sql = "SELECT parent_id,agent FROM `meet_customer` where code='" . $code . "'";
        $res = $this->QueryRow($sql);
        $s = 0;
        if (isset($res['agent']) && !empty($res['agent'])) {
            if ($res['parent_id'] == 1) {
                $sql = "SELECT a.order_id FROM {{order}} AS a LEFT JOIN `meet_customer` AS b ON a.customer_id=b.customer_id  WHERE b.agent='" . $res['agent'] . "' and b.parent_id='0'";
                $result= $this->QueryAll($sql);
                foreach ($result as $v) {
                    $s += $this->getCustomerNewCount($v['order_id']);
                }
            }
        }
        return $s;
    }

    /**
     * 该用户的所有下线的客户的预订金额
     * @param $data
     * @return int
     */
    public function getMasterCount($data){
        $res = explode(",", $data);
        $result = 0;
        foreach($res as $val){
            $result += $this->getAllPriceCount($val);
        }
        return $result;
    }

    public function  orderItemList($order_id)
    {
        $sql = "select oi.*,p.cat_b,p.cat_s,s.size_name,c.color_name,p.cost_price from meet_order_items as oi
        left join meet_product as p on p.product_id = oi.product_id
        left join meet_size as s on p.size_id = s.size_id
        left join meet_color as c on c.color_id = p.color_id
        where order_id ='" . $order_id . "' and oi.disabled = 'false' order by model_sn asc";
        $result = $this->QueryAll($sql);
        if(Yii::app()->params['is_latest_price']){
            foreach($result as $key => $val){
                $result[$key]['price'] = $val['cost_price'];
            }
        }
        return $result;
    }

    public function  DownloadOrderItemList($order_id)
    {
        $sql = "select oi.*,p.cat_b,p.cat_s,s.size_id,s.size_no,s.size_name,ms.scheme_id,ms.scheme_name,p.color_id,c.color_no,c.color_name,cb.big_id,cb.cat_name as big_name,
        cm.middle_id,cm.cat_name as middle_name, sm.small_id,sm.cat_name as small_name,cs.season_id,cs.season_name,
        p.level_id,l.level_name,p.memo,b.brand_name,b.brand_id,g.wave_name,g.wave_id,p.cost_price
        from meet_order_items as oi
        left join meet_product as p on p.product_id = oi.product_id
        left join meet_size as s on p.size_id = s.size_id
        left join meet_color as c on c.color_id = p.color_id
        left join meet_cat_big as cb on cb.big_id = p.cat_b
        left join meet_cat_middle as cm on cm.middle_id = p.cat_m
        left join meet_cat_small  as sm on sm.small_id = p.cat_s
        left join meet_scheme as ms on ms.scheme_id = p.scheme_id
        left join meet_season as cs on cs.season_id = p.season_id
        left join meet_level as l on l.level_id = p.level_id
        left join meet_brand as b on b.brand_id = p.brand_id
        left join meet_wave as g on g.wave_id = p.wave_id
        where oi.order_id ='" . $order_id . "' and oi.disabled = 'false' group by oi.product_sn order by oi.model_sn asc";
        $result = $this->QueryAll($sql);
        if(empty($result)) return array();
        if(Yii::app()->params['is_latest_price']){
            foreach($result as $key => $val){
                $result[$key]['amount'] = $val['cost_price'] * $val['nums'];
            }
        }
        return $result;
    }


    public function addOrder($purchase_id, $customer_id, $customer_name, $cost_item)
    {

        $create_time = time();

        //检查是否已经生成过订单
        $sql = "select * from meet_order where  purchase_id={$purchase_id} and customer_id= {$customer_id} ";
        $order_row = $this->QueryRow($sql);

        //订单主表
        if ($order_row) {
            $order_id = $order_row['order_id'];
            $order_sql = "UPDATE meet_order SET edit_time = {$create_time},cost_item='{$cost_item}'  WHERE order_id = '" . $order_id . "'";
        } else {
            $order_id = $this->build_order_no();
            $order_sql = "INSERT INTO meet_order (order_id,purchase_id,status,customer_id,customer_name,cost_item,create_time) VALUE
            ({$order_id},{$purchase_id},'active',{$customer_id},'{$customer_name}','{$cost_item}',{$create_time})";
        }

        if ($this->Execute($order_sql)) {
            return $order_id;
        } else {
            return false;
        }
    }


    public function addToOrderItem($order_id, $order_list)
    {
        $sql = '';

        foreach ($order_list as $k => $v) {

            $product_sn = $v['product_sn'];
            $style_sn = $v['style_sn'];
            $model_sn = $v['model_sn'];
            $name = $v['name'];
            $price = $v['price'];
            $num = $v['nums'];
            $product_id = $v['product_id'];
            $amount = $v['amount'];


            $insert_data_arr[] = "({$order_id},{$product_id},'{$product_sn}','{$style_sn}','{$model_sn}','{$name}','{$price}','{$amount}',{$num})";

        }

        $sql .= 'INSERT INTO {{order_items}} (order_id,product_id,product_sn,style_sn,model_sn,`name`,price,amount,nums) VALUES ' . implode(',', $insert_data_arr) . ';';
//        var_dump($sql);die();
        return $this->Execute($sql);
    }

    /**
     * 订单号生成
     */
    public function build_order_no()
    {
        return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }


    public function  getAllOrderId()
    {
        $sql = " select order_id from meet_order group by order_id";
        return $this->QueryAll($sql);
    }

    public function getList($select = array(), $order = '', $group = '')
    {
        $criteria = new CDbCriteria;
        $criteria->order = $order;
        $criteria->group = $group;
        //$criteria->params($select);
        $list = self::model()->findAll($criteria);
        return $list;
    }

    public function getCustomerNewCount($order_id, $default = false){
        if(Yii::app()->params['is_latest_price'] || $default){
            $sql = "SELECT oi.nums,p.cost_price FROM {{order_items}} AS oi LEFT JOIN {{product}} AS p ON p.product_id = oi.product_id WHERE oi.order_id ='{$order_id}' AND oi.disabled = 'false' ORDER BY oi.model_sn ASC";
            $result = $this->QueryAll($sql);
            $finally = 0;
            foreach($result as $k=>$val){
                $finally += $val['nums'] * $val['cost_price'];
            }
        }else{
            $sql = "SELECT SUM(amount) AS finally FROM {{order_items}} WHERE order_id='{$order_id}' AND disabled='false'";
            $result = $this->QueryRow($sql);
            $finally = $result['finally'];
        }
        return $finally;
    }

    public function getLatestCostPrice($model_sn){
        $sql = "SELECT cost_price FROM {{product}} WHERE model_sn ='{$model_sn}' AND disabled='false'";
        return $this->QueryRow($sql);
    }

    /**
     * 查找这个订单不同的商品价格
     *
     * @param $order_id
     * @return array
     */
    public function getThisOrderDifferent($order_id){
        $sql = "SELECT product_id,price,model_sn FROM {{order_items}} WHERE order_id='{$order_id}' AND disabled='false'";
        $result = $this->QueryAll($sql);
        if(empty($result)) return array();
        $product_id = "";
        foreach($result as $key => $val){
            $arr[$val['model_sn']] = $val['price'];
            $product_id .= ",".$val['product_id'];
        }
        $product_id = substr($product_id, 1);
        unset($result);
        $sql = "SELECT product_id,cost_price AS price,model_sn FROM {{product}} WHERE product_id IN (".$product_id.") AND disabled='false' AND is_down='0'";
        $result = $this->QueryAll($sql);
        if(empty($result)) return array();
        foreach($result as $k => $v){
            $res[$v['model_sn']] = $v['price'];
        }
        $rest = array();
        $rest['new'] = array_diff_assoc($res, $arr);
        $rest['old'] = array_diff_assoc($arr, $res);
        return $rest;
    }

    /**
     * 显示该商品的不同价格
     *
     * @param $result
     * @return mixed
     */
    public function showDifferentProduct($result){
        if(isset($result['old'])){
            $model_sn = "";
            foreach($result['old'] as $key => $val){
                $model_sn .= ",".$key;
            }
            $model_sn = substr($model_sn, 1);
            $sql = "SELECT model_sn,name,product_id FROM {{product}} WHERE model_sn IN (".$model_sn.") AND disabled='false' AND is_down='0' GROUP BY model_sn";
            return $this->QueryAll($sql);
        }
    }

    /**
     * 获取所有客户此大类的折扣信息
     *
     * @param $type_id
     * @return array
     */
    public function getAllCustomerDiscount($type_id){
        $sql = "SELECT o.order_id, c.big_{$type_id}_count AS discount,c.name,c.purchase_id,c.code,c.big_{$type_id} AS starget FROM meet_order AS o LEFT JOIN meet_customer AS c ON c.customer_id=o.customer_id WHERE o.disabled='false'";
        $result = $this->QueryAll($sql);
        foreach($result as $val){
            $val['amount'] = $this->getThisCustomerCatBigBroughtInfo($type_id,$val['order_id']);
            $val['final_amount'] = $val['amount'] * $val['discount'] /100;
            if(empty($val['starget'])){
                $val['percent'] = 0 .'%';
            }else{
                $val['percent'] = round( $val['final_amount'] / $val['starget'] * 100, 2 ).'%';
            }
            $res[] = $val;
        }
        if(empty($res)) return array();
        return $res;
    }

    /**
     * 获取此大类的商品的购买价格之和
     *
     * @param $type_id
     * @param $order_id
     * @return int
     */
    public function getThisCustomerCatBigBroughtInfo($type_id, $order_id){
        $sql = "SELECT p.cost_price,i.nums,i.amount FROM meet_order AS o LEFT JOIN meet_order_items AS i ON i.order_id=o.order_id LEFT JOIN meet_customer AS c ON c.customer_id=o.customer_id LEFT JOIN meet_product AS p ON p.product_id=i.product_id WHERE p.cat_b='{$type_id}' AND o.order_id='{$order_id}' AND i.disabled='false'";
        $res = $this->QueryAll($sql);
        $final = 0;
        foreach($res as $val){
            if(Yii::app()->params['is_latest_price']){
                $final += $val['cost_price']*$val['nums'];
            }else{
                $final += $val['amount'];
            }
        }
        return $final;
    }


//    /**
//     * 获取此大类的订货相关信息
//     *
//     * @param $order_id
//     * @param $big_id
//     * @return array
//     */
//    public function getThisOrderBigAndBigTarget($order_id, $big_id){
//        $sql = "SELECT i.nums,p.cost_price,i.amount,c.big_{$big_id} AS big_target, c.big_{$big_id}_count AS big_discount FROM {{order}} AS o
//LEFT JOIN {{order_items}} AS i ON o.order_id=i.order_id
//LEFT JOIN {{product}} AS p ON p.product_id = i.product_id
//LEFT JOIN {{customer}} AS c ON o.customer_id = c.customer_id
//WHERE o.order_id ='{$order_id}' AND p.cat_b='{$big_id}'";
//        $result = $this->QueryAll($sql);
//        if(empty($result)) return array();
//        //此大类的订货指标
//        $items['big_target'] = $result[0]['big_target'];
//        //此大类的订货的折扣
//        $items['big_discount'] = $result[0]['big_discount'];
//        //实际购买此大类的价格
//        $items['bro_big_money'] = 0;
//        //实际购买此大类的数量
//        $items['bro_big_nums'] = 0;
//        foreach($result as $val){
//            $items['bro_big_nums'] += $val['nums'];
//            if(Yii::app()->params['is_latest_price']){
//                $items['bro_big_money'] += $val['nums'] * $val['cost_price'];
//            }else{
//                $items['bro_big_money'] += $val['amount'];
//            }
//        }
//        unset($result);
//        $items['bro_big_discount'] = $items['bro_big_money'] * $items['big_discount'];
//        return $items;
//    }

    public function getThisTypeList($order_id){
        $sql = "SELECT p.type_id,i.nums,p.cost_price,p.name,i.amount,p.model_sn FROM {{order}} AS o
        LEFT JOIN {{order_items}} AS i ON o.order_id=i.order_id
        LEFT JOIN {{product}} AS p ON p.product_id=i.product_id WHERE o.order_id='{$order_id}' AND i.disabled='false'";
        $result = $this->QueryAll($sql);
        if(empty($result)) return array();
        foreach($result as $val){
            $arr[$val['type_id']][] = $val;
        }
        return $arr;
    }

    /**
     * 获取所有用户的折扣信息
     *
     * @return array
     */
    public function getAllUserOrderItems(){
        $sql = "SELECT o.order_id,c.customer_id,c.big_1_count,c.big_2_count,c.big_3_count,c.big_4_count,c.big_6_count FROM `meet_order` AS o LEFT JOIN `meet_customer` AS c ON c.customer_id=o.customer_id WHERE c.disabled='false' AND o.disabled='false'";
        $result = $this->QueryAll($sql);
        $item = array();
        foreach($result as $val){
            $item[$val['order_id']] = $val;
        }
        return $item;
    }

    /**
     * 根据款号获取其大类(大类小类根据款号修改)
     *
     * @return array
     */
    public function getProductModelSnAndCatBig(){
        $sql = "SELECT model_sn,cat_b FROM `meet_product` WHERE disabled='false'";
        $result = $this->QueryAll($sql);
        $res = array();
        foreach($result as $val){
            $res[$val['model_sn']] = $val['cat_b'];
        }
        unset($val);
//      下面方法为验证款号下是否有多个的大类
//        $items = array();
//        foreach($res as $key => $val){
//            if(count(array_unique($val)) != 1){
//                $items[]  = $key;
//            }
//        }
//        var_dump($items);die;
        return $res;
    }

    public function getAllOrderItemsList(){
        $sql = "SELECT i.model_sn,i.nums,i.order_id,s.size_no,c.code,p.cost_price,color.color_no
FROM `meet_order_items` AS i
LEFT JOIN `meet_order` AS o ON o.order_id=i.order_id
LEFT JOIN `meet_customer` AS c ON c.customer_id=o.customer_id
LEFT JOIN `meet_product` AS p ON i.product_sn=p.product_sn
LEFT JOIN `meet_size` AS s ON p.size_id = s.size_id
LEFT JOIN `meet_color` AS color ON p.color_id=color.color_id
WHERE i.disabled='false'";
        return $this->QueryAll($sql);
    }
}
