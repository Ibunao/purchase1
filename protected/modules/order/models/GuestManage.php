<?php

/**
 * Guest Manage Model 主要是处理ManageController分配给他的工作
 *
 * @author        zangmiao <838881690@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       Manage.controller
 * @license       http://www.octmami.com/license
 * @version       v1.2.0
 */
class GuestManage extends BaseModel
{
    public function tableName()
    {
        return '{{customer}}';
    }

    /**
     * @return array
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }

    /**
     *
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Admin the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return array
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('purchase_id, code, name, password, mobile', 'required'),
            array('parent_id, purchase_id, mobile, target', 'numerical', 'integerOnly' => true),
            array('mobile', 'length', 'max' => 11),
            array('code', 'unique', 'caseSensitive' => false, 'className' => 'code', 'message' => 'code"{value}"已经存在，请更换'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('customer_id, parent_id, purchase_id, code, name, password, mobile, type, province, area, target, department, leader, leader_name, agent, disabled', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'customer_id' => 'Customer Id',
            'parent_id' => 'Parent Id',
            'purchase_id' => 'Purchase Id',
            'code' => 'Code',
            'name' => 'Name',
            'password' => 'Password',
            'mobile' => 'Mobile',
            'type' => 'Type',
            'province' => 'Province',
            'area' => 'Area',
            'target' => 'Target',
            'department' => 'Department',
            'leader' => 'Leader',
            'leader_name' => 'Leader Name',
            'agent' => 'Agent',
            'disabled' => 'Disabled',
        );
    }

    /**
     * 导入数据库日志
     *
     * @param $file_address
     * @param $insert_time
     * @param $type
     * @param string $key
     */
    public function import_log($file_address, $insert_time, $type, $key = "")
    {
        $sql = "INSERT INTO {{import_log}} (import_file, insert_time, type, pre_key) VALUES ('{$file_address}', '{$insert_time}','{$type}', '{$key}')";
        $this->ModelExecute($sql);
    }

    /**
     * 显示分类
     * @return mixed
     */
    public function userFilter()
    {
        //客户类型
        $result['type'] = Yii::app()->params['customer_type'];

        //地区
        $result['area'] = Yii::app()->params['customer_area'];

        //部门类别
        $result['department'] = Yii::app()->params['customer_department'];

        //负责人
        $result['leader'] = Yii::app()->params['customer_leader'];

        //订货会
        $purchase = new Purchase();
        $purchase_list = $purchase->getPurchase();
        foreach ($purchase_list as $k => $v) {
            $result['purchase'][$v['purchase_id']] = $v['purchase_name'];
        }

        //省份
        $result['province'] = Yii::app()->params['customer_province'];

        //代理名称
        $agent = new Agent();
        $result['leader_name'] = $agent->getAgent();
        return $result;
    }

    /**
     * 检查用户代码的个数
     * @param $code
     * @return mixed
     */
    public function checkCode($code)
    {
        $sql = "SELECT COUNT(*) FROM {{customer}} WHERE code='" . $code . "'";
        return $this->QueryRow($sql);
    }

    /**
     * 根据手机号码与订货会品牌检查手机号码是否重复
     * @param $code
     * @param $purchase
     * @return mixed
     */
    public function checkMobile($code, $purchase)
    {
        $sql = "SELECT COUNT(*) FROM {{customer}} WHERE  mobile='" . $code . "'  and purchase_id='" . $purchase . "'";
        return $this->QueryRow($sql);
    }

    /**
     * 用户新增
     * @param array $data
     * @return mixed
     */
    public function insertDatabaseOperation($data = array())
    {
        if(($data['big_1']+$data['big_2']+$data['big_3']+$data['big_4']+$data['big_6'] == '100') && !empty($data['target'])){
            $data['big_1'] = (string)round($data['target'] * $data['big_1'] /100 , 2);
            $data['big_2'] = (string)round($data['target'] * $data['big_2'] /100 , 2);
            $data['big_3'] = (string)round($data['target'] * $data['big_3'] /100 , 2);
            $data['big_4'] = (string)round($data['target'] * $data['big_4'] /100 , 2);
            $data['big_6'] = (string)round($data['target'] * $data['big_6'] /100 , 2);
        }
        if(empty($data['big_1_count'])){
            $data['big_1_count'] = 100;
        }
        if(empty($data['big_2_count'])){
            $data['big_2_count'] = 100;
        }
        if(empty($data['big_3_count'])){
            $data['big_3_count'] = 100;
        }
        if(empty($data['big_4_count'])){
            $data['big_4_count'] = 100;
        }
        if(empty($data['big_6_count'])){
            $data['big_6_count'] = 100;
        }

        //密码默认手机号码后四位
        if (!empty($data['password'])) {
            $data['password'] = md5(md5($data['password']));
        } else {
            $data['password'] = md5(md5(substr($data['mobile'], -4)));
        }
        $sql = "SELECT * FROM  {{agent}} WHERE agent_code='" . $data['leader_name'] . "'";
        $agentResult = $this->QueryRow($sql);
        $data['leader_name'] = '';
        $data['agent'] = '';
        if (!empty($agentResult)) {
            $data['leader_name'] = $agentResult['agent_name'];
            $data['agent'] = $agentResult['agent_code'];
        }
        $data['parent_id'] = 0;
        if (!empty($data['agent'])) {
            if ($agentResult['agent_code'] == $data['code']) {
                $data['parent_id'] = 1;
            }
        }
        return $this->ModelInsert("{{customer}}", $data);
    }

    /**
     *
     * 当有错误的时候出来错误信息提示
     * @param string $msg 错误信息内容
     * @param string $url 出现错误后跳转地址
     */
    public function breakAction($msg = '', $url = '')
    {
        echo "<script>alert('" . $msg . "');location.href='" . $url . "'</script>";
        die();
    }

    /**
     * 当有错误的时候出来错误信息提示并返回前一页
     * @param string $msg
     */
    public function breakActions($msg = '')
    {
        echo "<script>alert('" . $msg . "')";
        echo "<script>history.go(-1);</script>";
        die();
    }

    /**
     * 获取当前代理相关信息
     * @param $leader_name
     * @return mixed
     */
    public function getLeaderName($leader_name)
    {
        $sql = "SELECT * FROM  {{agent}} WHERE agent_id='" . $leader_name . "'";
        return $this->QueryRow($sql);
    }

    /**
     * 更新操作
     * @param array $data
     * @return bool
     */
    public function updateDataBaseOperation($data = array())
    {
        if(($data['big_1']+$data['big_2']+$data['big_3']+$data['big_4']+$data['big_6'] == '100') && !empty($data['target'])){
            $data['big_1'] = (string)round($data['target'] * $data['big_1'] /100 , 2);
            $data['big_2'] = (string)round($data['target'] * $data['big_2'] /100 , 2);
            $data['big_3'] = (string)round($data['target'] * $data['big_3'] /100 , 2);
            $data['big_4'] = (string)round($data['target'] * $data['big_4'] /100 , 2);
            $data['big_6'] = (string)round($data['target'] * $data['big_6'] /100 , 2);
        }
        if(empty($data['big_1_count'])){
            $data['big_1_count'] = 100;
        }
        if(empty($data['big_2_count'])){
            $data['big_2_count'] = 100;
        }
        if(empty($data['big_3_count'])){
            $data['big_3_count'] = 100;
        }
        if(empty($data['big_4_count'])){
            $data['big_4_count'] = 100;
        }
        if(empty($data['big_6_count'])){
            $data['big_6_count'] = 100;
        }
        $customer_id = $data['id'];
        unset($data['id']);
        $sql = "SELECT * FROM  {{agent}} WHERE agent_code='" . $data['leader_name'] . "'";
        $agentResult = $this->QueryRow($sql);

        $data['leader_name'] = '';
        $data['agent'] = '';
        if (!empty($agentResult)) {
            $data['leader_name'] = $agentResult['agent_name'];
            $data['agent'] = $agentResult['agent_code'];
        }

        $data['parent_id'] = 0;
        if (!empty($data['agent']) && $agentResult['agent_code'] == $data['code']) {
            $data['parent_id'] = 1;
        }

        if (!empty($data['password'])) {
            $data['password'] = md5(md5($data['password']));
        }else{
            $data['password'] = md5(md5(substr($data['mobile'], -4)));
        }

        //用户定过货则不可修改该用户所参与的订货会与用户名称
        $sql = "SELECT COUNT(*) AS counts FROM {{order}} WHERE customer_id='{$customer_id}'";
        $res = $this->QueryRow($sql);
        if($res['counts'] >=1 ){
            unset($data['purchase_id']);
            unset($data['customer_name']);
            unset($data['code']);
        }

        $connection = Yii::app()->db;
        $transaction = $connection->beginTransaction();
        try {
            $this->ModelThisEdit("{{customer}}", "customer_id='{$customer_id}'", $data);
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 根据用户ID查询用户的相关信息
     * @param string $select_id 查询数据的id号
     * @return mixed
     */
    public function selectDataBaseOperation($select_id = '')
    {
        if (empty($select_id)) {
            echo "<script>alert('出错,缺少参数');history.go(-1);</script>";
            die;
        }
        $sql = "SELECT a.*,b.* FROM {{customer}} AS a LEFT  JOIN  {{agent}} AS b ON a.agent=b.agent_code WHERE a.customer_id='" . $select_id . "'";
        $res = $this->QueryRow($sql);
        if (empty($res)) {
            echo "<script>alert('暂无此用户信息');history.go(0);</script>";
            die;
        }
        if($res['target'] != '0.00') {
            $res['big_1'] = round($res['big_1'] / $res['target'] * 100, 2);
            $res['big_2'] = round($res['big_2'] / $res['target'] * 100, 2);
            $res['big_3'] = round($res['big_3'] / $res['target'] * 100, 2);
            $res['big_4'] = round($res['big_4'] / $res['target'] * 100, 2);
            $res['big_6'] = round($res['big_6'] / $res['target'] * 100, 2);
        }

        if(empty($data['big_1_count'])){
            $data['big_1_count'] = 100;
        }
        if(empty($data['big_2_count'])){
            $data['big_2_count'] = 100;
        }
        if(empty($data['big_3_count'])){
            $data['big_3_count'] = 100;
        }
        if(empty($data['big_4_count'])){
            $data['big_4_count'] = 100;
        }
        if(empty($data['big_6_count'])){
            $data['big_6_count'] = 100;
        }
        return $res;
    }

    /**
     * 查询所有客户的数据
     * @param string $pageIndex 当前页码
     * @return mixed 返回sql执行成功与否
     */
    public function selectGuestDateBaseOperation($pageIndex = '')
    {
        $limit = $this->pageNum($pageIndex);
        $sql = "SELECT a.customer_id,b.customer_name,a.code,a.purchase_id,a.target,b.cost_item FROM {{order}} AS b LEFT JOIN {{customer}} AS a ON a.customer_id=b.customer_id  LIMIT $limit";
        return $this->QueryAll($sql);
    }

    /**
     * 统计所查询的数据的总条数
     * @return mixed 返回数组
     */
    public function countAllResult()
    {
        $sql_count = "SELECT COUNT(*) FROM {{order}} AS b LEFT JOIN {{customer}} AS a ON a.customer_id=b.customer_id ";
        return $this->RowCount($sql_count);
    }

    /**
     * 根据当前页返回一个limit（分页）
     * @param string $pageIndex 当前页码
     * @return string 返回: limit X,15
     */
    public function pageNum($pageIndex = '')
    {
        $page = 15;
        $pageOffset = ($pageIndex - 1) * 15;
        return $limit = $pageOffset . ',' . $page;
    }

    /**
     * 查询检索的数据
     * @param array $data 需要查询的数据数组
     * @param string $pageIndex 当前页码
     * @return mixed 返回查询出来的数组
     */
    public function selectLikeDatabaseOperation($data = array(), $pageIndex = '')
    {
        $where = "";
        $limit = $this->pageNum($pageIndex);
        if (!empty($data['code'])) {
            $where .= "and c.code = '" . $data['code'] . "'";
        }
        if (!empty($data['name'])) {
            $where .= "and c.name like '%" . trim($data['name']) . "%'";
        }
        if (!empty($data['type'])) {
            $where .= " and c.type='" . $data['type'] . "'";
        }
        if (!empty($data['purchase_id'])) {
            $where .= " and c.purchase_id='" . $data['purchase_id'] . "'";
        }
        if (!empty($data['province'])) {
            $where .= " and  c.province='" . $data['province'] . "'";
        }

        if(!empty($data['order'])){
            if($data['order'] == '1'){
                $where .= " and o.cost_item != '0'";
            }elseif($data['order'] == '2'){
                $where .= " and o.cost_item IS NULL ";
            }
        }

        if (!empty($data['area'])) {
            $where .= " and c.area='" . $data['area'] . "'";
        }
        $sql = "SELECT c.customer_id,c.name,c.mobile,c.code,c.purchase_id,c.target,o.cost_item FROM {{customer}} AS c  LEFT JOIN  {{order}} AS o ON o.customer_id=c.customer_id WHERE c.disabled='false'  $where LIMIT $limit";
        return $this->QueryAll($sql);
    }

    /**
     * 查询所有选择的记录
     * @param array $data 需要查询的数据数组
     * @return mixed 返回数组
     */
    public function countSelectResult($data = array())
    {
        $where = "";
        if (!empty($data['code'])) {
            $where .= "and c.code = '" . $data['code'] . "'";
        }
        if (!empty($data['name'])) {
            $where .= "and c.name like '%" . trim($data['name']) . "%'";
        }
        if (!empty($data['type'])) {
            $where .= " and c.type='" . $data['type'] . "'";
        }
        if (!empty($data['purchase_id'])) {
            $where .= " and c.purchase_id='" . $data['purchase_id'] . "'";
        }
        if (!empty($data['province'])) {
            $where .= " and  c.province='" . $data['province'] . "'";
        }

        if(!empty($data['order'])){
            if($data['order'] == '1'){
                $where .= " and o.cost_item != '0'";
            }elseif($data['order'] == '2'){
                $where .= " and o.cost_item IS NULL ";
            }
        }

        if (!empty($data['area'])) {
            $where .= " and c.area='" . $data['area'] . "'";
        }
        $sql = "SELECT COUNT(*) FROM {{customer}} AS c  LEFT JOIN  {{order}} AS o ON o.customer_id=c.customer_id WHERE c.disabled='false'  $where";
        return $this->QueryRow($sql);
    }

    /**
     * 获取所有的用户
     * @return mixed
     */
    public function getAllCustomers()
    {
        $sql = "SELECT * FROM {{customer}} WHERE disabled='false'";
        return $this->QueryAll($sql);
    }

    /**
     * 转换
     *
     * @return mixed
     */
    public function transAllGuest()
    {
        $result = $this->getAllCustomers();
        if(empty($result)) return array();
        foreach ($result as $val) {
            $item[$val['code']] = $val;
        }
        return $item;
    }
}