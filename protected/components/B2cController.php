<?php
/**
 * 控制器基础类，所有控制器均需继承此类
 * @author chenfenghua <843958575@qq.com>
 */

class B2cController extends Controller
{
    public $layout='/layouts/column2';
    public $connection;
    public $pagesize = 15;
    public $down_name;
    public $down_code;
    public $username;
    public $customer_id;
    public $purchase_id;
    public $route;
    public $total_num = 0;
    public $amount = 0.00;
    public $order_state;
    public function __construct($id,$module)
    {
        parent::__construct($id,$module);

        $this->username = Yii::app()->session['name'];
        $this->customer_id = Yii::app()->session['customer_id'];
        $this->purchase_id = Yii::app()->session['purchase_id'];

        $this->CheckLogin();
        $this->orderTotal($this->purchase_id,$this->customer_id);
    }

    /**
     * 登录设置
     */
    public function CheckLogin()
    {
        if (!$this->customer_id) $this->redirect('/user/index');
    }

    /**
     * 订单统计
     *
     * @param $purchase_id
     * @param $customer_id
     * @return mixed
     */
    public function orderTotal($purchase_id,$customer_id)
    {
        $orderModel = new Order();
        $items = $orderModel->orderItems($purchase_id,$customer_id);

        $this->total_num = isset($items['order_row']['total_num'])?$items['order_row']['total_num']:0;
        $this->amount = isset($items['order_row']['cost_item'])?$items['order_row']['cost_item']:'0.00';
        $this->order_state = isset($items['order_row']['status'])?$items['order_row']['status']:'active';
    }

    /**
     * GET获取单个数据
     */
    public function get($val,$type='str')
    {
        if ($type == 'str') {
            $data = isset($_GET[$val])?$_GET[$val]:'';
        } else if($type == 'int') {
            $data = isset($_GET[$val])?$_GET[$val]:0;
        }
        $data = str_replace('.html','',$data);
        return $this->_CheckAndQuote($data);
    }

    /**
     * GET获取多个数据
     */
    public function gets(Array $arr)
    {
        foreach ($arr as $v) {
            $item[] = $this->get($v);
        }

        return $item;
    }

    /**
     * POST获取单个数据
     */
    public function post($val,$type='str')
    {
        if ($type == 'str') {
            $data = isset($_POST[$val])?$_POST[$val]:'';
        } else if($type == 'int') {
            $data = isset($_POST[$val])?$_POST[$val]:0;
        }
        return $data;
    }

    /**
     * POST获取多个数据
     */
    public function posts(Array $arr)
    {
        foreach ($arr as $v) {
            $item[$v] = $this->post($v);
        }

        return $item;
    }

    /**
     * prevent from invalidate sql sentense is put in advanced
     *
     * @param  $value value of waiting for format
     * @return string formatted value
     */
    function _CheckAndQuote($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        //return '\'' . mysql_real_escape_string($value) . '\'';
        return htmlspecialchars(addslashes($value));
    }

    /**
     * 加载js文件
     * @param $file
     * @param string $type
     * @param string $theme
     */
    public function registerJs($file,$type='end',$theme='b2c')
    {
        switch($type) {
            case 'end':
                $js = CClientScript::POS_END;
                break;
            default:
                $js = CClientScript::POS_END;
        }
        if (is_array($file)) {
            foreach ($file as $model)
                Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl .'/themes/'.$theme.'/js/'.$model.'.js',$js);
        } else
            Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl .'/themes/'.$theme.'/js/'.$file.'.js',$js);
    }

    //返回json响应
    /**
     * @param int $code
     * @param string $msg
     * @param array $data
     */
    public function sendJsonResponse($code=200,$msg='',$data=array()){
        $result = array('code'=>$code,'msg'=>$msg,'data'=>$data);
        echo json_encode($result);
        die();
    }

    /**
     * 生成URL
     *
     * @param array $params
     * @return string
     */
    public function urlParams($params=array())
    {
        $c_id = isset($params['c_id'])?$params['c_id']:$this->get('c_id');
        $sd = isset($params['sd'])?$params['sd']:$this->get('sd');
        $wv = isset($params['wv'])?$params['wv']:$this->get('wv');
        $lv = isset($params['lv'])?$params['lv']:$this->get('lv');
        $plv = isset($params['plv'])?$params['plv']:$this->get('plv');
        $or = isset($params['or'])?$params['or']:$this->get('or');
        $price = isset($params['price'])?$params['price']:$this->get('price');
        $hits = isset($params['hits'])?$params['hits']:$this->get('hits');
        $serial_num = isset($_GET['serial_num'])?$_GET['serial_num']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;


        $arr = array();
        if ($c_id) $arr[] = "c_id=$c_id";
        if ($sd) $arr[] = "sd=$sd";
        if ($wv) $arr[] = "wv=$wv";
        if ($lv) $arr[] = "lv=$lv";
        if ($plv) $arr[] = "plv=$plv";
        if ($or) $arr[] = "or=$or";
        if ($price) $arr[] = "price=$price";
        if ($hits) $arr[] = "hits=$hits";
        if($serial_num) $arr[] = "serial_num=".$serial_num;
        if (isset($params['next']) && $params['next'] == 1 && $page) {
            $nextpage = $page + 1;
            $arr[] = "page=$nextpage";
        }

        $url='/'.$this->route.'/';
        return $url.'?'.implode('&',$arr);
    }

    /**
     * 字符串截取
     * @access public
     * @param $String
     * @param $CutNum
     * @param string $Style
     * @param string $encoding
     * @return string
     */

    function globalSubstr($String,$CutNum,$Style='',$encoding='utf-8'){
        if(!function_exists('mb_substr')) {
            die('must support php_mb_substr');
        }
        if(!$String) return false;
        $String = strip_tags($String);
        $strCounter = mb_strlen($String,$encoding);
        if($CutNum > $strCounter) $CutNum = $strCounter;
        $tempStr  = mb_substr($String,0,$CutNum,$encoding);
        if($strCounter > $CutNum) {
            $tempStr .= $Style;
        }
        return $tempStr;
    }
}