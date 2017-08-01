<?php

/**
 * 订单管理
 * @author     chenfenghua<843958575@qq.com>
 * @copyright  Copyright 2008-2013 mall.octmami.com
 * @version    1.0
 */
class OrderController extends BaseController
{

    public $admin;

    public function __construct($id, $module)
    {
        parent::__construct($id, $module);
        $this->admin = Yii::app()->session['_admini'];
        $this->registerJs(
            array('jquery.uploadify'), 'end', 'bootstrap'
        );

        $this->registerJs(
            array(
                'date-time/bootstrap-datepicker.min',
                'date-time/bootstrap-timepicker.min',
                'date-time/moment.min',
                'date-time/daterangepicker.min',
                'bootstrap-colorpicker.min',
                'oct/sales/recommend',
                'layer/layer.min',
                'jquery.PrintArea',

            )
        );

    }

    /**
     * 订单列表
     */
    public function actionIndex()
    {
        $select_option = $this->filter();

        $pageIndex = isset($_GET['page']) ? $_GET['page'] : 1;

        $params = @$_GET['param'];
        isset($params['order']) ? isset($params['order']) : 'o.cost_item';
        $params['page'] = $pageIndex;

        $order = new Orders();

        $result = $order->orderCheckList($pageIndex, 15, $params);
        $listAmount = $order->orderCheckListAmount($params);//查询的订单数量

        $listAmountReally = $order->orderCheckListAmountReally($params);//查询所有确认的订单数量

        $amount = 0;
        if (!empty($listAmount['item'])) {
            foreach ($listAmount['item'] as $v) {
                $amount += $v['cost_item'];
            }
        }
        $statistics['amount'] = $amount;//预期已订货金额

        $amount_really = 0;
        if (!empty($listAmountReally['item'])) {
            foreach ($listAmountReally['item'] as $v) {
                $amount_really += $v['cost_item'];
            }
        }
        $statistics['amount_really'] = $amount_really;//实际已订货金额

        $customer = new Customer();
        $statistics['target_sum'] = $customer->getCustomerTargets($params);//总订货指标

        $statistics['choose_target_sum'] = $customer->getChooseCustomerTargets($params);//已选客户合并指标

        if (!empty($result['item'])) {
            foreach ($result['item'] as $k => $v) {
                $result['item'][$k]['xxydhje'] = $order->getAllPriceCount($v['code']);
                $order_log = $order->getOrderLog($v['order_id']);
                if ($order_log) {
                    $result['item'][$k]['check_time'] = date('Y-m-d H:i:s', $order_log['time']);
                    $result['item'][$k]['check_user'] = $order_log['name'];
                } else {
                    $result['item'][$k]['check_time'] = '';
                    $result['item'][$k]['check_user'] = '';
                }
            }
        }

        if (!empty($params['download'])) {
            $keys = array('客户/店铺名称', '客户/店铺代码', '订货会', '订货指标', '已订货金额', '达成率', '未完成金额', '下线已定货金额');
            $data = array();
            foreach ($result['item'] as $k => $v) {
                $data[$k]['A'] = $v['customer_name'];
                $data[$k]['B'] = $v['code'];
                $data[$k]['C'] = $v['purchase_id'] == 1 ? 'oct' : 'uki';
                $data[$k]['E'] = $v['target'];
                $data[$k]['F'] = $v['cost_item'];
                $data[$k]['G'] = number_format($v['rate'] * 100, 2) . "%";
                $data[$k]['H'] = $v['target'] - $v['cost_item'] <= 0 ? 0 : $v['target'] - $v['cost_item'];
                $data[$k]['I'] = $v['xxydhje'];
            }

            //总预期达成率
            $zyqdcl = 0.00;
            $target_sum = floatval($statistics['target_sum']);
            if (!empty($statistics['amount']) && !empty($target_sum)) {
                $zyqdcl = number_format($statistics['amount'] / $target_sum * 100, 2);
            }

            //实际达成率
            $zsjdcl = 0.00;
            if (!empty($statistics['amount_really']) && !empty($target_sum)) {
                $zsjdcl = number_format($statistics['amount_really'] / $target_sum * 100, 2);
            }
            //已选客户预期达成率
            $yxzyqdcl = 0.00;
            $choose_target_sum = floatval($statistics['choose_target_sum']);
            if (!empty($statistics['amount']) && !empty($choose_target_sum)) {
                $yxzyqdcl = number_format($statistics['amount'] / $choose_target_sum * 100, 2);
            }
            //实际达成率
            $yxzsjdcl = 0.00;
            if (!empty($statistics['amount_really']) && !empty($choose_target_sum)) {
                $yxzsjdcl = number_format($statistics['amount_really'] / $choose_target_sum * 100, 2);
            }

            $data2 = array(
                array('', ''),
                array('总订货指标', $statistics['target_sum']),
                array('预期已订货金额', number_format($statistics['amount'], 2)),
                array('预期达成率', $zyqdcl . "%"),
                array('实际已订货金额', number_format($statistics['amount_really'], 2)),
                array('实际达成率', $zsjdcl . "%"),
                array('', ''),
                array('已选客户合并指标', $statistics['choose_target_sum']),
                array('已选客户预期达成率', $yxzyqdcl . "%"),
                array('已选客户实际达成率', $yxzsjdcl . "%")
            );
            $filename = '订单导出筛选结果';
            $export = new Io_xls();
            $export->export_begin($keys, $filename, count($data));
            $export->export_rows($data);
            $export->export_rows($data2);
            $export->export_finish();
        } else {
            $pages = new CPagination($result['count']);
            $this->render('index', array(
                'result' => $result,
                'pages' => $pages,
                'pageIndex' => $pageIndex - 1,
                'params' => $params,
                'selectOption' => $select_option,
                'statistics' => $statistics
            ));
        }
    }

    public function filter()
    {
        //订货会：
        $purchase = new Purchase();
        $select_option['purchase'] = $purchase->getPurchase();

        //（渠道）客户类型：
        $customer = new Customer();
        $select_option['customer_type'] = $customer->getList('type', '', 'type');
        $select_option['customer_province'] = $customer->getList('province,area', '', 'province');
        $select_option['customer_area'] = $customer->getList('area', '', 'area');
        $select_option['customer_department'] = $customer->getList('department', '', 'department');
        $select_option['customer_leader'] = $customer->getList('leader', '', 'leader');
        $select_option['customer_leader_name'] = $customer->getList('leader_name', '', 'leader_name');
        $select_option['customer_agent'] = $customer->getList('agent', '', 'agent');
        $select_option['customer_name'] = $customer->getList('name', '', 'name');
        //大类：
        $cat_big = new Cat_big();
        $select_option['cat_big'] = $cat_big->getList();
        //中类：
        $cat_middle = new Cat_middle();
        $select_option['cat_middle'] = $cat_middle->getList();
        //小类：
        $cat_small = new Cat_small();
        $select_option['cat_small'] = $cat_small->getList();
        //季节：
        $season = new Season();
        $select_option['season'] = $season->getList();
        //波段：
        $wave = new Wave();
        $select_option['wave'] = $wave->getList();
        //等级：
        $level = new Level();
        $select_option['level'] = $level->getList();
        //色系：
        $scheme = new Scheme();
        $select_option['scheme'] = $scheme->getList();
        //价格带：
        $select_option['price_level'] = array(
            1 => '0-99',
            2 => '100-199',
            3 => '200-299',
            4 => '300-399',
            5 => '400-499',
            6 => '500-999',
            7 => '1000-1499',
            8 => '1500-2000',
            9 => '2000以上',
        );
        return $select_option;
    }


    public function  actionDialogue()
    {
        $style_sn = $this->get('style_sn');
        if (empty($style_sn)) {
            echo json_encode(array('code' => 400));
        }
        $product = new Product();

        $result = $product->getList($style_sn);
        if ($result) {
            $result['order_count'] = $product->getProductSizeOrder($style_sn);
        }

        echo json_encode(array('code' => 200, 'data' => $result));
    }

    public function  actionDetail()
    {
        $order_id = $this->get('order_id');

        $order = new Orders();
        $product = new Product();
        $order_model_sn = $order->orderProductModelSn($order_id);

        if (empty($order_model_sn)) {
            echo "此订单没有商品";
            die();
        }
        $order_info = $order->orderInfo($order_id);
        $result = array();
        foreach ($order_model_sn as $k => $v) {
            $size_arr = $product->getSizeArr($v['model_sn']);
            $color_arr = $product->getColorArr($v['model_sn']);
//            $result[$k]['color'] = $color_arr;
//            $result[$k]['size'] = $size_arr;
            $order_items = $product->getProductsCount($order_id, $v['model_sn']);
            foreach ($size_arr as $sk => $sv) {
                foreach ($color_arr as $ck => $cv) {
                    $result[$k]['norm'][$cv['color_name']][$sv['size_name']] = 0;
                    foreach ($order_items as $ik => $iv) {
                        $result[$k]['name'] = $iv['name'];
                        $result[$k]['wave_name'] = $iv['wave_name'];
                        $result[$k]['model_sn'] = $iv['model_sn'];
                        $result[$k]['size_name'][$sk] = $sv['size_name'];
                        $result[$k]['color_name'][$ck] = $cv['color_name'];
                        $result[$k]['img_url'] = $iv['img_url'];
                        $result[$k]['cost_price'] = $iv['cost_price'];
                        if ($iv['size_id'] == $sv['size_id'] && $iv['color_id'] == $cv['color_id']) {
                            $result[$k]['norm'][$cv['color_name']][$sv['size_name']] = $iv['nums'];
                        }
                    }
                }
            }
        }
        $data = array();
        $orderlist = $order->orderItemList($order_id);
        foreach ($orderlist as $k => $v) {
            $data[$v['style_sn']]['model_sn'] = $v['model_sn'];
            $data[$v['style_sn']]['name'] = $v['name'];
            $data[$v['style_sn']]['price'] = $v['price'];
            $data[$v['style_sn']]['color_name'] = $v['color_name'];
            $data[$v['style_sn']][$v['color_name']]['size_name'][$k]['size_name'] = $v['size_name'];
            $data[$v['style_sn']][$v['color_name']]['size_name'][$k]['nums'] = $v['nums'];
        }
        $count = $order->getCustomerNewCount($order_id);
        $size = new Size();
        $groupSize = $size->getGroupSize();
        $this->render('detail', array('result' => $result, 'order_info' => $order_info, 'orderlist' => $data, 'count'=>$count, 'sizeGroup'=>$groupSize));
    }
//    public function  actionDetail(){
//        $order_id = $this->get('order_id');
//
//        $order = new Orders();
//
//        $order_style_sn =$order->orderProductStyleSn($order_id);
//        if(empty($order_style_sn)){
//            echo "没有此订单";die();
//        }
//        $order_info =$order->orderInfo($order_id);
////        var_dump($order_style_sn);die();
//        $product = new Product();
//        foreach($order_style_sn as $k=> $v){
//            $result[$k] = $product->getList($v['style_sn']);
////            var_dump($result[$k] );
//            if($result){
//                $result[$k]['order_count'] = $product->getProductSizeOrderCustomer($v['style_sn'],$order_id);
//                if(!empty($result[$k]['order_count'])){
//                    $customer = $self = $all = 0;
//                    foreach($result[$k]['order_count'] as $kk=> $vv){
//
//                        $customer +=$vv['customer'];
//                        $self += $vv['self'];
//                        $all +=  $vv['customer'] +$vv['self'];
//
//                        $result[$k]['all'][$kk] = $vv['customer'] +$vv['self'];
//                    }
//
//                    $result[$k]['customer_count'] = $customer;
//                    $result[$k]['self_count'] = $self;
//                    $result[$k]['all_count'] = $all;
//                }
//
//            }
//
//        }
//
//
//        $this->render('detail',array('result'=>$result,'order_info'=>$order_info));
//    }

    /**
     * 订单统计
     */
    public function  actionStatistics()
    {
        $season_sp_id = Yii::app()->params['season_one'];
        $season_sm_id = Yii::app()->params['season_two'];

        $order_id = $this->get('order_id');//获取订单号
        $type = $this->get("type");
        $order = new Orders();
        $order_style_sn = $order->orderProductStyleSn($order_id);//获取购买的商品的商品编号
        if (empty($order_style_sn)) {
            echo "此订单没有商品";
            die();
        }
        $order_info = $order->orderInfo($order_id);//获取订购者信息
        $order_item = $order->orderItem($order_id);//获取商品信息

        $item = array();
        $item['model_s_1'] = $item['model_s_2'] = array();
        $item['num_s_1'] = $item['num_s_2'] = $item['amount_s_1'] = $item['amount_s_2'] = $item['all_num'] = $item['all_amount'] = 0;
        foreach ($order_item as $k => $v) {
            if ($v['season'] == $season_sp_id) {
                $item['model_s_1'][] = $v['model_sn'];
                $item['num_s_1'] += $v['nums'];
                $item['amount_s_1'] += $v['amount'];
            }
            if ($v['season'] == $season_sm_id) {
                $item['model_s_2'][] = $v['model_sn'];
                $item['num_s_2'] += $v['nums'];
                $item['amount_s_2'] += $v['amount'];
            }
            $item['all_num'] += $v['nums'];
            $item['all_amount'] += $v['amount'];
        }
        $item['target'] = $order_info['target'];
        if ($item['target'] == 0) {//达成率
            $item['target_percent'] = "0%";
        } else {
            $item['target_percent'] = round($item['all_amount'] / $item['target'] * 100, 2) . "%";
        }
        $cat = new Cat_big();
        $cat_arr = $cat->cat_big_small();
        foreach ($cat_arr as $k => $v) {//$v 是大类
            $cat_arr[$k]['target'] = $order_info['big_' . $v['big_cat_id']];
            $cat_arr[$k]['nums'] = $cat_arr[$k]['amount'] = 0;
            if (!isset($cat_arr[$k]['res_cat_num'])) $cat_arr[$k]['res_cat_num'] = 0;
            if (!isset($cat_arr[$k]['res_cat_amount'])) $cat_arr[$k]['res_cat_amount'] = 0;
            $cat_arr[$k]['res_num_season_1'] = $cat_arr[$k]['res_num_season_2'] = 0;
            $cat_arr[$k]['res_amount_season_1'] = $cat_arr[$k]['res_amount_season_2'] = 0;
            $cat_arr[$k]['res_style_season_1'] = $cat_arr[$k]['res_style_season_2'] = array();
            foreach ($v['cat_small'] as $kk => $vv) {//$vv 是小类
                $cat_arr[$k]['cat_small'][$kk]['cat_big_name'] = $v['big_cat_name'];
                $cat_arr[$k]['cat_small'][$kk]['style_season_1'] = array();
                $cat_arr[$k]['cat_small'][$kk]['style_season_2'] = array();
                $cat_arr[$k]['cat_small'][$kk]['total_style'] = array();
                if (!isset($cat_arr[$k]['cat_small'][$kk]['total_num'])) $cat_arr[$k]['cat_small'][$kk]['total_num'] = 0;
                if (!isset($cat_arr[$k]['cat_small'][$kk]['total_amount'])) $cat_arr[$k]['cat_small'][$kk]['total_amount'] = 0;
                if (!isset($cat_arr[$k]['cat_small'][$kk]['num_season_1'])) $cat_arr[$k]['cat_small'][$kk]['num_season_1'] = 0;
                if (!isset($cat_arr[$k]['cat_small'][$kk]['num_season_2'])) $cat_arr[$k]['cat_small'][$kk]['num_season_2'] = 0;
                if (!isset($cat_arr[$k]['cat_small'][$kk]['amount_season_1'])) $cat_arr[$k]['cat_small'][$kk]['amount_season_1'] = 0;
                if (!isset($cat_arr[$k]['cat_small'][$kk]['amount_season_2'])) $cat_arr[$k]['cat_small'][$kk]['amount_season_2'] = 0;
                foreach ($order_item as $kkk => $vvv) {//$vvv 订购商品的属性
                    if ($vvv['cat_b'] == $v['big_cat_id'] && $vvv['cat_s'] == $vv['small_id']) {
                        if ($vvv['season'] == $season_sp_id) {
                            $cat_arr[$k]['cat_small'][$kk]['style_season_1'][] = $vvv['model_sn'];
                            $cat_arr[$k]['cat_small'][$kk]['num_season_1'] += $vvv['nums'];
                            $cat_arr[$k]['cat_small'][$kk]['amount_season_1'] += $vvv['amount'];
                            $cat_arr[$k]['res_num_season_1'] += $vvv['nums'];//春季数量总计
                            $cat_arr[$k]['res_style_season_1'][] = $vvv['model_sn'];//春季总计
                            $cat_arr[$k]['res_amount_season_1'] += $vvv['amount'];//春季金额总计
                        }
                        if ($vvv['season'] == $season_sm_id) {
                            $cat_arr[$k]['cat_small'][$kk]['style_season_2'][] = $vvv['model_sn'];
                            $cat_arr[$k]['cat_small'][$kk]['num_season_2'] += $vvv['nums'];
                            $cat_arr[$k]['cat_small'][$kk]['amount_season_2'] += $vvv['amount'];
                            $cat_arr[$k]['res_num_season_2'] += $vvv['nums'];//夏季数量总计
                            $cat_arr[$k]['res_style_season_2'][] = $vvv['model_sn'];//夏季总计
                            $cat_arr[$k]['res_amount_season_2'] += $vvv['amount'];//春季数量总计
                        }
                        $cat_arr[$k]['cat_small'][$kk]['total_style'][] = $vvv['model_sn'];
                        $cat_arr[$k]['cat_small'][$kk]['total_num'] += $vvv['nums'];
                        $cat_arr[$k]['cat_small'][$kk]['total_amount'] += $vvv['amount'];

                        $cat_arr[$k]['res_cat_num'] += $vvv['nums'];//数量总计
                        $cat_arr[$k]['res_cat_amount'] += $vvv['amount'];//金额总计
                    }
                }
            }
            if ($cat_arr[$k]['target'] == 0) {
                $cat_arr[$k]['target_percent'] = "0%";
            } else {
                $cat_arr[$k]['target_percent'] = round($cat_arr[$k]['res_cat_amount'] / $cat_arr[$k]['target'] * 100, 2) . "%";
            }
        }
        if ($type == 'download') {
            //款式数量
            $model_s_1 = count(array_unique($item['model_s_1'])); //季节1款式数量
            $model_s_2 = count(array_unique($item['model_s_2'])); //季节2款式数量
            $model_all = $model_s_1 + $model_s_2; //款式数量总数
            //订货数量
            $num_s_1 = $item['num_s_1'];//季节1订货数量
            if ($item['all_num'] == 0) { //季节1订货占比
                $num_p_1 = "0%";
            } else {
                $num_p_1 = round($num_s_1 / $item['all_num'] * 100, 1) . "%";
            }
            $num_s_2 = $item['num_s_2'];//季节2订货数量
            if ($item['all_num'] == 0) {//季节2订货占比
                $num_p_2 = "0%";
            } else {
                $num_p_2 = round($num_s_2 / $item['all_num'] * 100, 1) . "%";
            }
            $num_all = $num_s_1 + $num_s_2;//订货数量总和
            if ($item['all_num'] == 0) {//订货总和占比
                $num_all_p = "0%";
            } else {
                $num_all_p = round($num_all / $item['all_num'] * 100, 1) . "%";
            }
            //订货金额
            $amount_s_1 = $item['amount_s_1'];//季节1订货金额
            if ($item['all_amount'] == 0) { //季节1订货金额占比
                $amount_p_1 = "0%";
            } else {
                $amount_p_1 = round($amount_s_1 / $item['all_amount'] * 100, 1) . "%";
            }
            $amount_s_2 = $item['amount_s_2'];//季节2订货金额
            if ($item['all_amount'] == 0) { //季节2订货金额占比
                $amount_p_2 = "0%";
            } else {
                $amount_p_2 = round($amount_s_2 / $item['all_amount'] * 100, 1) . "%";
            }
            $amount_all = $amount_s_1 + $amount_s_2;//订货金额总计
            if ($item['all_amount'] == 0) {
                $amount_p = "0%";
            } else {
                $amount_p = round($amount_all / $item['all_amount'] * 100, 1) . "%";
            }
            //订货指标
            $target = $order_info['target'];//订货指标
            if ($amount_all == 0) {//达成率
                $target_p = "0%";
            } else {
                $target_p = round($item['all_amount'] / $target * 100, 1) . "%";
            }
            $season_spring = Yii::app()->params['season_one_name'];
            $season_summer = Yii::app()->params['season_two_name'];
            $title_header = array(
                "大类", "小类", "{$season_spring}款式数量", "{$season_summer}款式数量", "{$season_spring}{$season_summer}款式数量合计",
                "{$season_spring}订货数量", "{$season_spring}订货数量占比", "{$season_summer}订货数量", "{$season_summer}订货数量占比",
                "{$season_spring}{$season_summer}订货数量合计", "{$season_spring}{$season_summer}订货数量合计占比", "{$season_spring}订货金额",
                "{$season_spring}订货金额占比", "{$season_summer}订货金额", "{$season_summer}订货金额占比", "{$season_spring}{$season_summer}订货金额",
                "{$season_spring}{$season_summer}订货金额占比", "订货指标", "订货指标达成率"
            );
            $title_content[0] = array(
                "订货总计", "", $model_s_1, $model_s_2, $model_all, $num_s_1, $num_p_1, $num_s_2, $num_p_2, $num_all, $num_all_p, $amount_s_1, $amount_p_1,
                $amount_s_2, $amount_p_2, $amount_all, $amount_p, $target, $target_p
            );
            $data_xls = array();
            $i = 1;
            foreach ($cat_arr as $k => $v) {
                foreach ($v['cat_small'] as $kk => $vv) {
                    $data_xls[$i]['A'] = $vv['cat_big_name'];
                    $data_xls[$i]['B'] = $vv['small_cat_name'];
                    $data_xls[$i]['C'] = count(array_unique($vv['style_season_1']));
                    $data_xls[$i]['D'] = count(array_unique($vv['style_season_2']));
                    $data_xls[$i]['E'] = ($data_xls[$i]['C'] + $data_xls[$i]['D']);
                    $data_xls[$i]['F'] = $vv['num_season_1'];
                    if ($num_all == 0) {
                        $data_xls[$i]['G'] = "0%";
                    } else {
                        $data_xls[$i]['G'] = round($data_xls[$i]['F'] / $num_all * 100, 1) . "%";
                    }
                    $data_xls[$i]['H'] = $vv['num_season_2'];
                    if ($num_all == 0) {
                        $data_xls[$i]['I'] = "0%";
                    } else {
                        $data_xls[$i]['I'] = round($data_xls[$i]['H'] / $num_all * 100, 1) . "%";
                    }
                    $data_xls[$i]['J'] = ($vv['num_season_1'] + $vv['num_season_2']);
                    if ($num_all == 0) {
                        $data_xls[$i]['K'] = "0%";
                    } else {
                        $data_xls[$i]['K'] = round($data_xls[$i]['J'] / $num_all * 100, 1) . "%";
                    }
                    $data_xls[$i]['L'] = $vv['amount_season_1'];
                    if ($amount_all == 0) {
                        $data_xls[$i]['M'] = "0%";
                    } else {
                        $data_xls[$i]['M'] = round($data_xls[$i]['L'] / $amount_all * 100, 1) . "%";
                    }
                    $data_xls[$i]['N'] = $vv['amount_season_2'];
                    if ($amount_all == 0) {
                        $data_xls[$i]['O'] = "0%";
                    } else {
                        $data_xls[$i]['O'] = round($data_xls[$i]['N'] / $amount_all * 100, 1) . "%";
                    }
                    $data_xls[$i]['P'] = $vv['amount_season_2'] + $vv['amount_season_1'];
                    if ($amount_all == 0) {
                        $data_xls[$i]['Q'] = "0%";
                    } else {
                        $data_xls[$i]['Q'] = round($data_xls[$i]['P'] / $amount_all * 100, 1) . "%";
                    }
                    $data_xls[$i]['R'] = "";
                    $data_xls[$i]['S'] = "";
                    $i++;
                }
                $data_xls[$i]['A'] = $v['big_cat_name'];
                $data_xls[$i]['B'] = "订货总计";
                $data_xls[$i]['C'] = count(array_unique($v['res_style_season_1']));
                $data_xls[$i]['D'] = count(array_unique($v['res_style_season_2']));
                $data_xls[$i]['E'] = $data_xls[$i]['C'] + $data_xls[$i]['D'];
                $data_xls[$i]['F'] = $v['res_num_season_1'];
                if ($num_all == 0) {
                    $data_xls[$i]['G'] = "0%";
                } else {
                    $data_xls[$i]['G'] = round($data_xls[$i]['F'] / $num_all * 100, 1) . "%";
                }
                $data_xls[$i]['H'] = $v['res_num_season_2'];
                if ($num_all == 0) {
                    $data_xls[$i]['I'] = "0%";
                } else {
                    $data_xls[$i]['I'] = round($data_xls[$i]['H'] / $num_all * 100, 1) . "%";
                }
                $data_xls[$i]['J'] = $v['res_num_season_1'] + $v['res_num_season_2'];
                if ($num_all == 0) {
                    $data_xls[$i]['K'] = "0%";
                } else {
                    $data_xls[$i]['K'] = round($data_xls[$i]['J'] / $num_all * 100, 1) . "%";
                }
                $data_xls[$i]['L'] = $v['res_amount_season_1'];
                if ($amount_all == 0) {
                    $data_xls[$i]['M'] = "0%";
                } else {
                    $data_xls[$i]['M'] = round($data_xls[$i]['L'] / $amount_all * 100, 1) . "%";
                }
                $data_xls[$i]['N'] = $v['res_amount_season_2'];

                if ($amount_all == 0) {
                    $data_xls[$i]['O'] = "0%";
                } else {
                    $data_xls[$i]['O'] = round($data_xls[$i]['N'] / $amount_all * 100, 1) . "%";
                }
                $data_xls[$i]['P'] = $v['res_amount_season_1'] + $v['res_amount_season_2'];
                if ($amount_all == 0) {
                    $data_xls[$i]['Q'] = "0%";
                } else {
                    $data_xls[$i]['Q'] = round($data_xls[$i]['P'] / $amount_all * 100, 1) . "%";
                }
                $data_xls[$i]['R'] = $v['target'];
                if($v['target'] == 0){
                    $data_xls[$i]['S'] = "0%";
                }else{
                    $data_xls[$i]['S'] = round($data_xls[$i]['P'] /$v['target'] * 100, 1). "%";
                }
                $i = $i + 2;
            }
            $title_end[0] = array(
                '订货指标：', ($order_info['target'] == 0) ? '' : number_format($order_info['target'], 2),
            );
            $title_end[1] = array(
                '已定货：', number_format($order_info['cost_item'], 2)
            );
            $title_end[2] = array(
                '达成率：', ($order_info['target'] == 0) ? '' : number_format($order_info['cost_item'] / $order_info['target'] * 100, 1) . "%"
            );
            $filename = $order_info['customer_name'] . "_" . $order_info['order_id'];
            $export = new Io_xls();
            $export->export_begin($title_header, $filename, count($title_content));
            $export->export_rows($title_content);
            $export->export_rows($data_xls);
            $export->export_rows($title_end);
            $export->export_finish();
        } else {
            $this->render('statistics', array('cat_arr' => $cat_arr, 'order_info' => $order_info, 'item' => $item));
        }
    }

    public function  actionCheck()
    {
        $order_id = $this->post('order_id');
        $status = $this->post('status');

        $admin = $this->admin;
        $name = $admin['name'];
        $user_id = $admin['user_id'];

        if (empty($order_id)) {
            echo json_encode(array('code' => '400'));
        } else {
            $order = new Orders();
            if ($order->updateOrderStatus($order_id, $status)) {
                echo json_encode(array('code' => '200'));

                $order->addLog($order_id, $status, $name, $user_id);
            } else {
                echo json_encode(array('code' => '400'));
            }
        }
    }

    public function  actionCopy()
    {
        $this->render('copy');
    }

    public function actionDoCopy()
    {
        if (empty($_POST['from']) || empty($_POST['to'])) {
            $this->breakAction('请填写客户编号');
        }
        $from = $this->post('from');
        $to = $this->post('to');

        $customer = new Customer();
        $order = new Orders();
        //确认客户信息
        $from_customer_info = $customer->getCustomerInfo($from);
        $to_customer_info = $customer->getCustomerInfo($to);
        if (empty($from_customer_info)) {
            $this->breakAction('没有被复制客户信息');
        }
        if (empty($to_customer_info)) {
            $this->breakAction('没有复制到客户信息');
        }
        //比较客户类型
        if ($from_customer_info['purchase_id'] !== $to_customer_info['purchase_id']) {
            $this->breakAction('两个客户类型不一致');
        }

        //获取被复制客户订单
        $from_order = $customer->getCustomerOrder($from_customer_info['customer_id']);
        if (!$from_order) {
            $this->breakAction('被复制客户没有订单');
        }
        //获取被复制客户订单商品
        $order_list = $order->orderItem($from_order['order_id']);
        if (!$order_list) {
            $this->breakAction('被复制客户订单没有商品');
        }

        //获取复制到客户订单
        $to_order = $customer->getCustomerOrder($to_customer_info['customer_id']);
        if ($to_order) {
            //获取复制到订单商品
            $to_order_list = $order->orderItem($to_order['order_id']);
            if ($to_order_list) {
                $this->breakAction('复制到客户订单存在商品，请先删除');
            }
        }

        //添加订单
        $order_id = $order->addOrder($from_order['purchase_id'], $to_customer_info['customer_id'], $to_customer_info['name'], $from_order['cost_item']);
        if (!$order_id) {
            $this->breakAction('复制订单失败');
        }
        //添加订单商品
        if ($order->addToOrderItem($order_id, $order_list)) {
            $this->okAction('复制订单成功');
        } else {
            $this->breakAction('复制订单失败');
        }

    }

    public function breakAction($msg)
    {
        echo json_encode(array('msg' => $msg, 'code' => '400'));
        die();
    }

    public function okAction($msg)
    {
        echo json_encode(array('msg' => $msg, 'code' => '200'));
        die();
    }


    public function actionDownloadOrderItems($order_id)
    {
        if (empty($order_id)) {
            echo "订单号不能为空";
        }
        $order = new Orders();
        $order_info = $order->orderInfo($order_id);//订货统计
        $order_item = $order->DownloadOrderItemList($order_id);//商品属性
        $keys = array(
            '订单号',
            '商品id',
            '商品的货号',
            '款色号',
            '型号',
            '商品名称',
            '商品单价',
            '总价',
            '数量',
            '型号id',
            '型号',
            '色系id',
            '色系',
            '颜色id',
            '颜色',
            '大类id',
            '大类',
            '中类id',
            '中类',
            '小类id',
            '小类',
            '季节id',
            '季节',
            '等级id',
            '等级',
            '商品详细',
            '品牌',
        );
        $data = array();
        foreach ($order_item as $k => $v) {
            $data[$k]['order_id'] = $v['order_id'];
            $data[$k]['product_id'] = $v['product_id'];
            $data[$k]['product_sn'] = $v['product_sn'];
            $data[$k]['style_sn'] = $v['style_sn'];
            $data[$k]['model_sn'] = $v['model_sn'];
            $data[$k]['name'] = $v['name'];
            $data[$k]['price'] = $v['price'];
            $data[$k]['amount'] = $v['amount'];
            $data[$k]['nums'] = $v['nums'];
            $data[$k]['size_id'] = $v['size_id'];
            $data[$k]['size_name'] = $v['size_name'];
            $data[$k]['scheme_id'] = $v['scheme_id'];
            $data[$k]['scheme_name'] = $v['scheme_name'];
            $data[$k]['color_id'] = $v['color_id'];
            $data[$k]['color_name'] = $v['color_name'];
            $data[$k]['big_id'] = $v['big_id'];
            $data[$k]['big_name'] = $v['big_name'];
            $data[$k]['middle_id'] = $v['middle_id'];
            $data[$k]['middle_name'] = $v['middle_name'];
            $data[$k]['small_id'] = $v['small_id'];
            $data[$k]['small_name'] = $v['small_name'];
            $data[$k]['season_id'] = $v['season_id'];
            $data[$k]['season_name'] = $v['season_name'];
            $data[$k]['level_id'] = $v['level_id'];
            $data[$k]['level_name'] = $v['level_name'];
            $data[$k]['memo'] = $v['memo'];
            $data[$k]['brand_name'] = $v['brand_name'];
        }
        $filename = $order_info['customer_name'] . $order_info['code'];
        $export = new Io_xls();
        $export->export_begin($keys, $filename, count($data));
        $export->export_rows($data);
        $export->export_finish();
    }


    public function actionDownloadOrderItems3($order_id)
    {
        if (empty($order_id)) {
            echo "订单号不能为空";
        }
        $order = new Orders();
        $order_info = $order->orderInfo($order_id);
        $order_item = $order->DownloadOrderItemList($order_id);
        $keys = array(
            '样品代码', '样品名称', '单位', '颜色', '颜色名称', '尺码', '尺码名称', '数量', '标准价', '折扣', '单价', '标准金额',
            '金额', '交货日期', '订货状态', '备注', '关联分销订单', '品牌代码', '品牌名称',
            //这里开始 都是未定义
            '大类', '中类', '小类', '面料', '种类划分', '款式划分', '波段划分'
        );

        $data = array();
        foreach ($order_item as $k => $v) {
            $data[$k]['model_sn'] = $v['model_sn'];
            $data[$k]['name'] = $v['name'];
            $data[$k]['dan_wei'] = " ";
            $data[$k]['color_no'] = $v['color_no'];
            $data[$k]['color_name'] = $v['color_name'];
            $data[$k]['size_no'] = $v['size_no'];
            $data[$k]['size_name'] = $v['size_name'];
            $data[$k]['nums'] = $v['nums'];  //数量
            $data[$k]['price'] = $v['price'];//标准价
            $data[$k]['discount'] = '1';     //折扣
            $data[$k]['Standard_price'] = $v['price']; //单价
            $data[$k]['Standard_amount'] = $v['amount'];  //标准金额
            $data[$k]['amount'] = $v['amount']; //金额

            $data[$k]['date'] = "2015-09-19";
            $data[$k]['status'] = "0";
            $data[$k]['remark'] = " ";
            $data[$k]['order_id'] = " ";
            $data[$k]['brand_id'] = $v['brand_id'];
            $data[$k]['brand_name'] = $v['brand_name'];

            $data[$k]['A'] = '未定义';
            $data[$k]['B'] = '未定义';
            $data[$k]['C'] = '未定义';
            $data[$k]['D'] = '未定义';
            $data[$k]['E'] = '未定义';
            $data[$k]['F'] = '未定义';
            $data[$k]['G'] = '未定义';
        }

        $order_information = array(
            array('合计', '', '', '', '', '', '', $order_info['nums'], '', '', '', $order_info['cost_item'], $order_info['cost_item'], '', '', '', '', '', '', '', '', '', '', '', '', ''),
            array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
            array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
            array('DHJ', '订货会订单:' . $order_id),
            array('DJBH', '单据编号:'),
            array('RQ', '日期:' . date('Y-m-d H:i:s', $order_info['create_time'])),
            array('YDJH', '原单号:'),
            array('QDDM', '渠道:'),
            array('DM2', '仓库:'),
            array('DM2_1', '库位:'),
            array('DM1', '客户:'),
            array('DM3', '关联仓库:'),
            array('DM3_1', '关联库位:'),
            array('BYZD1', '价格选定:'),
            array('DM1_1', '折扣类型:'),
            array('BYZD12', '折扣:'),
            array('YGDM', '业务员:'),
            array('DM4', '订货会:' . $order_info['purchase_name']),
            array('DM4_1', '订单类型:'),
            array('YXRQ', '交货日期:2015-09-19'),
            array('BYZD7', '品牌:'),
            array('BYZD8', '定金类别:1'),
            array('BYZD9', '定金比:0.3'),
            array('BYZD10', '定金:0'),
            array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
            array('1', '标准', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
        );
        $filename = $order_info['customer_name'] . $order_info['code'];
        $export = new Io_xls();
        $export->export_begin($keys, $filename, count($data));
        $export->export_rows($data);
        $export->export_rows($order_information);
        $export->export_finish();
    }


    /**
     * 客户订单下载
     *
     * @param $order_id
     */
    public function actionDownloadOrderItems2($order_id)
    {
        if (empty($order_id)) {
            echo "订单号不能为空";
        }
        $order = new Orders();
        $order_info = $order->orderInfo($order_id);

        $order_item = $order->DownloadOrderItemList($order_id);
        $keys = array(
            '样品代码', '样品名称', '单位', '颜色', '颜色名称', '尺码', '尺码名称', '数量', '标准价', '折扣', '单价', '标准金额',
            '金额', '交货日期', '订货状态', '备注', '关联分销订单', '品牌代码', '品牌名称',
            //这里开始 都是未定义
            '大类', '中类', '小类', '面料', '种类划分', '款式划分', '波段划分'
        );
        $count_cost = 0;
        $data = array();
        foreach ($order_item as $k => $v) {
            $data[$k]['model_sn'] = $v['model_sn'];
            $data[$k]['name'] = $v['name'];
            $data[$k]['dan_wei'] = " ";
            $data[$k]['color_no'] = $v['color_no'];
            $data[$k]['color_name'] = $v['color_name'];
            $data[$k]['size_no'] = $v['size_no'];
            $data[$k]['size_name'] = $v['size_name'];
            $data[$k]['nums'] = $v['nums'];  //数量
            $data[$k]['price'] = $v['cost_price'];//标准价
            $data[$k]['discount'] = round($order_info['big_'.$v['cat_b'].'_count'] / 100, 2);     //折扣
            $data[$k]['Standard_price'] = round($v['cost_price'] * $order_info['big_'.$v['cat_b'].'_count'] / 100, 2); //单价
            $data[$k]['Standard_amount'] = round($v['amount'] * $order_info['big_'.$v['cat_b'].'_count'] / 100, 2);  //标准金额
            $data[$k]['amount'] = round($v['amount'] * $order_info['big_'.$v['cat_b'].'_count'] / 100, 2); //金额
            $data[$k]['date'] = "2016-03-19";
            $data[$k]['status'] = "0";
            $data[$k]['remark'] = " ";
            $data[$k]['order_id'] = " ";
            $data[$k]['brand_id'] = $v['brand_id'];
            $data[$k]['brand_name'] = $v['brand_name'];
            $data[$k]['A'] = $v['big_name'];//大类
            $data[$k]['B'] = $v['middle_name'];//中类
            $data[$k]['C'] = $v['small_name'];//小类
            $data[$k]['D'] = $v['memo'];//面料
            $data[$k]['E'] = $v['scheme_name'];//种类划分
            $data[$k]['F'] = $v['level_name'];//款式划分
            $data[$k]['G'] = $v['wave_name'];//波段划分
            $count_cost += $data[$k]['amount'];
        }

        $order_information = array(
            array('DHJ', '订货会订单:' . $order_id),
            array('DJBH', '单据编号:'),
            array('RQ', '日期:' . date('Y-m-d H:i:s', $order_info['create_time'])),
            array('YDJH', '原单号:'),
            array('QDDM', '渠道:'),
            array('DM2', '仓库:'),
            array('DM2_1', '库位:'),
            array('DM1', '客户:'),
            array('DM3', '关联仓库:'),
            array('DM3_1', '关联库位:'),
            array('BYZD1', '价格选定:'),
            array('DM1_1', '折扣类型:'),
            array('BYZD12', '折扣:'),
            array('YGDM', '业务员:'),
            array('DM4', '订货会:' . $order_info['purchase_name']),
            array('DM4_1', '订单类型:'),
            array('YXRQ', '交货日期:2016-03-19'),
            array('BYZD7', '品牌:'),
            array('BYZD8', '定金类别:1'),
            array('BYZD9', '定金比:0.3'),
            array('BYZD10', '定金:0'),
            array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
            array('1', '标准', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
        );
        $data_sum = array(
            array('合计', '', '', '', '', '', '', $order_info['nums'], '', '', '', $count_cost, $count_cost, '', '', '', '', '', '', '', '', '', '', '', '', ''),
            array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
            array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
        );
        $filename = $order_info['customer_name'] . $order_info['code'];
        $export = new Io_xls();
        $export->download($filename . '.xls');
        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><style>td{vnd.ms-excel.numberformat:@}</style></head>';
        echo '<table width="100%" border="1">';
        $export->export_rows($order_information);
        echo '<tr><th filter=all>' . implode('</th><th filter=all>', $keys) . "</th></tr>\r\n";
        flush();
        $export->export_rows($data);
        $export->export_rows($data_sum);
        $export->export_finish();
    }

    public function actionDownloadAllItems()
    {
        $order = new Orders();
        $order_ids = $order->getAllOrderId();
        if (empty($order_ids)) {
            echo "暂无订单";
            die();
        }
        $keys = array(
            '样品代码', '样品名称', '单位', '颜色', '颜色名称', '尺码', '尺码名称', '数量', '标准价', '折扣', '单价', '标准金额',
            '金额', '交货日期', '订货状态', '备注', '关联分销订单', '品牌代码', '品牌名称',
            //这里开始 都是未定义
            '大类', '中类', '小类', '面料', '种类划分', '款式划分', '波段划分'
        );

        $filename = '所有商品';
        $export = new Io_xls();
        $export->export_begin($keys, $filename, 0);

        foreach ($order_ids as $v) {
            $order_info = $order->orderInfo($v['order_id']);
            $order_item = $order->DownloadOrderItemList($v['order_id']);

            $data = array();
            foreach ($order_item as $k => $v) {
                $data[$k]['model_sn'] = $v['model_sn'];
                $data[$k]['name'] = $v['name'];
                $data[$k]['dan_wei'] = " ";
                $data[$k]['color_no'] = $v['color_no'];
                $data[$k]['color_name'] = $v['color_name'];
                $data[$k]['size_no'] = $v['size_no'];
                $data[$k]['size_name'] = $v['size_name'];
                $data[$k]['nums'] = $v['nums'];  //数量
                $data[$k]['price'] = $v['price'];//标准价
                $data[$k]['discount'] = '1';     //折扣
                $data[$k]['Standard_price'] = $v['price']; //单价
                $data[$k]['Standard_amount'] = $v['amount'];  //标准金额
                $data[$k]['amount'] = $v['amount']; //金额

                $data[$k]['date'] = "2016-03-19";
                $data[$k]['status'] = "0";
                $data[$k]['remark'] = " ";
                $data[$k]['order_id'] = " ";
                $data[$k]['brand_id'] = $v['brand_id'];
                $data[$k]['brand_name'] = $v['brand_name'];


                $data[$k]['A'] = '未定义';
                $data[$k]['B'] = '未定义';
                $data[$k]['C'] = '未定义';
                $data[$k]['D'] = '未定义';
                $data[$k]['E'] = '未定义';
                $data[$k]['F'] = '未定义';
                $data[$k]['G'] = '未定义';
            }
            $export->export_rows($data);

        }
        $order_information = array(
            array('合计', '', '', '', '', '', '', $order_info['nums'], '', '', '', $order_info['cost_item'], $order_info['cost_item'], '', '', '', '', '', '', '', '', '', '', '', '', ''),
            array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
            array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
            array('DHJ', '订货会订单:'),
            array('DJBH', '单据编号:'),
            array('RQ', '日期:' . date('Y-m-d H:i:s', $order_info['create_time'])),
            array('YDJH', '原单号:'),
            array('QDDM', '渠道:'),
            array('DM2', '仓库:'),
            array('DM2_1', '库位:'),
            array('DM1', '客户:'),
            array('DM3', '关联仓库:'),
            array('DM3_1', '关联库位:'),
            array('BYZD1', '价格选定:'),
            array('DM1_1', '折扣类型:'),
            array('BYZD12', '折扣:'),
            array('YGDM', '业务员:'),
            array('DM4', '订货会:'),
            array('DM4_1', '订单类型:'),
            array('YXRQ', '交货日期:2016-03-19'),
            array('BYZD7', '品牌:'),
            array('BYZD8', '定金类别:1'),
            array('BYZD9', '定金比:0.3'),
            array('BYZD10', '定金:0'),
            array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
            array('1', '标准', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
        );

        $export->export_rows($order_information);
        $export->export_finish();
    }


    public function _utf82gbk(&$string)
    {
        if (empty ($string))
            return $string;

        $string = str_replace("\r", "", $string);
        $string = str_replace("\n", " ", $string);

        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, 'gbk', 'utf-8');
        } else {
            return iconv('utf-8', 'gbk', $string);
        }
        return $string;
    }

    /**
     * 导入excel页面
     */
    public function actionImport()
    {
        if(!Yii::app()->params['order_include']){
            echo "502 forbidden";die;
        }
        header("Content-type: text/html; charset=utf-8");
        $this->render('import');
    }

    /**
     * ajax获取用户信息
     */
    public function actionAjaxThisCustomerExist()
    {
        $code = $this->get("code");
        if (empty($code)) {
            die;
        }
        $productModel = new Product();
        $res = $productModel->selectQueryRow("*", "{{customer}}", "code='{$code}'");
        if (empty($res)) {
            echo json_encode(array('code' => '400'));
            die;
        } else {
            if ($res['purchase_id'] == 1) {
                $res['purchase_id'] = "OCT";
            } elseif ($res['purchase_id'] == 2) {
                $res['purchase_id'] = "UKI";
            }
            $result = $productModel->selectQueryRow("SUM(i.nums) AS nums", "{{order}} AS o LEFT JOIN {{order_items}} AS i ON i.order_id=i.order_id", "o.customer_id='{$res['customer_id']}'");
            if ($result['nums'] >= 1) {
                $res['otype'] = "追加";
            } else {
                $res['otype'] = "新增";
            }
            echo json_encode(array('code' => '200', 'data' => $res));
        }
    }

    /**
     * 上传检查CSV文件
     */
    public function actionImportFiles()
    {
        header("Content-type: text/html; charset=utf-8");
        $customer_id = $this->post("code");
        if (empty($customer_id)) {
            echo "<script>alert('请输入客户代码');location.href='/admin.php?r=order/order/import'</script>";
            die;
        }

        $postFile = isset($_FILES["file"]) ? $_FILES['file'] : exit("请上传文件");

        $postFileType = pathinfo($postFile['name'], PATHINFO_EXTENSION);
        $allowExt = array('csv');
        if (empty($postFile)) {
            echo "<script>alert('请上传文件');location.href='/admin.php?r=order/order/import'</script>";
            die;
        }

        if (!in_array($postFileType, $allowExt)) {
            echo "<script>alert('上传文件不支持类型，仅限传csv后缀名文件,请先下载导入模板再执行操作');location.href='/admin.php?r=order/order/import'</script>";
            die;
        }

        if (!is_uploaded_file($postFile['tmp_name'])) {
            echo "<script>alert('不是通过HTP POST上传的文件');location.href='/admin.php?r=order/order/import'</script>";
            die;
        }

        $tableModel = new Table();
        $c_info = $tableModel->ModelQueryRow("SELECT name,purchase_id,code,mobile,type,province FROM {{customer}} WHERE customer_id='{$customer_id}' AND disabled='false'");

        $nowTime = time();
        $newFileName = $nowTime . "." . $postFileType;
        $newFolder = date("Ymd", time());
        $transData = $newFolder . "/" . $newFileName;
        $newFolderPath = "images/" . $newFolder . "/";//新地址
        if (!file_exists($newFolderPath)) mkdir($newFolderPath, 0777);
        $newFile = Yii::app()->basePath . "/../" . $newFolderPath . "/" . $newFileName;
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $newFile)) {
            $handle = fopen($newFile, 'r');
            $result = ErpCsv::input_csv($handle);
            $len_result = count($result);
            if ($len_result <= 1) {
                echo "<script>alert('对不起，您的文件中没有数据，请检查');location.href='/admin.php?r=order/order/import'</script>";
                die;
            }
            $arr = array();
            $data_str = "";
            $color_list = $tableModel->colorList();
            $size_list = $tableModel->sizeList();
            for ($i = 1; $i < $len_result; $i++) {
                $data = "";
                $model_sn = intval($result[$i][0]);
                if (empty($model_sn)) {
                    $data .= "<span>款号的<b>" . $result[$i][0] . "</b>不是数字</span>";
                } else {
                    $res = $tableModel->getProductList($model_sn);
                    if ($res['nums'] <= 0) {
                        $data .= "<span>款号的<b>" . $result[$i][0] . "</b>有问题</span>";
                    }
                }

                $color_id = @$color_list[$result[$i][1]]['color_id'];
                if (empty($color_id)) {
                    $data .= "<span>颜色的<b>" . $result[$i][1] . "</b>有问题</span>";
                }

                $size_id = @$size_list[$result[$i][2]]['size_id'];
                if (empty($size_id)) {
                    $data .= "<span>尺码的<b>" . $result[$i][2] . "</b>有问题</span>";
                }

                $num = intval($result[$i][3]);
                if (empty($num)) {
                    $data .= "<span>数量的<b>" . $result[$i][3] . "</b>有问题</span>";
                }

                $sql = "SELECT COUNT(*) AS nums FROM {{product}} WHERE model_sn='{$model_sn}' AND purchase_id='{$c_info['purchase_id']}' AND color_id='{$color_id}' AND size_id='{$size_id}' AND is_down='0' AND disabled='false' AND is_error='false'";
                $count = $tableModel->ModelQueryRow($sql);
                if ($count['nums'] <= 0) {
                    $data .= "<b>款号对应的颜色、尺寸的商品不存在或者已下架，请重新添加</b>";
                }

                if (empty($data)) {
                    $arr[$model_sn."_".$color_id."_".$size_id] = $model_sn."_".$color_id."_".$size_id;
                } else {
                    $data_str .= "<p>第 " . ($i + 1) . " 行的 " . $data . "</p> ";
                }
            }

            if(empty($data_str)){
                if(($len_result -1) != count($arr)){
                    $data_str .= "<p class='text-danger'><b>系统显示：款号，色号，尺码有重复，请检查后合并订单然后再次上传！</b></p>";
                }
            }

            $product_list = array();
            if (empty($data_str)) {
                $product_list = $data_str;
            }

            $this->render('importdetail', array(
                'customer' => $customer_id,
                'c_info' => $c_info,
                'file' => $transData,
                'data' => $data_str,
                'product_list' => $product_list,
            ));
        } else {
            echo "<script>alert('上传失败');location.href='/admin.php?r=order/order/import'</script>";
            die;
        }
    }

    /**
     * 刷新价格
     * @param $model_sn
     * @param $customer_id
     * @return string
     */
    public function getNewPrice($model_sn, $customer_id)
    {
        $productModel = new Product();
        //获取最新价格
        $product_info = $productModel->selectQueryRow("cost_price", "{{product}}", " model_sn='{$model_sn}' AND is_down='0' AND disabled='false'");
        $allPrice = $productModel->selectQueryRows("i.nums,i.model_sn,i.item_id,o.order_id", "{{order_items}} AS i LEFT JOIN {{order}} AS o ON o.order_id=i.order_id", " i.model_sn='{$model_sn}' AND o.customer_id='{$customer_id}' AND i.disabled='false'");
        $sql = "";
        foreach ($allPrice as $val) {
            $item['price'] = $product_info['cost_price'];
            $amount = $product_info['cost_price'] * $val['nums'];
            $sql .= "UPDATE {{order_items}} SET price='{$product_info['cost_price']}', amount='{$amount}' WHERE item_id='{$val['item_id']}' AND model_sn='{$val['model_sn']}' AND order_id='{$val['order_id']}' AND disabled='false';";
        }
        if (empty($sql)) return "";
        $productModel->ModelExecute($sql);
    }


    /**
     * 刷总价格
     * @param $customer_id
     * @return mixed
     */
    public function getNewAmount($customer_id)
    {
        $productModel = new Product();
        $sql = "SELECT SUM(i.amount) AS count FROM {{order}} AS o LEFT JOIN {{order_items}} AS i ON o.order_id=i.order_id WHERE o.customer_id='{$customer_id}' AND o.disabled='false' AND i.disabled='false'";
        $result = $productModel->QueryRow($sql);
        $params = array(
            'cost_item' => $result['count'],
            'edit_time' => time(),
        );
        $productModel->ModelThisEdit("{{order}}", "customer_id='{$customer_id}' AND disabled='false'", $params);
    }

    /**
     * 导入CVS数据
     */
    public function actionUploadingCsvData()
    {
        $param = $this->post("param");
        if (!isset($param['file']) || !isset($param['customer_id'])) {
            echo "<script>alert('数据不存在！');location.href='/admin.php?r=order/order/import'</script>";
            die;
        }

        //再次判断用户是否存在
        $productModel = new Product();
        $customer_info = $productModel->selectQueryRow("purchase_id,name,customer_id", "{{customer}}", "customer_id='{$param['customer_id']}'");
        if (!$customer_info) {
            echo "<script>alert('用户不存在！');location.href='/admin.php?r=order/order/import'</script>";
            die;
        }

        //尺码，颜色转换
        $tableModel = new Table();
        $color_list = $tableModel->colorList();
        $size_list = $tableModel->sizeList();

        //打开csv的相关操作
        $filePath = 'images/' . $param['file'];
        $handle = fopen($filePath, 'r');
        $result = ErpCsv::input_csv($handle);
        $len_result = count($result);

        //检查此用户是否存在订单
        $order = new Orders();
        $order_info = $productModel->selectQueryRow("*", "{{order}}", "customer_id='{$customer_info['customer_id']}'");
        if (empty($order_info)) {
            //创建订单，插入数据
            $order_id = $order->build_order_no();
            $params = array(
                'order_id' => $order_id,
                'purchase_id' => $customer_info['purchase_id'],
                'customer_id' => $customer_info['customer_id'],
                'customer_name' => $customer_info['name'],
                'create_time' => time(),
                'cost_item' => '0',
                'status' => 'active',
            );
            $productModel->ModelInsert("{{order}}", $params);
        } else {
            //获取订单id
            $order_id = $order_info['order_id'];
        }
        $updateSql = "";
        $fullSql = "";
        $values = "";
        for ($i = 1; $i < $len_result; $i++) {
            $model_sn = $result[$i][0];
            $color_id = $color_list[$result[$i][1]]['color_id'];
            $size_id = $size_list[$result[$i][2]]['size_id'];
            $num = intval($result[$i][3]);
            //用户导入的商品详情
            $product_info = $productModel->selectQueryRow("name,product_sn,style_sn,cost_price,product_id,model_sn", "{{product}}", "model_sn='{$model_sn}' AND color_id='{$color_id}' AND size_id='{$size_id}' AND is_down='0' AND disabled='false'");
            //检查订单 查看用户是否购买过此商品
            $ordered_lists = $productModel->selectQueryRow("item_id,nums,amount", "{{order_items}}", "order_id='{$order_id}' AND style_sn='{$product_info['style_sn']}' AND product_sn='{$product_info['product_sn']}' AND disabled='false'");
            if (!empty($ordered_lists)) {
                $nowNum = $ordered_lists['nums'] + $num;
                //需要修改的订单详情
                $updateSql .= "UPDATE {{order_items}} SET nums='{$nowNum}',import='true' WHERE item_id='{$ordered_lists['item_id']}';";
            } else {
                $res['nums'] = $num;
                $res['order_id'] = $order_id;
                $res['amount'] = sprintf("%.2f", $num * $product_info['cost_price']);
                $res['import'] = "true";
                $res['product_id'] = $product_info['product_id'];
                $res['product_sn'] = $product_info['product_sn'];
                $res['style_sn'] = $product_info['style_sn'];
                $res['name'] = $product_info['name'];
                $res['model_sn'] = $product_info['model_sn'];
                $res['price'] = sprintf("%.2f", $product_info['cost_price']);
                unset($res['cost_price']);
                //要插入新的商品订单的 key
                $key = $order->ModelInsertKey($res);
                //要插入的新的商品订单 的values
                $values .= $order->ModelInsertValue($res);
            }
            $model_list[$model_sn] = $model_sn;
        }

        //再次判断是否有新增的商品款号
        if(empty($model_list)){
            echo "<script>alert('未检测到任何数据，请重试！');location.href='/admin.php?r=order/order/import'</script>";
            die;
        }

        if (!empty($values)) {
            $values = substr($values, 1);
            //准备插入数据的sql
            $fullSql .= "INSERT INTO {{order_items}} ({$key}) VALUES {$values};";
        }

        if (!empty($updateSql)) {
            $fullSql .= $updateSql;
        }

        //执行sql
        if ($order->ModelExecute($fullSql)) {
            foreach ($model_list as $val) {
                //刷价格
                $this->getNewPrice($val, $customer_info['customer_id']);
            }
            //刷总价
            $this->getNewAmount($customer_info['customer_id']);
            $manage = new GuestManage();
            $nowTime = time();
            $manage->import_log($filePath, $nowTime, 'order', $order_id);
            echo "<script>alert('添加成功');location.href='/admin.php?r=order/order/index'</script>";
        } else {
            echo "<script>alert('添加失败');location.href='/admin.php?r=order/order/import'</script>";
            die;
        }
    }

    /**
     * 导出 按客户代码导出
     */
    public function actionExportMaster()
    {
        $productModel = new Product();
        $res = $productModel->exportMasterAndSlave();
        $count_target = $productModel->getCustomerOrderInfo();
        $amount = 0;
        $target = 0;
        foreach($res as $val){
            $amount += $val['count'];
            $target += $val['target'];
        }
        $xls = new Io_xls();
        $key = array(
            '客户/店铺名称', '客户/店铺代码', '订货会', '订货指标', '已订货金额', '达成率', '未完成金额', '下线已定货金额',
        );
        $fileName = "客户订单(客户合并)";
        $xls->export_begin($key, $fileName, 0);
        $i = 1;
        $order = new Orders();
        foreach ($res as $val) {
            $data[$i]['A'] = $val['name'];
            $data[$i]['B'] = $val['code'];
            $data[$i]['C'] = $val['purchase_name'];
            $data[$i]['D'] = $val['target'];
            $data[$i]['E'] = $val['count'];
            if ($val['target'] == '0') {
                $data[$i]['F'] = '0%';
            } else {
                $data[$i]['F'] = round($val['count'] / $val['target'] * 100, 2) . "%";
            }
            $data[$i]['G'] = $val['target'] - $val['count'];
            if($data[$i]['G'] <= 0){
                $data[$i]['G'] = 0;
            }
            $data[$i]['H'] = $order->getMasterCount($val['code']);
            $xls->export_rows($data);
        }
        $params[] = array('','');
        $params[] = array('订单总指标', isset($count_target['des_target'])?$count_target['des_target']:0);

        $params[] = array('预期已订货金额',$amount);
        if($count_target['des_target'] == 0){
            $prePercent = 0 ."%";
        }else{
            $prePercent = round($amount / $count_target['des_target'] * 100, 2)."%";
        }
        $params[] = array('预期达成率',$prePercent);

        $params[] = array('实际已订货金额',isset($count_target['fin_target'])?$count_target['fin_target'] :0);
        if($count_target['des_target'] == 0){
            $factPercent = 0 ."%";
        }else{
            $factPercent = round( $count_target['fin_target'] / $count_target['des_target']   * 100, 2)."%";
        }
        $params[] = array('实际达成率',$factPercent);
        $xls->export_rows($params);
        $xls->export_finish();
    }

    /**
     * 显示修改价格的商品
     */
    public function actionDiffer(){
        $order_id = $this->get("order_id");
        $order = new Orders();
        $result = $order->getThisOrderDifferent($order_id);
        if(!empty($result) && !empty($result['new'])){
            $res = $order->showDifferentProduct($result);
            $this->render('differ', array('result'=>$res, 'price'=>$result));
        }else{
            echo "<script>alert('暂无');location.href='/admin.php?r=order/order/index'</script>";
        }
    }

    /**
     * 折扣价格导出
     */
    public function actionDiscount(){
        $big_id = $this->get('type');
        $order = new Orders();
        $result = $order->getAllCustomerDiscount($big_id);
        $cat_big = new Cat_big();
        $big_name = $cat_big->getCatBig();
        foreach($big_name as $val){
            $big[$val['big_id']] = $val['cat_name'];
        }
        $xls = new Io_xls();
        $key = array(
            '客户名称', '订货会', '客户代码', $big[$big_id].'订货指标', $big[$big_id].'已订货金额', '折扣', $big[$big_id].'折扣后价格', '折扣后'.$big[$big_id].'订货指标完成率'
        );
        $fileName = '客户'.$big[$big_id].'折扣导出';
        $xls->export_begin($key, $fileName, 0);
        $i = 1;
        $all_big_target = 0; //该大类的指标
        $all_bro_money = 0; //实际已买的金额
        $all_dis_money = 0; //折扣后的价格
        foreach($result as $val){
            $data[$i]['A'] = $val['name'];
            $data[$i]['B'] = $val['purchase_id']==1?'OCT':'UKI';
            $data[$i]['C'] = $val['code'];
            $data[$i]['D'] = $val['starget'];
            $data[$i]['E'] = $val['amount'];
            $data[$i]['F'] = $val['discount'];
            $data[$i]['G'] = $val['final_amount'];
            $data[$i]['H'] = $val['percent'];
            $all_bro_money += $val['amount'];
            $all_big_target += $val['starget'];
            $all_dis_money += $val['final_amount'];
            $xls->export_rows($data);
        }
        if(empty($all_big_target)){
            $bro_percent = 0 .'%';
            $dis_percent = 0 .'%';
        }else{
            $bro_percent = round($all_bro_money/$all_big_target * 100 , 2) .'%';
            $dis_percent = round($all_dis_money/$all_big_target * 100 , 2) .'%';
        }
        if(empty($all))
        $params[] = array('','');
        $params[] = array($big[$big_id].'订货指标', $all_big_target);
        $params[] = array('实际已买'.$big[$big_id].'金额', $all_bro_money);
        $params[] = array('实际已买金额占'.$big[$big_id].'订货指标', $bro_percent);
        $params[] = array($big[$big_id].'折扣后金额', $all_dis_money);
        $params[] = array('实际已买折扣金额占'.$big[$big_id].'订货指标', $dis_percent);
        $xls->export_rows($params);
        $xls->export_finish();
    }

    /**
     * 所有订单明细
     *
     * @throws CException
     * @throws Exception\
     */
    public function actionDownloadOrderItemsInOrderItemsTable(){
        set_time_limit(0);
        $order = new Orders();
        $customer_info = $order->getAllUserOrderItems();
        $model_info    = $order->getProductModelSnAndCatBig();
        $order_info = $order->getAllOrderItemsList();

//        //xls
        Yii::$enableIncludePath = false; // 不自动加载
        Yii::import('application.extensions.PHPExcel', 1);
        $objPHPExcel = new PHPExcel();
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '商品代码')
            ->setCellValue('B1', '颜色编码')
            ->setCellValue('C1', '尺寸编码')
            ->setCellValue('D1', '数量')
            ->setCellValue('E1', '吊牌价')
            ->setCellValue('F1', '折扣')
            ->setCellValue('G1', '客户代码')
            ->setCellValue('H1', '订单编号');
        $i = 2;
        foreach($order_info as $k => $v){
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $i, $v['model_sn'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getNumberFormat()->setFormatCode("@");
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $i, $v['color_no'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getNumberFormat()->setFormatCode("@");
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $i, $v['size_no'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('C'.$i)->getNumberFormat()->setFormatCode("@");
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $i, $v['nums'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('D'.$i)->getNumberFormat()->setFormatCode("@");
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $i, $v['cost_price'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('E'.$i)->getNumberFormat()->setFormatCode("@");
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('F' . $i, ($customer_info[$v['order_id']]['big_'.$model_info[$v['model_sn']].'_count'] /100),PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('F'.$i)->getNumberFormat()->setFormatCode("@");
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $i, $v['code'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('G'.$i)->getNumberFormat()->setFormatCode("@");
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('H' . $i, $v['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('H'.$i)->getNumberFormat()->setFormatCode("@");
            $i++;
        }
        $name = '订单信息';
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$name.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
//        $xls = new Io_xls();
//        $key = array(
//            '商品代码','颜色编号','尺寸编号','数量','吊牌价','折扣','客户代码','订单编号'
//        );
//        $fileName = '订单详细信息导出';
//        $xls->export_begin($key, $fileName, 0);
//        $i = 1;
//        $key = '1111';
//        $key = array(
//            '商品代码','颜色编号','尺寸编号','数量','吊牌价','折扣','客户代码','订单编号'
//        );
//        $filename = '订单信息导出.csv';
//        $i = 2;
//        foreach($order_info as $val){
//            $data[$i]['A'] = "'".$val['model_sn'];
//            $data[$i]['B'] = "'".$val['color_no'];
//            $data[$i]['C'] = "'".$val['size_no'];
//            $data[$i]['D'] = "'".$val['nums'];
//            $data[$i]['E'] = "'".$val['cost_price'];
//            $data[$i]['F'] = "'".($customer_info[$val['order_id']]['big_'.$model_info[$val['model_sn']].'_count'] /100);
//            $data[$i]['G'] = "'".$val['code'];
//            $data[$i]['H'] = "'".$val['order_id'];
//            $i++;
//        }
//        ErpCsv::exportCsv($key, $data, $filename);
//        $xls->export_finish();
    }
}

