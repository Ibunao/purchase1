<?php
/**
 *
 * @author       zangmiao <838881690@qq.com>
 * @copyright    Copyright (c) 2011-2016 octmami.All Rights Reserved.
 * @link         http://mall.octmami.com
 */
class DownUserController extends B2cController
{
    public function __construct($id, $module)
    {
        parent::__construct($id, $module);
    }

    public function init()
    {
        $this->registerJs('public');

    }

    /**
     * 季节汇总、订单统计
     */
    public function actionBydowncount()
    {
        $page = isset($_GET['page']) ? $this->get('page') : 1;
        $purchase_id = $this->get('purchase_id');
        $customer_id = $this->get('customer_id');

        $orderModel = new Order();
        $userInfo = $orderModel->userBaseInfo($customer_id);
        if(!$userInfo){
            $this->redirect('/');
        }
        $this->down_code = $userInfo['code'];
        $this->down_name = $userInfo['name'];
        $productModel = new Product();
        $order = $orderModel->orderItems($purchase_id, $customer_id, $page);
        $result = $productModel->orderSprandSumItems($order['item_list']);
        $this->render('bydowncount',
            array(
                'list' => $result['list'],
                'result' => $result,
                'purchase_id' => $purchase_id,
                'customer_id' => $customer_id
            )
        );
    }

    public function actionBydownprice()
    {
        $page = isset($_GET['page']) ? $this->get('page') : 1;
        $purchase_id = $this->get('purchase_id');
        $customer_id = $this->get('customer_id');
        $orderModel = new Order();
        $userInfo = $orderModel->userBaseInfo($customer_id);
        if(!$userInfo){
            $this->redirect('/');
        }
        $this->down_code = $userInfo['code'];
        $this->down_name = $userInfo['name'];
        $productModel = new Product();
        $order = $orderModel->orderItems($purchase_id, $customer_id, $page);//已经购买的产品
        $result = $productModel->orderJiaGeDaiItems($order['item_list']);

        $this->render('bydownprice',
            array(
                'list' => $result['list'],
                'result' => $result,
                'purchase_id' => $purchase_id,
                'customer_id' => $customer_id
            )
        );
    }
}