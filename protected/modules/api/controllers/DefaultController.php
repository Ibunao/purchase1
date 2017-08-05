<?php

/**
 * api 首页
 * @author        chenfenghua <843958575@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       api.controllers.croncontroller
 * @version       v1.2.0
 */
class DefaultController extends ApiController
{
    private $this_stie;
    public $purchase;
    public $brand;
    public $color;
    public $size;
    public $cat_b;
    public $cat_m;
    public $cat_s;
    public $season;
    public $level;
    public $scheme;
    public $wave;
    public $price_level;
    public $type;
    public $route = 'default/index';
    public $productModel;

    public function __construct($id, $module)
    {
        parent::__construct($id, $module);
    }

    public function init()
    {
        $this->productModel = new AppProduct();
        $this->purchase = $this->productModel->tableValue('purchase', 'purchase_name', 'purchase_id');
        $this->brand = $this->productModel->tableValue('brand', 'brand_name', 'brand_id');
        $this->cat_b = $this->productModel->tableValue('cat_big', 'cat_name', 'big_id');
        $this->cat_m = $this->productModel->tableValue('cat_middle', 'cat_name', 'middle_id');
        $this->cat_s = $this->productModel->tableValue('cat_small', 'cat_name', 'small_id');
        $this->wave = $this->productModel->tableValue('wave', 'wave_name', 'wave_id');
        $this->scheme = $this->productModel->tableValue('scheme', 'scheme_name', 'scheme_id');
        $this->season = $this->productModel->tableValue('season', 'season_name', 'season_id');
        $this->color = $this->productModel->tableValue('color', 'color_name', 'color_id');
        $this->size = $this->productModel->tableValue('size', 'size_name', 'size_id');
        $this->level = $this->productModel->tableValue('level', 'level_name', 'level_id');
        $this->type = $this->productModel->tableValue('type', 'type_name', 'type_id');
        $this->price_level = array(
            1 => '0-99',
            2 => '100-199',
            3 => '200-299',
            4 => '300-399',
            5 => '400-499',
            6 => '500-999',
            7 => '1k-1.5k',
            8 => '1.5k-2k',
            9 => '2k以上',
        );
    }

    /**
     * 首页
     */
    public function actionIndex()
    {
        $purchase_id = $this->request('purchase_id');
        $customer_id = $this->request('customer_id');
        Yii::app()->session['purchase_id'] = $purchase_id;
        $page = !empty($_POST['page']) ? $_POST['page'] : 1;

        $big_id = $this->request("big_id");     //大类
        $small_id = $this->request("small_id");  //小类
        $sd = $this->request('sd');             //季节
        $wv = $this->request('wv');             //波段
        $lv = $this->request('lv');             //等级
        $plv = $this->request('plv');           //价格带
        $or = $this->request('or');             //已定/未定
        $price = $this->request('price', 'int');
        $hits = $this->request('hits', 'int');
        //输入搜索
        $serial_num = $this->request('serial_num', 'int');
        $conArr = array();


        if (!empty($big_id)) {
            $conArr[] = 's_id_' . $big_id;
            if (!empty($small_id)) {
                $conArr[] = 'c_id_' . $small_id;
            }
        }

        if ($sd) {
            $conArr[] = 'sd_' . $sd;
        }
        if ($wv) {
            $conArr[] = 'wv_' . $wv;
        }
        if ($lv) {
            $conArr[] = 'lv_' . $lv;
        }
        if ($plv) {
            $conArr[] = 'plv_' . $plv;
        }

        $this->productModel = new Product();

        $params = array(
            'or' => $or,
            'purchase_id' => $purchase_id,
            'customer_id' => $customer_id,
            'hits' => $hits,
        );
        $arr = array();

        $model['list'] = $this->productModel->newitems($conArr, $serial_num, $params, $price, $page);
        $appModel = new AppProduct();

        foreach ($model['list'] as $v) {
            $item['product_id'] = $v['product_id'];
            $item['name'] = $v['name'];
            $item['img_url'] = Yii::app()->params['img_url']. $v['img_url'];
            $item['model_sn'] = $v['model_sn'];
            $item['serial_num'] = $v['serial_num'];
            $item['type_id'] = $v['type_id'];
            $item['is_down'] = $v['is_down'];
            $item['is_order'] = $v['is_order'];
            $item['product_sn'] = $v['product_sn'];
            $item['cost_price'] = $v['cost_price'];
            $item['color'] = $this->color[$v['color_id']];
            $item['size'] = $v['size'] ? implode(',', $this->_arrayToCount($v['size'], true)) : '';
            $item['size_list'] = $this->getSizeList($v['model_sn']);
            $item['wave'] = isset($this->wave[$v['wave_id']]) ? $this->wave[$v['wave_id']] : "";
            $item['level'] = $this->level[$v['level_id']];
            $item['memo'] = $v['memo'];
            $item['type'] = $this->type[$v['type_id']];
            $model_sn = $item['model_sn'];
            $item['order_num'] = $appModel->getThisModelOrdered($customer_id, $model_sn);
            $item['product_list'] = $appModel->getProductListsInfo($model_sn, $purchase_id, $customer_id);
            $arr[] = $item;
        }
        unset($model['list']);
        $result['goods_lists'] = $arr;
        //基础数据
        $list['wave'] = $this->_arrayToCount($this->wave);
        $list['scheme'] = $this->_arrayToCount($this->scheme);
        $list['season'] = $this->_arrayToCount($this->season);
        $list['level'] = $this->_arrayToCount($this->level);
        $list['price_level'] = $this->_arrayToCount($this->price_level);
        $list['cat_list'] = $this->_arrayToCount(Cache::cateList(), true);
        $result['orders'] = $this->getCustomerOrdered($customer_id);
        $result['base'] = $list;
        if ($page > 1) {
            $this->sendSucc($result, '商品数据');
        } else {
            $this->sendSucc($result, '商品数据');
        }
    }

    /**
     * ID转换
     * @param array $array
     * @param bool|false $is_direct
     * @return array|bool
     */
    private function _arrayToCount(Array $array, $is_direct = false)
    {
        if (!$array) return false;
        $items = array();
        foreach ($array as $k => $v) {
            if ($is_direct) $items[] = $v;
            else $items[] = array('id' => $k, 'val' => $v);
        }
        return $items;
    }

    /**
     * 获取该款号的所有尺寸列表
     * @param $model_sn
     * @return array
     */
    public function getSizeList($model_sn)
    {
        $arr = Yii::app()->cache->get("app-get-size-list-" . $model_sn);
        if (!$arr) {
            $res = $this->selectQueryRows("s.size_name,p.size_id", "{{product}} AS p LEFT JOIN {{size}} AS s ON s.size_id=p.size_id", "p.model_sn='{$model_sn}'  ORDER BY s.size_id ASC");
            if(!$res) return array();
            $item = $this->arrayToTransPosition($res, "size_id");
            foreach ($item as &$v) {
                $arr[] = $v['size_name'];
            }
            Yii::app()->cache->set("app-get-size-list-" . $model_sn, $arr, 86400);
        }
        return array_values($arr);
    }

    /**
     * 获取该用户的订货总量、金额、完成率、目标
     * @param $customer_id
     * @return mixed
     */
    public function getCustomerOrdered($customer_id)
    {
        $res = $this->selectQueryRow("SUM(i.nums) as nums,o.cost_item", "{{order}} AS o LEFT JOIN {{order_items}} AS i ON i.order_id=o.order_id", "o.customer_id='{$customer_id}' AND i.disabled='false'");
        $target = $this->selectQueryRow("target", "{{customer}}", "customer_id='{$customer_id}'");
        $result['nums'] = isset($res['nums']) ? $res['nums'] : 0;
        $result['cost_item'] = isset($res['cost_item']) ? $res['cost_item'] : 0;
        $result['target'] = isset($target['target']) ? $target['target'] : 0;
        if ($result['target'] == 0) {
            $result['percent'] = "0";
        } else {
            $result['percent'] = (string)round($result['cost_item'] / $result['target'] * 100, 2);
        }
        return $result;
    }

    /**
     * 点击预订
     */
    public function actionOrderProduct()
    {
        $order_items = $this->post("order_items");
        $customer_id = $this->post("customer_id");
        $purchase_id = $this->post("purchase_id");
        $model_sn = $this->post("model_sn");
        $serial_num = $this->post("serial_num");

        $appModel = new AppProduct();
        if(!$appModel->checkThisCustomerIsValid($customer_id, $purchase_id)){
            $this->sendJSONResponse(array('code' => '400', 'msg' => '请重新登录后再试'));
        }
        Yii::app()->session['purchase_id'] = $purchase_id;
        $order_items = substr($order_items, 1);
        $order_items = substr($order_items, 0, -1);
        $each_info = explode("|", $order_items);
        $res = array();
        foreach ($each_info as $key => $val) {
            $res[] = explode("_", $val);
        }
        $items = array();
        foreach ($res as $k => $v) {
            if (!empty($v[0])) {
                $items[] = $v;
            }
        }
        $customer_info = $appModel->ModelQueryRow("SELECT `name` FROM {{customer}} WHERE customer_id='{$customer_id}' AND disabled='false'");
        $result = $appModel->orderSubmit($items, $purchase_id, $customer_id, $customer_info['name']);
        $data = $appModel->getOnlyBroughtModel($model_sn, $purchase_id, $customer_id);
        $data['order_results'] = $this->getCustomerOrdered($customer_id);
        $have = $appModel->getThisModelOrdered($customer_id, $model_sn, $serial_num);
        if ($have >= 1) {
            $data['is_order'] = 1;
        } else {
            $data['is_order'] = 2;
        }
        if ($result) {
            $this->sendJSONResponse(array('code' => '200', 'data' => $data));
        } else {
            $this->sendJSONResponse(array('code' => '400', 'data' => $data));
        }
    }

    /**
     * 点击预订按钮后返回当前流水号所有的数据
     */
    public function actionOrderThisProduct()
    {
        $serial_num = $this->post("serial_num");
        $purchase_id = $this->post("purchase_id");
        $customer_id = $this->post("customer_id");
        $appModel = new AppProduct();
        $data = $appModel->getThisProductInfo($serial_num, $purchase_id, $customer_id);
        if(!empty($data)){
            $wave_list = $appModel->tableValue("wave", "wave_name", "wave_id");
            $data['wave'] = isset($wave_list[$data['wave_id']]) ? $wave_list[$data['wave_id']] :"";
            $data['size_list'] = $this->getSizeList($data['model_sn']);
        }
        $this->sendJSONResponse(array('code' => '200', 'data' => $data));
    }

    /**
     * 订单列表
     */
    public function actionOrderListSheet()
    {
        $customer_id = $this->post("customer_id");
        $purchase_id = $this->post("purchase_id");
        $type = $this->post("type");
        Yii::app()->session['purchase_id'] = $purchase_id;
        $appModel = new AppProduct();
        $orderModel = new Order();
        $order = $orderModel->orderItems($purchase_id, $customer_id);
        if ($type == 'season') {
            $data = $appModel->orderSeasonTable($order);
        } elseif ($type == "price") {
            $data = $appModel->orderPriceTable($order);
        } else {
            $this->sendError("请选择订单类型");
        }
        if (empty($data)) {
            $this->sendError("您还没有订单");
        }
        $this->sendJSONResponse(array('code' => '200', 'data' => $data));
    }

    /**
     * 检查此用户订单是否为  提交/完成 状态
     */
    public function actionCheckSubmit()
    {
        $purchase_id = $this->post("purchase_id");
        $customer_id = $this->post("customer_id");

        $orderModel = new AppProduct();
        $res = $orderModel->checkThisSubmit($purchase_id, $customer_id);
        $this->sendJSONResponse(array('code' => '200', 'status' => $res));
    }

    /**
     * 提交订单
     */
    public function actionSubmitOrder()
    {
        $purchase_id = $this->post("purchase_id");
        $customer_id = $this->post("customer_id");

        $orderModel = new AppProduct();
        $res = $orderModel->submitOrder($purchase_id, $customer_id);
        $this->sendJSONResponse(array('code' => '200', 'msg' => '提交订单成功', 'status'=>$res));
    }

    /**
     * 取消订单
     */
    public function actionCancelOrder()
    {
        $purchase_id = $this->post("purchase_id");
        $customer_id = $this->post("customer_id");

        $orderModel = new AppProduct();
        $res = $orderModel->orderRepeal($purchase_id, $customer_id);
        if(!$res){
            $this->sendJSONResponse(array('code' => '400','status'=>$res, 'msg' => '取消订单失败'));
        }else{
            $this->sendJSONResponse(array('code' => '200','status'=>$res, 'msg' => '取消订单成功'));
        }
    }

    /**
     * 订单明细
     */
    public function actionOrderDetail(){
        $purchase_id = $this->post("purchase_id");
        $customer_id = $this->post("customer_id");
        $page = !empty($_POST['page']) ? $_POST['page'] : 1;

        $orderModel = new Order();
        $order_list = $orderModel->orderItemList($purchase_id, $customer_id, $page);
        if(empty($order_list['item_list'])) $this->sendJSONResponse(array('code' => '400','data'=>array(), 'msg'=>'数据为空'));
        $item = array();
        foreach($order_list['item_list'] as $val){
            $item[$val['model_sn']] = $val;
        }
        $appModel = new AppProduct();
        $wave_list = $appModel->tableValue("wave", "wave_name", "wave_id");
        $results = array();
        foreach($item as $k => $v){
            $model_detail = $appModel->getOnlyBroughtModel($k, $purchase_id, $customer_id);
            if(empty($model_detail)){
                continue;
            }
            $items['size_list'] = $this->getSizeList($k);
            $items['product_list'] = $model_detail['product_list'];
            $items['order_num'] = $model_detail['order_num'];
            $items['model_sn'] = $v['model_sn'];
            $items['name'] = $v['name'];
            $items['serial_num'] = $v['serial_num'];
            $items['cost_price'] = $v['cost_price'];
            $items['img_url'] = Yii::app()->params['img_url'].$v['img_url'];
            $items['wave'] = $wave_list[$v['wave_id']];
            $items['memo'] = $v['memo'];
            $items['type_id'] = $v['type_id'];
            $results[] = $items;
        }
        $res['goods_lists'] = $results;
        $this->sendJSONResponse(array('code' => '200','data'=>$res));
    }

    /**
     * 查看我的分销
     */
    public function actionUserDownBuyers()
    {
        $customer_id = $this->post("customer_id");
        $orders = new Order();
        $downUserInfo = $orders->getUserDownUsers($customer_id);
        if(!$downUserInfo) $this->sendError('暂无相关订单');
        else $this->sendJsonResponse(array('code' => 200, 'data' => $downUserInfo));
    }
}

