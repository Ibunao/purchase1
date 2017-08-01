<?php
/**
 * 首页和商品详情页
 *
 * @author        chenfenghua <843958575@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       api.controllers.croncontroller
 * @version       v1.0.0
 */
class DefaultController extends B2cController
{
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
    public $type;
    public $parice_level;
    public $route = 'default/index';

    public $productModel;

    public function __construct($id, $module)
    {
        parent::__construct($id, $module);
    }

    public function init()
    {
        $this->productModel = new Product();
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
        $this->parice_level = array(
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

        $this->registerJs('public');
    }

    /**
     * 首页
     */
    public function actionIndex()
    {
        //页码
        $page = isset($_GET['page']) ? $this->get('page') : 1;

        $c_ids = $this->get('c_id');         //分类ID 主分类,二级分类 的格式
        $sd = $this->get('sd');             //季节
        $wv = $this->get('wv');             //波段
        $lv = $this->get('lv');             //等级
        $plv = $this->get('plv');           //价格带
        $or = $this->get('or');             //已订/未订
        $price = $this->get('price', 'int');    //价格升降排序
        $hits = $this->get('hits', 'int');      //人气升降排序

        //输入搜索
        $serial_num = $this->get('serial_num', 'int');
        $conArr = array();
        //小分类 大分类
        $c_id = $b_id = 0;
        if ($c_ids) {
            $cat_arr = explode(',', $c_ids);

            if (isset($cat_arr[0])) $b_id = $cat_arr[0];
            if (isset($cat_arr[1])) $c_id = $cat_arr[1];

            if ($c_id) {
                $conArr[] = 's_id_' . $b_id;
                $conArr[] = 'c_id_' . $c_id;
            } elseif ($b_id) {
                $conArr[] = 's_id_' . $b_id;
            }

        }
        if ($sd) {
            $sdArr = explode('_', $sd);
            $conArr[] = 'sd_' . $sdArr[0];
            $model['sd'] = $sdArr[1];
        }
        if ($wv) {
            $wvArr = explode('_', $wv);
            $conArr[] = 'wv_' . $wvArr[0];
            $model['wv'] = $wvArr[1];
        }
        if ($lv) {
            $lvArr = explode('_', $lv);
            $conArr[] = 'lv_' . $lvArr[0];
            $model['lv'] = $lvArr[1];
        }
        if ($plv) {
            $plvArr = explode('_', $plv);
            $conArr[] = 'plv_' . $plvArr[0];
            $model['plv'] = $plvArr[1];
        }

        $this->productModel = new Product();

        $params = array(
            'or' => $or,
            'purchase_id' => $this->purchase_id,
            'customer_id' => $this->customer_id,
            'hits' => $hits,
        );
        //一个品牌商下的所有产品
        $res=$this->productModel->checkStatus($params['customer_id']);

        //获取搜索的商品
        $model['list'] = $this->productModel->newitems($conArr, $serial_num, $params, $price, $page);
        $model['c_id'] = $c_id;
        $model['price'] = $price;
        $model['price_f'] = $price == 1 ? 2 : 1;
        $model['hits'] = $hits;
        $model['hits_f'] = $hits == 1 ? 2 : 1;
        $model['or'] = $or;

        if ($page > 1) {
            echo $this->renderPartial('ajaxindex', array('model' => $model, 'c_id' => $c_id, 'b_id' => $b_id,'res'=>$res));
        } else {
            //var_dump($model['list']);die;
            $this->render('index', array('model' => $model, 'c_id' => $c_id, 'b_id' => $b_id, 'serial_num' => $serial_num,'res'=>$res));
        }
    }

    /**
     * 商品详情
     */
    public function actionDetail()
    {
        $model_sn = $this->post('model_sn');
        $list = $this->productModel->detail($model_sn);
        $purchase_id = $this->purchase_id;
        $customer_id = $this->customer_id;
        $order = $this->productModel->getThisOrderedInfo($purchase_id, $customer_id, $model_sn);
        $total = 0;
        if (isset($order) && $order) {
            foreach ($order as $v) {
                if ($v['model_sn'] == $model_sn) $total += $v['nums'];
            }
        }
        // var_dump(array('list' => $list, 'total' => $total, 'order_items'=>$order));exit;
        echo $this->renderPartial('detail', array('list' => $list, 'total' => $total, 'order_items'=>$order));
    }
}

