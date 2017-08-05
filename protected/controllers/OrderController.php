<?php

/**
 * 订单页
 *
 * @author        chenfenghua <843958575@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       api.controllers.croncontroller
 * @version       v1.0.0
 */
class OrderController extends B2cController
{
    public $wave;

    public function __construct($id, $module)
    {
        if(isset($this->down_code))
        {
            unset($this->down_name, $this->down_code);
        }
        parent::__construct($id, $module);
    }

    public function init()
    {
        $this->registerJs('public');

    }

    /**
     * 订单首页
     */
    public function actionIndex()
    {
        $orderModel = new Order();
        $productModel = new Product();
        $this->wave = $productModel->tableValue('wave', 'wave_name', 'wave_id');
        $order = $orderModel->orderItems($this->purchase_id, $this->customer_id);
        $model_items = array();
        $model_sn = array();
        $order_row = isset($order['order_row']) ? $order['order_row'] : array();
        $list = array();
        $product_num = array();
        if (isset($order['item_list']) && $order['item_list']) {
            $order_items = $order['item_list'];

            foreach ($order_items as $v) {
                if (isset($model_items[$v['model_sn']]))
                    $model_items[$v['model_sn']] += $v['nums'];
                else $model_items[$v['model_sn']] = $v['nums'];
                $product_num[$v['product_sn']] = $v['nums'];

                if (!in_array($v['model_sn'], $model_sn)) $model_sn[] = $v['model_sn'];
            }
        }
        if ($model_sn) {
            foreach ($model_sn as $v) {
                $list[] = $productModel->detail($v);
            }
        }
        $this->render('index', array('model_items' => $model_items, 'order_row' => $order_row, 'list' => $list, 'product_num' => $product_num));
    }

    /**
     *  订单明细
     */
    public function actionBydetail()
    {
        $page = isset($_GET['page']) ? $this->get('page') : 1;
        $orderModel = new Order();
        $productModel = new Product();
        $this->wave = $productModel->tableValue('wave', 'wave_name', 'wave_id');
        //$order = $orderModel->orderItems($this->purchase_id,$this->customer_id);
        $order = $orderModel->orderItemList($this->purchase_id, $this->customer_id, $page);
        $model_items = array();
        $model_sn = array();
        $order_row = isset($order['order_row']) ? $order['order_row'] : array();
        $list = array();
        $product_num = array();
        if (isset($order['item_list']) && $order['item_list']) {
            $order_items = $order['item_list'];
            foreach ($order_items as $v) {
                if (isset($model_items[$v['model_sn']]))
                    $model_items[$v['model_sn']] += $v['nums'];
                else $model_items[$v['model_sn']] = $v['nums'];
                $product_num[$v['product_sn']] = $v['nums'];
                if (!in_array($v['model_sn'], $model_sn)) $model_sn[] = $v['model_sn'];
            }
        }
        if ($model_sn) {
            foreach ($model_sn as $v) {
                $list[] = $productModel->detail($v);
            }
        }
        $this->render('bydetail', array('model_items' => $model_items, 'order_row' => $order_row, 'list' => $list, 'product_num' => $product_num, 'next' => $page + 1));
    }

    /**
     * ajax片段提交
     */
    public function actionDetail()
    {
        $page = $_GET['page'];
        $orderModel = new Order();
        $productModel = new Product();
        $this->wave = $productModel->tableValue('wave', 'wave_name', 'wave_id');
        //$order = $orderModel->orderItems($this->purchase_id,$this->customer_id);
        $order = $orderModel->orderItemList($this->purchase_id, $this->customer_id, $page);
        $model_items = array();
        $model_sn = array();
        $order_row = isset($order['order_row']) ? $order['order_row'] : array();
        $list = array();
        $product_num = array();
        if (isset($order['item_list']) && $order['item_list']) {
            $order_items = $order['item_list'];
            foreach ($order_items as $v) {
                if (isset($model_items[$v['model_sn']]))
                    $model_items[$v['model_sn']] += $v['nums'];
                else $model_items[$v['model_sn']] = $v['nums'];
                $product_num[$v['product_sn']] = $v['nums'];

                if (!in_array($v['model_sn'], $model_sn)) $model_sn[] = $v['model_sn'];
            }
        }
        if ($model_sn) {
            foreach ($model_sn as $v) {
                $list[] = $productModel->detail($v);
            }
        }
        $this->renderPartial('detail', array('model_items' => $model_items, 'order_row' => $order_row, 'list' => $list, 'product_num' => $product_num, 'next' => $page + 1));
    }

    /**
     * 创建订单
     */
    public function actionCreate()
    {
        $product = $this->post('Product');
        $orderModel = new Order();
        $result = $orderModel->Add($product, $this->purchase_id, $this->customer_id, $this->username);
        $url = Yii::app()->request->urlReferrer;
        if ($result) $this->redirect($url);
    }

    /**
     *  ajax 预订
     */
    public function actionGetAllPrice()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $orderItem = isset($_POST['dt']) ? $_POST['dt'] : '非法访问';
            // var_dump($_POST);exit;
            // array (size=1)
            // 'dt' => string '|7_4|8_|9_|10_2|11_|12_'
            $orderItem = substr($orderItem, 1);
            $arr = explode("|", $orderItem);
            foreach ($arr as $k => $v) {
                $result[] = explode("_", $v);
            }
            $orderModel = new Order();
            if($orderModel->AddAjax($result, $this->purchase_id, $this->customer_id, $this->username)){
                $order = $this->getCustomerOrdered($this->customer_id);
                if ($order) {
                    echo json_encode(array('code'=>'200','data'=>$order, 'msg'=>'订货成功'));
                } else {
                    echo json_encode(array('code'=>'400', 'msg'=>'订货成功'));
                }
            }else{
                echo json_encode(array('code'=>'400', 'msg'=>'订货失败'));
            }
        }
    }

    /**
     * 获取该用户的订货总量、金额、完成率、目标
     * @param $customer_id
     * @return mixed
     */
    public function getCustomerOrdered($customer_id)
    {
        $productModel = new AppProduct();
        $res = $productModel->ModelQueryRow("SELECT SUM(i.nums) as nums,o.cost_item FROM {{order}} AS o LEFT JOIN {{order_items}} AS i ON i.order_id=o.order_id WHERE o.customer_id='{$customer_id}' AND i.disabled='false'");
        $target = $productModel->ModelQueryRow("SELECT target FROM {{customer}} WHERE customer_id='{$customer_id}'");
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
     * 删除商品
     */
    public function actionDelete()
    {
        $model_sn = $this->get('model_sn');
        $order_id = $this->get('order_id');

        $orderModel = new Order();
        $orderModel->deleteItems($order_id, $model_sn, $this->purchase_id, $this->customer_id);

        $url = Yii::app()->request->urlReferrer;
        $this->redirect($url);
    }

    /**
     * 季节汇总、订单统计
     */
    public function actionBycount()
    {
        $page = isset($_GET['page']) ? $this->get('page') : 1;
        $orderModel = new Order();
        $productModel = new Product();
        $order = $orderModel->orderItems($this->purchase_id, $this->customer_id, $page);
        $result = $productModel->orderSprandSumItems($order['item_list']);
        // var_dump($result);exit;
        $this->render('bycount', array('list' => $result['list'], 'result' => $result));
    }

    /**
     * 价格汇总
     */
    public function actionByprice()
    {
        $page = isset($_GET['page']) ? $this->get('page') : 1;
        $orderModel = new Order();
        $productModel = new Product();
        $order = $orderModel->orderItems($this->purchase_id, $this->customer_id, $page);//已经购买的产品
        $result = $productModel->orderJiaGeDaiItems($order['item_list']);

        $this->render('byprice', array('list' => $result['list'], 'result' => $result));
    }

    /**
     * 我的分销
     */
    public function actionBydownuser()
    {
        $orders = new Order();
        $downUserInfo = $orders->getUserDownUsers($this->customer_id);
        $this->render('bydownuser',
            array(
                'downUserInfo' => $downUserInfo
            )
        );
    }

    /**
     * 订单提交
     */
    public function actionSubmit()
    {
        $orderModel = new Order();
        $orderModel->orderSubmit($this->purchase_id, $this->customer_id);

        $url = Yii::app()->request->urlReferrer;
        $this->redirect($url);
    }

    /**
     * 订单提交验证
     */
    public function actionSubmitcheck()
    {
        $target = Yii::app()->session['target'];
        $amount = $this->amount;

        if ($target < $amount) echo '400';
        echo '200';
    }

    /**
     * 订单撤销
     */
    public function actionRepeal()
    {
        $orderModel = new Order();
        $orderModel->orderRepeal($this->purchase_id, $this->customer_id);

        $url = Yii::app()->request->urlReferrer;
        $this->redirect($url);
    }

    /**
     * 订单撤销验证
     */
    public function actionRepealcheck()
    {
        $orderModel = new Order();
        $result = $orderModel->orderRepealCheck($this->purchase_id, $this->customer_id);

        if ($result) echo '200';
        else echo '400';
    }

    /**
     * 获取当前预订 的总价格、数量
     */
    public function actionGetNowOrderNumber()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $orderModel = new Order();
            $items = $orderModel->orderItems($this->purchase_id, $this->customer_id);
            $js[] = $this->total_num = isset($items['order_row']['total_num']) ? $items['order_row']['total_num'] : 0;
            $js[] = $this->amount = isset($items['order_row']['cost_item']) ? $items['order_row']['cost_item'] : '0.00';
            echo json_encode($js);
        }
    }

    public function actionUserInfo()
    {
        if (Yii::app()->request->isAjaxRequest) {

        }
    }
} 