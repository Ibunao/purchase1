<?php

/**
 * 订单管理
 *
 * @author     chenfenghua<843958575@qq.com>
 * @copyright  Copyright 2008-2013 mall.octmami.com
 * @version    1.0
 */
class DefaultController extends BaseController
{

    public $admin;
    public function __construct($id,$module)
    {
        parent::__construct($id,$module);
        $this->admin = Yii::app()->session['_admini'];
        $this->registerJs(
            array('jquery.uploadify'),'end','bootstrap'
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
     * 商品订单汇总
     */
    public function actionIndex()
    {
        set_time_limit(0);
        $select_option =  '';//Yii::app()->cache->get('select_option');

        if(empty($select_option)){
            $select_option = $this->filter();
        }
        $pageIndex = isset($_GET['page'])?$_GET['page']:1;

        $params= @$_GET['param'];
        //???
        isset($params['order'])?isset($params['order']):'p.style_sn';

        $params['page']= $pageIndex;
        $order = new Orders();
        //$pageIndex 多余   ???
        $result = $order->orderList($pageIndex,$this->pagesize,$params);

        if(!empty($params['download'])){
            if(!empty($result['item'])){
                $product_id= array();
                foreach($result['item'] as $k=>$v){
                    $product_id[] = $v['product_id'];
                    $order_type = $order->customerOrderByProductIdCount($v['product_id'],$params);
                    $result['item'][$k]['customer'] = $order_type['customer'];
                    $result['item'][$k]['self'] = $order_type['self'];
                    $result['item'][$k]['img_url'] = $v['img_url'];
                    foreach($select_option['cat_big'] as $cat_big){
                        if($cat_big['big_id'] == $v['cat_b'])
                            $result['item'][$k]['cat_big_name'] = $cat_big['cat_name'];
                    }
                    foreach($select_option['cat_middle'] as $cat_middle){
                        if($cat_middle['middle_id'] == $v['cat_m'])
                            $result['item'][$k]['cat_middle_name'] = $cat_middle['cat_name'];
                    }
                    foreach($select_option['cat_small'] as $cat_small){
                        if($cat_small['small_id'] == $v['cat_s'])
                            $result['item'][$k]['cat_small_name'] = $cat_small['cat_name'];
                    }
                }
                //订单数量汇总: 订单金额汇总:
                $order_amount = $order->getOrderAmount(implode(',',$product_id),$params);
                $result['nums'] = $order_amount['nums'];
                $result['amount'] = $order_amount['amount'];
            }
            $keys = array('大类','中类','小类','款色','流水','商品类型', '吊牌价' ,'加盟订货','直营订货','总订货','尺寸');
            $data = array();
            foreach($result['item'] as $k=> $v){
                $data[$k]['A'] = $v['cat_big_name'];
                $data[$k]['B'] = $v['cat_middle_name'];
                $data[$k]['C'] = $v['cat_small_name'];
                $data[$k]['D'] = $v['style_sn'];
                $data[$k]['E'] = $v['serial_num'];
                $data[$k]['F'] = $v['type_name'];
                $data[$k]['G'] = $v['cost_price'];
                $data[$k]['H'] = $v['customer'];
                $data[$k]['I'] = $v['self'];
                $data[$k]['J'] = $v['nums'];
                $data[$k]['K'] = $v['size_name'];
            }

            $data2 = array(
                array('',''),
                array('订货数量汇总',$result['nums'] ),
                array('订货金额汇总',number_format($result['amount'],2) ),
            );
            $filename = '商品导出筛选结果';
            $export = new Io_xls();
            $export->export_begin($keys, $filename, count($data));
            $export->export_rows($data);
            $export->export_rows($data2);
            $export->export_finish();
        }else{
            if(!empty($result['item'])){
                $product_id= array();
                foreach($result['item'] as $k=>$v){
                    $product_id[] = $v['product_id'];
                    $order_type = $order->customerOrderByStyleSnCount($v['style_sn'],$params);
                    $result['item'][$k]['customer'] = $order_type['customer'];
                    $result['item'][$k]['self'] = $order_type['self'];
                    $result['item'][$k]['img_url'] = $v['img_url'];
                    foreach($select_option['cat_big'] as $cat_big){
                        if($cat_big['big_id'] == $v['cat_b'])
                            $result['item'][$k]['cat_big_name'] = $cat_big['cat_name'];
                    }
                    foreach($select_option['cat_middle'] as $cat_middle){
                        if($cat_middle['middle_id'] == $v['cat_m'])
                            $result['item'][$k]['cat_middle_name'] = $cat_middle['cat_name'];
                    }
                    foreach($select_option['cat_small'] as $cat_small){
                        if($cat_small['small_id'] == $v['cat_s'])
                            $result['item'][$k]['cat_small_name'] = $cat_small['cat_name'];
                    }
                }
                //订单数量汇总: 订单金额汇总:
                $order_amount = $order->getOrderAmount(implode(',',$product_id),$params);
                $result['nums'] = $order_amount['nums'];
                $result['amount'] = $order_amount['amount'];
            }

            //分页
            $pages = new CPagination($result['count']);

            if(empty($params['view'])){
                $view = 'index';
            }else{
                $view = 'indexwithpic';
            }

            $this->render($view,array(
                'result'=>$result,
                'pages'=>$pages,
                'pageIndex'=>$pageIndex-1,
                'params'=>$params,
                'selectOption'=>$select_option
            ));
        }
    }




    public function filter(){
        //订货会：
        $purchase = new Purchase();
        $select_option['purchase'] = $purchase->findAll();

        //（渠道）客户类型：
        $customer = new Customer();
        $select_option['customer'] = $customer->getList('type','','type');

        //大类：
        $cat_big = new Cat_big();
        $select_option['cat_big'] = $cat_big->getList();
//        var_dump($select_option);die();
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
            1=>'0-99',
            2=>'100-199',
            3=>'200-299',
            4=>'300-399',
            5=>'400-499',
            6=>'500-999',
            7=>'1000-1499',
            8=>'1500-2000',
            9=>'2000以上',
        );

        $type = new Type();
        $select_option['ptype'] = $type->getType();

        Yii::app()->cache->set('select_option',$select_option,7200);
        return $select_option;
    }


    public function  actionDialogue(){

        $style_sn = $this->get('style_sn');
        if(empty($style_sn)){
            echo json_encode(array('code'=>400));
            die();
        }
        $product = new Product();

        $result = $product->getList($style_sn);
        if($result){
            $result['order_count'] = $product->getProductSizeOrder($style_sn);
        }

        echo json_encode(array('code'=>200,'data'=>$result));


    }
}