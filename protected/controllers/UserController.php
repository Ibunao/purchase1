<?php
/**
 * 登录页
 *
 * @author        chenfenghua <843958575@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       api.controllers.croncontroller
 * @version       v1.0.0
 */

class UserController extends Controller
{
    public $layout;
    public function __construct($id,$module)
    {
        parent::__construct($id,$module);
    }

    /**
     * 用户登录
     */
    public function actionIndex()
    {
        $this->layout = '/layouts/column_user';
        $error = false;
        if ($_POST) {
            $account = $_POST['account'];
            $password = $_POST['password'];

            $userModel = new User();
            // var_dump($account,md5(md5($password)));exit;
            $item = $userModel->login($account,md5(md5($password)));
            Yii::app()->session['code'] = $item['code'];
            if ($item) {
                $nowTime = time();
                Yii::app()->session['customer_id'] = $item['customer_id'];
                Yii::app()->session['purchase_id'] = $item['purchase_id'];
                Yii::app()->session['name'] = $item['name'];
                Yii::app()->session['mobile'] = $item['mobile'];
                Yii::app()->session['type'] = $item['type'];
                Yii::app()->session['province'] = $item['province'];
                Yii::app()->session['area'] = $item['area'];
                Yii::app()->session['target'] = $item['target'];
                Yii::app()->session['login_time'] = $nowTime;
                $userModel->userLoginLog($item['customer_id'], $nowTime);
                $this->redirect('/default/index');
            } else {
                $error = true;
            }
        }

        $this->render('index',array('error'=>$error));
    }

    /**
     * 用户退出
     */
    public function actionLogout()
    {
        unset(Yii::app()->session['customer_id']);
        unset(Yii::app()->session['purchase_id']);
        unset(Yii::app()->session['name']);
        unset(Yii::app()->session['mobile']);
        unset(Yii::app()->session['type']);
        unset(Yii::app()->session['province']);
        unset(Yii::app()->session['area']);
        unset(Yii::app()->session['target']);
        unset(Yii::app()->session['login_time']);

        $this->redirect('index');
    }

    /**
     * 删除缓存
     */
    public function actionCache()
    {
        Yii::app()->cache->flush();
        echo "清除缓存";
    }
}