<?php
/**
 * 控制器基础类，所有控制器均需继承此类
 * @author chenfenghua <843958575@qq.com>
 */

class ApiController extends Controller
{
    public $connection;
    public $pageSize = 10;
    public $xuyue_price = 199;
    public $cart_expire = 86400;

    public function __construct($id,$module)
    {
        parent::__construct($id,$module);

        $this->connection = Yii::app()->db;
    }

    /**
     * Controller 直接执行SQL
     *
     * @param $sql
     * @return mixed
     */
    public function QueryRow($sql)
    {
        $command = $this->connection->createCommand($sql);
        $result = $command->queryRow();
        return $result;
    }

    /**
     * Controller 直接执行SQL
     *
     * @param $sql
     * @return mixed
     */
    public function QueryAll($sql)
    {
        $command = $this->connection->createCommand($sql);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * Controller 直接执行SQL
     *
     * @param $sql
     * @return mixed
     */
    public function Execute($sql)
    {
        $command = $this->connection->createCommand($sql);
        $result = $command->execute();
        return $result;
    }

    /**
     * GET获取单个数据
     *
     * @param $val
     * @param string $type
     * @return string
     */
    public function get($val,$type='str')
    {
        $data = '';
        if ($type == 'str') {
            $data = isset($_GET[$val])?$_GET[$val]:'';
        } else if($type == 'int') {
            $data = isset($_GET[$val])?$_GET[$val]:0;
        }
        return $this->_CheckAndQuote($data);
    }

    /**
     * POST获取单个数据
     *
     * @param $val
     * @param string $type
     * @return string
     */
    public function post($val,$type='str')
    {
        $data = '';
        if ($type == 'str') {
            $data = isset($_POST[$val])?$_POST[$val]:'';
        } else if($type == 'int') {
            $data = isset($_POST[$val])?$_POST[$val]:0;
        }
        return $this->_CheckAndQuote($data);
    }

    /**
     * request封装
     * @param string $val  字段名
     * @param string $type 字段类型
     * @return string
     */
    public function request($val,$type='str',$default=0){
        if ($type == 'str') {
            $data = isset($_REQUEST[$val])?$_REQUEST[$val]:'';
        } else if($type == 'int') {
            $data = isset($_REQUEST[$val])?$_REQUEST[$val]:$default;
        }
        return $this->_CheckAndQuote($data);
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

        return htmlspecialchars(addslashes($value));
    }

    /**
     * 数据展示
     *
     * @param $arr
     */
    public function sendJSONResponse( $arr)
    {
        //header('Content-type: application/json');
        echo json_encode($arr);
        Yii::app()->end();
    }

    /**
     * 返回正确数据
     *
     * @param $data
     * @param string $msg
     * @param int $code
     */
    public function sendSucc($data,$msg = '返回正确数据',$code = 200)
    {
        //header('Content-type: application/json');
        $result= array('code'=>$code,'msg'=>$msg,'data'=>$data);
        echo $this->sendJSONResponse($result);
        Yii::app()->end();
    }

    /**
     * 错误展示
     *
     * @param $code
     * @param $msg
     */
    public function sendError($msg,$code=400)
    {
        $result= array('code'=>$code,'msg'=>$msg);
        echo $this->sendJSONResponse($result);
        die;
    }

    /**
     * 手机号码验证
     *
     * @param string $mobile
     * @return bool
     */
    public function matchMobile($mobile){
        return preg_match("/1[34578]{1}\d{9}$/",$mobile)? true: false;
    }

    /**
     * 写入日志
     *
     * @param $filename
     * @param $content
     */
    public function log($filename,$content){
        $file_path ="./cache/".$filename;
        file_put_contents($file_path,date("Y-m-d H:i:s",time()).'  '.$content."\n\r",FILE_APPEND);
    }

    /**
     * 转换位置
     * @param array $arr
     * @param $trans_name
     * @return mixed
     */
    public function arrayToTransPosition($arr = array(), $trans_name){
        if(!$arr) return array();
        foreach($arr as $val){
            $item[$val[$trans_name]] = $val;
        }
        return $item;
    }

    /**
     * 查询1条数据
     * @param $select
     * @param $table
     * @param $where
     * @param string $orderBy
     * @param string $groupBy
     * @return CDbDataReader|mixed
     */
    public function selectQueryRow($select, $table, $where="", $groupBy="", $orderBy=""){
        if(!empty($where)){
            $where =" WHERE ".$where;
        }
        if(!empty($orderBy)) {
            $orderBy = " ORDER BY {$orderBy} ";
        }
        if(!empty($groupBy)) {
            $groupBy = " GROUP BY {$groupBy} ";
        }
        $sql = "SELECT {$select} FROM {$table} ".$where." {$groupBy} {$orderBy}";
        return $this->QueryRow($sql);
    }

    /**
     * 查询N条数据
     * @param $select
     * @param $table
     * @param $where
     * @param $orderBy
     * @param $groupBy
     * @return mixed
     */
    public function selectQueryRows($select, $table, $where="", $groupBy="", $orderBy=""){
        if(!empty($where)){
            $where =" WHERE ".$where;
        }
        if(!empty($orderBy)) {
            $orderBy = " ORDER BY {$orderBy} ";
        }
        if(!empty($groupBy)) {
            $groupBy = " GROUP BY {$groupBy} ";
        }
        $sql = "SELECT {$select} FROM {$table} ".$where." {$groupBy} {$orderBy}";
        return $this->QueryAll($sql);
    }
} 