<?php

/**
 * 登陆
 * @author       zangmiao <838881690@qq.com>
 * @copyright    Copyright (c) 2011-2016 octmami.All Rights Reserved.
 * @link         http://mall.octmami.com
 * @package      api.controllers.logincontroller
 * @version      v1.2
 */
class LoginController extends ApiController
{

    public function actionLogin()
    {
        $username = $this->post("username");
        $pass = $this->post("password");
        if (empty($username) || empty($pass)) {
            $this->sendJSONResponse(array('code' => '400', 'msg' => '请输入用户名/密码'));
        }
        $user = new User();
        $res = $user->login($username, md5(md5($pass)));
        if ($res) {
            $nowTime = time();
            $user->userLoginLog($res['customer_id'], $nowTime);
            $result['customer_id'] = $res['customer_id'];
            $result['purchase_id'] = $res['purchase_id'];
            $result['name'] = $res['name'];
            //检查该用户订单状态
            $orderModel = new AppProduct();
            $orderStatus = $orderModel->checkThisSubmit($res['purchase_id'], $res['customer_id']);
            $result['status'] = $orderStatus;
            $result['online'] = Yii::app()->params['is_online'];
            $result['is_change_url'] = Yii::app()->params['is_change_url'];
            $result['change_url'] = Yii::app()->params['change_url'];
            $result['is_distribution'] = $res['parent_id'] == 1 ? 'yes' : 'no';
            $result['purchase_name'] = $res['purchase_id'] == 1 ?
                Yii::app()->params['purchase_oct'] : Yii::app()->params['purchase_uki'];
            $result['is_spring_summer'] = Yii::app()->params['season_title'] == '春夏' ? 'yes' : 'no';
            $this->sendJSONResponse(array('code' => '200', 'data' => $result));
        } else {
            $this->sendJSONResponse(array('code' => '400', 'msg' => '登录失败'));
        }
    }

    /**
     * 显示登陆的背景图片
     */
    public function actionBackgroundImage()
    {
        $this->sendJSONResponse(
            array(
                'code' => 200,
                'image' => Yii::app()->params['background_images'],//背景图片
                'btn_color' => Yii::app()->params['app_login_btn_color'], //登录按钮背景颜色
                'btn_font_color' => Yii::app()->params['app_login_btn_font_color'],//登录文字颜色
                'btn_x' => Yii::app()->params['app_login_frame_x'],
                'btn_y' => Yii::app()->params['app_login_frame_y'],
            )
        );
    }
}