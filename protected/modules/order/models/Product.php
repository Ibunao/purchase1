<?php

/**
 * "ectools_order_bills" 数据表模型类.
 *
 * @author        chenfenghua <843958575@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       mall.model
 * @license       http://www.octmami.com/license
 * @version       v1.0.0
 *
 */
class Product extends BaseModel
{

    private $pageSize = 15;
    private $expire = 3600;

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{product}}';
    }


    //根据款色查询商品的基本信息
    public function getList($style_sn)
    {
        $sql = "select p.name,cb.cat_name as big_name,cs.cat_name as small_name,season.season_name,scheme.scheme_name,
        p.img_url,p.cost_price,color.color_name,p.style_sn,p.product_sn,p.memo
from meet_product as p
left join meet_cat_big as cb on cb.big_id = p.cat_b
left join meet_cat_small as cs on cs.small_id = p.cat_s
left join meet_scheme as scheme on scheme.scheme_id = p.scheme_id
left join meet_season as season on season.season_id = p.season_id
left join meet_color as color on color.color_id = p.color_id where style_sn ='" . $style_sn . "' and p.disabled='false' ";
        $result = $this->QueryRow($sql);
        if (!empty($result)) {
            $result['img_url'] = $result['img_url'];
        } else {
            return false;
        }
        return $result;
    }

    /**
     * 导出商品列表
     */
    public function getListModel()
    {
        $list = $this->QueryAll("select pu.purchase_name,p.purchase_id,p.name,p.model_sn,p.serial_num,p.cost_price,
            br.brand_id,br.brand_name,
            z.size_no,z.size_name,co.color_no,co.color_name,cb.big_id as cat_b_id,cb.cat_name as cat_b,
            cm.middle_id as cat_m_id,cm.cat_name as cat_m,cs.small_id as cat_s_id,cs.cat_name as cat_s,
            le.level_name,sc.scheme_name,se.season_id,se.season_name,wa.wave_no,wa.wave_name,p.memo
            from meet_product p left join meet_size z on p.size_id = z.size_id
            left join meet_color co on p.color_id = co.color_id
            left join meet_cat_big cb on p.cat_b = cb.big_id
            left join meet_cat_middle cm on p.cat_m = cm.middle_id
            left join meet_cat_small cs on p.cat_s = cs.small_id
            left join meet_level le on p.level_id = le.level_id
            left join meet_purchase pu on p.purchase_id = pu.purchase_id
            left join meet_scheme sc on p.scheme_id = sc.scheme_id
            left join meet_season se on p.season_id = se.season_id
            left join meet_wave wa on p.wave_id = wa.wave_id
            left join meet_brand br on p.brand_id = br.brand_id
            where p.disabled = 'false' order by p.purchase_id ASC,p.serial_num ASC;
            ");
        $result = array();
        foreach ($list as $v) {
            if (!isset($result[$v['model_sn']]) || !$result[$v['model_sn']]) $result[$v['model_sn']] = $v;
            $result[$v['model_sn']]['color_str'][] = $v['color_no'] . '[' . $v['color_name'] . ']' . ';' . '000[无定义]';
            $result[$v['model_sn']]['size_str'][] = $v['size_no'] . '[' . $v['size_name'] . ']';
        }


        return $result;
    }

    public function  getProductSizeOrder($style_sn)
    {

        //查询款色的 尺码
        $sql = "select p.*,s.size_name from meet_product as p
left join meet_size as s on s.size_id = p.size_id
where p.style_sn = '" . $style_sn . "' and p.disabled='false' group by p.size_id order by p.cat_b";
        $result = $this->QueryAll($sql);
        if (empty($result)) {
            return array();
        }
        foreach ($result as $k => $v) {
            //尺寸
            $order[$k]['size'] = $v['size_name'];
            $order[$k]['self'] = '0';
            $order[$k]['customer'] = '0';

            //根据 尺码查询 商品的 id
            $sql = "select product_id from meet_product where style_sn = '" . $style_sn . "'  and size_id =" . $v['size_id'] . " and disabled='false'";
            $product_id_arr = $this->QueryAll($sql);
            if ($product_id_arr) {
                $product_ids = array();
                foreach ($product_id_arr as $vv) {
                    $product_ids[] = $vv['product_id'];
                }

                $product_ids = implode(',', $product_ids);

                //根据商品查询
                $sql = "select sum(oi.nums) as count,c.type FROM meet_order as o
left join meet_customer as c  on c.customer_id = o.customer_id
left join meet_order_items as oi on oi.order_id = o.order_id
where  oi.product_id in (" . $product_ids . ") and oi.disabled = 'false' group by c.type";
                $result = $this->QueryAll($sql);
                if ($result) {
                    foreach ($result as $vv) {
                        if ($vv['type'] == '直营') {
                            $order[$k]['self'] += $vv['count'];
                        } else {
                            $order[$k]['customer'] += $vv['count'];
                        }
                    }
                }
            }
        }
        return $order;
    }


    public function  getProductSizeOrderCustomer($style_sn, $order_id)
    {

        //查询款色的 尺码
        $sql = "select p.*,s.size_name from meet_product as p
left join meet_size as s on s.size_id = p.size_id
where p.style_sn = '" . $style_sn . "' and disabled='false' group by  p.size_id order by p.cat_b  ";
        $result = $this->QueryAll($sql);

        if (empty($result)) {
            return array();
        }
        foreach ($result as $k => $v) {

            //尺寸
            $order[$k]['size'] = $v['size_name'];
            $order[$k]['self'] = '0';
            $order[$k]['customer'] = '0';

            //根据 尺码查询 商品的 id
            $sql = "select product_id from  meet_product where style_sn = '" . $style_sn . "' and disabled='false' and size_id =" . $v['size_id'];
            $product_id_arr = $this->QueryAll($sql);

            if ($product_id_arr) {
                $product_ids = array();
                foreach ($product_id_arr as $vv) {
                    $product_ids[] = $vv['product_id'];
                }

                $product_ids = implode(',', $product_ids);

                //根据商品查询
                $sql = "select sum(oi.nums) as count,c.type    FROM meet_order as o
left join meet_customer as c  on c.customer_id = o.customer_id
left join meet_order_items as oi on oi.order_id = o.order_id
where  oi.product_id in (" . $product_ids . ") and  oi.order_id = '" . $order_id . "'  group by c.type";

                $result = $this->QueryAll($sql);

                if ($result) {
                    foreach ($result as $vv) {
                        if ($vv['type'] == '直营') {
                            $order[$k]['self'] = $vv['count'];
                        } else if ($vv['type'] == '客户') {
                            $order[$k]['customer'] = $vv['count'];
                        }
                    }
                }

            }
        }


        return $order;

    }


    public function getProductsCount($order_id, $model_sn)
    {
        $sql = 'select oi.*,p.wave_id,p.size_id,p.color_id,s.size_name,c.color_name,w.wave_name,p.img_url,p.cost_price from meet_order_items as oi
        left join meet_product as p on p.product_id = oi.product_id
left join meet_size as s on s.size_id = p.size_id
left join meet_color as c on c.color_id = p.color_id
left join meet_wave as w on w.wave_id = p.wave_id
 where order_id= "' . $order_id . '" and oi.model_sn="' . $model_sn . '"' . " and oi.disabled = 'false'";
        $result = $this->QueryAll($sql);
        if(!Yii::app()->params['is_latest_price']){
            foreach($result as $key => $val){
                $result[$key]['cost_price'] = $val['price'];
            }
        }
        return $result;
    }


    public function getSizeArr($model_sn)
    {
        $sql = 'select p.size_id ,s.size_name from meet_product as p left join meet_size as s on s.size_id = p.size_id
        where p.model_sn = "' . $model_sn . '" and p.disabled = "false" group by s.size_id ';
        return $this->QueryAll($sql);
    }

    public function getColorArr($model_sn)
    {
        $sql = 'select p.color_id ,c.color_name from meet_product as p left join meet_color as c on c.color_id = p.color_id
        where p.model_sn = "' . $model_sn . '" and p.disabled = "false" group by c.color_id ';
        return $this->QueryAll($sql);
    }

    /**
     * @return array 对模型的属性验证规则.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }


    /**
     * 返回指定的AR类的静态模型.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Admin the static model class
     */
    public static function model($className = __CLASS__)
    {

        return parent::model($className);
    }




    /*********************** 下面是新添加的内容，关于ProductController的控制器的模型层 *****************************************/

    /**
     * 获取所有数据的总数
     * @param array $arr
     * @return array|mixed
     */
    public function countAllData($arr = array())
    {
        $where = "";
        if (!empty($arr['serialNum'])) {
            $where .= " AND serial_num = {$arr['serialNum']}";
        }
        if (!empty($arr['modelSn'])) {
            $where .= " AND model_sn = {$arr['modelSn']}";
        }
        if (!empty($arr['name'])) {
            $where .= " AND name = '" . $arr['name'] . "'";
        }
        if (!empty($arr['catBig'])) {
            $where .= " AND cat_b = {$arr['catBig']}";
        }
        if (!empty($arr['catMiddle'])) {
            $where .= " AND cat_m = {$arr['catMiddle']}";
        }
        if (!empty($arr['catSmall'])) {
            $where .= " AND cat_s = {$arr['catSmall']}";
        }
        if (!empty($arr['color'])) {
            $where .= " AND color_id = {$arr['color']}";
        }
        if (!empty($arr['priceList'])) {
            $where .= " AND price_level_id = {$arr['priceList']}";
        }
        return $this->selectQueryRow("COUNT(DISTINCT (serial_num)) AS countAll", "{{product}}", "disabled='false' {$where}");
    }

    /**
     * 商品index页面的商品筛选
     * @param array $data
     * @return mixed
     */
    public function getIndexFilter($data = array())
    {

        $result['priceList'] = array(
            '1' => '0-99',
            '2' => '100-199',
            '3' => '200-299',
            '4' => '300-399',
            '5' => '400-499',
            '6' => '500-999',
            '7' => '1000-1499',
            '8' => '1500-2000',
            '9' => '2000以上',
        );

        $result['serialNum'] = $this->selectQueryRows("serial_num", "{{product}}", "disabled='false'", "serial_num");

        $result['modelSn'] = $this->selectQueryRows("model_sn", "{{product}}", "disabled='false'", "model_sn");

        $result['name'] = $this->selectQueryRows("name", "{{product}}", "disabled='false'", "name");

        $color = new Color();
        $result['color'] = $color->getColor();

        $catBig = new Cat_big();
        $result['catBig'] = $catBig->getCatBig();

        $result['catMiddle'] = array();
        if (!empty($data['catBig'])) {
            $result['catMiddle'] = $this->selectQueryRows("middle_id,cat_name", "{{cat_middle}}", "parent_id = '{$data['catBig']}'");
        }

        $result['catSmall'] = array();
        if (!empty($data['catMiddle'])) {
            $result['catSmall'] = $this->selectQueryRows("small_id,cat_name", "{{cat_small}}", "parent_id = '{$data['catMiddle']}'");
        }

        return $result;
    }

    /**
     * 商品管理的 index ，根据关键字搜索出相应的结果
     * @param array $arr 搜索关键字
     * @param string $page 页码
     * @return array|mixed
     */
    public function manageSelectLikeSearch($arr = array(), $page = '')
    {
        $offset = ($page - 1) * $this->pageSize;
        $where = "";
        if (!empty($arr['serialNum'])) {
            $where .= " AND p.serial_num = {$arr['serialNum']}";
        }
        if (!empty($arr['modelSn'])) {
            $where .= " AND p.model_sn = {$arr['modelSn']}";
        }
        if (!empty($arr['name'])) {
            $where .= " AND p.name = '" . $arr['name'] . "'";
        }
        if (!empty($arr['catBig'])) {
            $where .= " AND p.cat_b = {$arr['catBig']}";
        }
        if (!empty($arr['catMiddle'])) {
            $where .= " AND p.cat_m = {$arr['catMiddle']}";
        }
        if (!empty($arr['catSmall'])) {
            $where .= " AND p.cat_s = {$arr['catSmall']}";
        }
        if (!empty($arr['color'])) {
            $where .= " AND p.color_id = {$arr['color']}";
        }
        if (!empty($arr['priceList'])) {
            $where .= " AND p.price_level_id = {$arr['priceList']}";
        }
        $sql = "SELECT p.serial_num,p.model_sn,p.name,b.cat_name,m.cat_name AS cat_middle,p.is_down, s.small_cat_name,c.color_name,p.cost_price
              FROM  {{product}} AS p
              LEFT JOIN {{color}} AS c ON p.color_id = c.color_id
              LEFT JOIN {{cat_big}} AS b ON p.cat_b = b.big_id
              LEFT JOIN {{cat_middle}} AS m ON m.middle_id= p.cat_m
              LEFT JOIN {{cat_big_small}} AS s ON p.cat_s=s.small_id
              WHERE disabled='false' " . $where . "  GROUP BY p.serial_num ORDER BY p.serial_num DESC LIMIT {$offset}, {$this->pageSize}";

        return $this->QueryAll($sql);
    }

    /**
     * 商品默认数据
     * @param array $data
     * @return mixed
     */
    public function getProductFilter($data = array())
    {
        $brandModel = new Purchase();
        $result['purchase'] = $brandModel->getPurchase();

        $brandModel = new Brand();
        $result['brand'] = $brandModel->getBrand();

        $schemeModel = new Scheme();
        $result['scheme'] = $schemeModel->getScheme();
        $result['sizeGroup'] = $this->selectQueryRows("size_group_code, group_id, size_group_name", "{{size_group}}");

        $levelModel = new Level();
        $result['level'] = $levelModel->getLevel();

        $waveModel = new Wave();
        $result['wave'] = $waveModel->getWave();

        $catBigModel = new Cat_big();
        $result['catBig'] = $catBigModel->getCatBig();

        if (!empty($data['cat_b'])) {
            $result['season'] = $this->selectQueryRows("season_id, season_name", "{{season_big}}", "big_id = '{$data['cat_b']}'");
        }

        $result['catMiddle'] = array();
        if (!empty($data['cat_b'])) {
            $result['catMiddle'] = $this->selectQueryRows("middle_id,cat_name", "{{cat_middle}}");
        }

//        $result['catSmall'] = array();
//        if (!empty($data['cat_m'])) {
//            $result['catSmall'] = $this->selectQueryRows("small_id,cat_name", "{{cat_small}}", "parent_id = '{$data['cat_m']}'");
//        }
        $result['catSmall'] = array();
        if (!empty($data['cat_b'])) {
            $result['catSmall'] = $this->selectQueryRows("small_id,small_cat_name AS cat_name", "{{cat_big_small}}", "big_id = '{$data['cat_b']}'");
        }

        $colorModel = new Color();
        $result['color'] = $colorModel->getColor();

        $typeModel = new Type();
        $result['type'] = $typeModel->getType();

        if(!empty($data['sizeGroup'])){
            $result['size'] = $this->selectQueryRows("size_id, size_name", "{{size}}", " group_id='{$data['sizeGroup']}'");
        }

        return $result;
    }

    /**
     * 判断吊牌价所处的价格带
     * @param string $costPrice
     * @return string
     */
    public function _transCostPriceToLevel($costPrice = '')
    {
        $costPrice = (int)$costPrice;
        if ($costPrice <= 99) {
            return "1";

        } elseif ($costPrice >= 100 && $costPrice <= 199) {
            return "2";

        } elseif ($costPrice >= 200 && $costPrice <= 299) {
            return "3";

        } elseif ($costPrice >= 300 && $costPrice <= 399) {
            return "4";

        } elseif ($costPrice >= 400 && $costPrice <= 499) {
            return "5";

        } elseif ($costPrice >= 500 && $costPrice <= 999) {
            return "6";

        } elseif ($costPrice >= 1000 && $costPrice <= 1499) {
            return "7";

        } elseif ($costPrice >= 1500 && $costPrice <= 2000) {
            return "8";

        } else {
            return "9";
        }
    }

    /**
     * 检查数据是否为空
     * @param array $data
     * @param string $url
     * @param string $dataNum
     */
    private function _checkThisParamIsEmpty($data = array(), $url = "change&modelSn=", $dataNum = "")
    {
        $guestModel = new GuestManage();
        if (empty($data)) {
            $guestModel->breakAction('传入的数据为空，请检查', "/admin.php?r=order/product/" . $url . $dataNum);
        }
        foreach ($data as $k => $val) {
            if ($val == "") {
                $guestModel->breakAction("请检查{$k}的{$val}是空，请检查后提交", "/admin.php?r=order/product/" . $url . $dataNum);
            }
        }
    }

    /**
     * 添加、复制、根据款号添加商品
     * @param array $param
     * @return bool
     */
    public function addProductOperation($param = array())
    {
        if(empty($param['type'])){
            $param['type'] = '0';
        }
        //色号转换
        $color_no = $this->_transColorIdToNo($param['color']);

        //当上传图片为空，给定默认值
        if (empty($param['image'])) {
            $param['image'] = "/images/" . $param['modelSn'] . "_" . $color_no . ".jpg";
        }

        //检查是否有空值
        $this->_checkThisParamIsEmpty($param, "change&modelSn=", $param['modelSn']);

        //再次判断款号与色号是否已存在，如果重复则跳转商品修改页面
        $query_model_color_exist = $this->selectQueryRow('serial_num', '{{product}}', "model_sn='{$param['modelSn']}' AND color_id='{$param['color']}'");
        if (!empty($query_model_color_exist)) {
            echo "<script>location.href = '/admin.php?r=order/product/update&serial_num={$query_model_color_exist['serial_num']}';</script>";
            die;
        }

        //款号
        $style_sn = $param['modelSn'] . sprintf("%04d", $color_no);

        //本产品的流水号
        $query_serial_num = $this->selectQueryRow("MAX( serial_num * 1 ) AS largest", "{{product}}");
        $serialNum = $query_serial_num['largest'] + 1;

        //查询本款号的货号的最大一位（以便生成货号）
        $query_model_sn_numbers = $this->selectQueryRow("MAX(SUBSTRING(product_sn,-3,LENGTH(product_sn))) AS nums", "{{product}}", "model_sn = '{$param['modelSn']}'");
        if(empty($query_model_sn_numbers['nums'])){
            $countModelSn = 0;
        }else{
            $countModelSn = (string)$query_model_sn_numbers['nums'];
            $countModelSn = substr($countModelSn, -3);
        }

        //获取价格带
        $priceLevel = $this->_transCostPriceToLevel($param['costPrice']);

        $sql_value = "";
        foreach ($param['size'] as $v) {
            //货号
            $countModelSn++;
            $backNum = sprintf("%03d", $countModelSn);
            $product_sn = $style_sn . $backNum;

            $insert_param = array(
                'purchase_id' => $param['purchase'],
                'product_sn' => $product_sn,
                'style_sn' => $style_sn,
                'model_sn' => $param['modelSn'],
                'serial_num' => $serialNum,
                'name' => addslashes($param['name']),
                'img_url' => $param['image'],
                'brand_id' => $param['brand'],
                'cat_b' => $param['catBig'],
                'cat_m' => $param['catMiddle'],
                'cat_s' => $param['catSmall'],
                'color_id' => $param['color'],
                'size_id' => $v,
                'season_id' => $param['season'],
                'level_id' => $param['level'],
                'wave_id' => $param['wave'],
                'scheme_id' => $param['scheme'],
                'cost_price' => $param['costPrice'],
                'price_level_id' => $priceLevel,
                'memo' => addslashes($param['memo']),
                'type_id' => $param['type'],
                'disabled' => 'false',
                'is_down' => $param['status'],
            );
            $sql_value .= $this->ModelInsertValue($insert_param);
            $sql_key = $this->ModelInsertKey($insert_param);
        }

        $sql_value = substr($sql_value, 1);
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $sql = "INSERT INTO {{product}} ({$sql_key}) VALUES {$sql_value}";
            $this->ModelExecute($sql);
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 把重复的product_sn设置disabled => true
     */
    public function deleteRepeatProduct(){
        $nowTime = time();
        $sqlData = $this->QueryAll("SELECT COUNT(*) AS counts, product_sn,model_sn FROM {{product}} GROUP BY product_sn HAVING counts>1");
        foreach($sqlData as $val){
            if($val['counts'] >=2 ){
                $sql = "UPDATE {{product}} SET disabled='true' WHERE product_sn ='{$val['product_sn']}';";
                $sql .= "INSERT INTO {{pchange_log}} (change_name, change_id, change_log, change_type, create_time) VALUES ('product_sn','{$val['product_sn']}', '重复的货号product_sn disabled=true', 'error', '{$nowTime}');";
                $this->ModelExecute($sql);
            }
        }
    }

    /**
     * 修改商品操作
     * @param $param
     * @param $moreData
     * @param $lessData
     * @param $serialNum
     * @return bool
     */
    public function updateProductOperation($param, $moreData, $lessData, $serialNum)
    {
        if ($param['color_id'] == "" || $param['scheme_id'] == "") {
            echo "<script>alert('数据出错，请重试');</script>";
            die;
        }

        if (empty($param['size'])) {
            echo "<script>alert('如果你不想让这个款号出现，请刷新本页后选择：下架此商品');</script>";
            die;
        }

        //再次判断款号与色号是否已存在，如果重复则跳转商品修改页面
        $query_model_color_exist = $this->selectQueryRow('serial_num', '{{product}}', "model_sn='{$param['modelSn']}' AND color_id='{$param['color_id']}' AND serial_num != '{$serialNum}'");
        if ($query_model_color_exist) {
            $this->_checkAndSkip("此换号与色号已存在，点击确定跳转到该款号色号中修改商品", "/admin.php?r=order/product/update&serial_num='{$query_model_color_exist['serial_num']}'");
            die;
        }

        $param['size'] = $moreData;
        $sql_add = "";

        //新增尺码数据
        if (!empty($moreData)) {
            $sql_add .= $this->_addOnlyAddProducts($param, $moreData, $serialNum);
        }

        //下架该尺码
        if (!empty($lessData)) {
             $this->_updateProducts($lessData, $serialNum);
        }

        //执行上面返回的sql
        if (!empty($sql_add)) {
            $this->ModelExecute($sql_add);
        }

        //修改其他商品基本数据
        if ($this->_updateAllSerialNumProduct($param, $serialNum)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断此流水号是否存在多款号
     * @param $serialNum
     * @return bool
     */
    public function _checkThisHaveMoreThanOneProductSn($serialNum){
        $i = 0;
        $nowTime = time();
        $model_sn = $this->selectQueryRow("model_sn", "{{product}}", "serial_num='{$serialNum}'");
        $sqlData = $this->selectQueryRows("COUNT(*) AS counts, product_sn", "{{product}}", " model_sn = '{$model_sn['model_sn']}' GROUP BY product_sn HAVING counts>1");
        foreach($sqlData as $val){
            if($val['counts'] >=2){
                $sql = "INSERT INTO {{pchange_log}} (change_name, change_id, change_log, change_type, create_time) VALUES ('product_sn', '{$val['product_sn']}', '此货号(product_sn)重复', 'error', '{$nowTime}');";
                $this->ModelExecute($sql);
                $i ++;
            }
        }
        if($i>=1){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 只插入新的数据
     * @param $param
     * @param $moreData
     * @param $serialNum
     * @return bool
     */
    private function _addOnlyAddProducts($param, $moreData, $serialNum)
    {
        //先检查该新增的商品在数据库中是否存在，如果存在就直接disabled='false'
        $moreData = $this->_checkDoHaveThisProduct($serialNum, $moreData);
        if (empty($moreData)) {
            return "";
        }

        if(empty($param['type'])){
            $param['type'] = '0';
        }

        //色号转换
        $color_no = $this->_transColorIdToNo($param['color_id']);

        //当上传图片为空，给定默认值
        if (empty($param['image'])) {
            $param['image'] = "/images/" . $param['modelSn'] . "_" . $color_no . ".jpg";
        }

        //检查是否有空值
        $this->_checkThisParamIsEmpty($param, "update&serial_num=", $serialNum);

        //款号
        $style_sn = $param['modelSn'] . sprintf("%04d", $color_no);

        //查询此款款号的最大货号（以便生成新的货号）
        $query_model_sn_numbers = $this->selectQueryRow("MAX(SUBSTRING(product_sn,-3,LENGTH(product_sn))) AS nums", "{{product}}", "style_sn = '{$style_sn}'");
        if(empty($query_model_sn_numbers['nums'])){
            $countModelSn = 0;
        }else{
            $countModelSn = (string)$query_model_sn_numbers['nums'];
            $countModelSn = substr($countModelSn, -3);
        }

        //获取价格带
        $priceLevel = $this->_transCostPriceToLevel($param['costPrice']);

        $sql_value = "";
        $nowTime = time();
        $sql = "";
        foreach ($moreData as $v) {
            //货号
            $countModelSn++;
            $backNum = sprintf("%03d", $countModelSn);
            $product_sn = $style_sn . $backNum;

            $insert_param = array(
                'purchase_id' => $param['purchase'],
                'product_sn' => $product_sn,
                'style_sn' => $style_sn,
                'model_sn' => $param['modelSn'],
                'serial_num' => $serialNum,
                'name' => addslashes($param['name']),
                'img_url' => $param['image'],
                'brand_id' => $param['brand'],
                'cat_b' => $param['catBig'],
                'cat_m' => $param['catMiddle'],
                'cat_s' => $param['catSmall'],
                'color_id' => $param['color_id'],
                'size_id' => $v,
                'season_id' => $param['season'],
                'level_id' => $param['level'],
                'wave_id' => $param['wave'],
                'scheme_id' => $param['scheme_id'],
                'cost_price' => $param['costPrice'],
                'price_level_id' => $priceLevel,
                'type_id' => $param['type'],
                'memo' => addslashes($param['memo']),
                'is_down' => $param['status'],
            );
            $sql .= "INSERT INTO {{pchange_log}} ( change_name, change_id, change_type, create_time) VALUES ('product_sn', '{$product_sn}', 'add', '{$nowTime}');";
            $sql_value .= $this->ModelInsertValue($insert_param);
            $sql_key = $this->ModelInsertKey($insert_param);
        }
        $sql_value = substr($sql_value, 1);
        return "INSERT INTO {{product}} ({$sql_key}) VALUES {$sql_value} ;" . $sql;
    }

    /**
     * disabled=true商品
     * @param $lessData
     * @param $serialNum
     * @return string
     */
    private function _updateProducts($lessData, $serialNum)
    {
        $nowTime = time();
        foreach ($lessData as $k => $v) {
            $error_product = $this->selectQueryRow("product_id,COUNT(*) AS counts", "{{product}}", "serial_num='{$serialNum}' AND size_id='{$v}' AND is_error='true'");
            if($error_product['counts'] >=1){
                unset($lessData[$k]);
            }else{
                $sqlData = $this->selectQueryRow("product_id", "{{product}}", "serial_num='{$serialNum}' AND size_id='{$v}' AND disabled='false' AND is_error='false'");
                $isBrought = $this->selectQueryRow("SUM(nums) AS nums", "{{order_items}}", "product_id='{$sqlData['product_id']}' AND disabled='false' ");
                if (empty($isBrought['nums'])) {
                    $sql_update = "UPDATE {{product}} SET disabled='true' WHERE product_id = '{$sqlData['product_id']}' AND is_error='false';";
                    $sql_update .= "INSERT INTO {{pchange_log}} (change_name, change_id, change_log, change_type, create_time) VALUES ('product_id', '{$sqlData['product_id']}', '删除商品disabled=true', 'disabled', '{$nowTime}');";
                    $this->Execute($sql_update);
                    unset($lessData[$k]);
                }
            }
        }
        return true;
    }

    /**
     * 修改其他商品属性
     * @param $param
     * @param $serialNum
     * @return bool
     */
    private function _updateAllSerialNumProduct($param, $serialNum)
    {
        //色号转换
        $color_no = $this->_transColorIdToNo($param['color_id']);
        $model_sn = $this->getThisModelSnBySerialNum($serialNum);
        $this->disabledErrorProduct($model_sn);
        //当上传图片为空，给定默认值
        if (empty($param['image'])) {
            $param['image'] = "/images/" . $param['modelSn'] . "_" . $color_no . ".jpg";
        }
        $nowTime = time();
        $style_sn = $param['modelSn'] . sprintf("%04d", $color_no);
        $price_level_id = $this->_transCostPriceToLevel($param['costPrice']);
        //检查这个款号是否被购买，如果有人购买了，不可修改品牌，名称
        if ($this->_checkThisHasBeenBrought($style_sn)) {
            //根据流水号修改的数据
            $updateParam = array(
                'img_url' => addslashes($param['image']),
                'memo' => addslashes($param['memo']),
                'is_down' => $param['status'],
            );
            //根据款号修改的数据
            $updateBaseInfo = array(
                'cost_price' => $param['costPrice'],
                'price_level_id' => $price_level_id,
                'cat_b' => $param['catBig'],
                'cat_m' => $param['catMiddle'],
                'cat_s' => $param['catSmall'],
                'season_id' => $param['season'],
                'level_id' => $param['level'],
                'wave_id' => $param['wave'],
                'type_id' => $param['type'],
                'brand_id' => $param['brand'],
            );
        } else {
            //根据流水号修改的数据
            $updateParam = array(
                'name' => addslashes($param['name']),
                'img_url' => addslashes($param['image']),
                'memo' => addslashes($param['memo']),
                'is_down' => $param['status'],
            );
            //根据款号修改的数据
            $updateBaseInfo = array(
                'cost_price' => $param['costPrice'],
                'price_level_id' => $price_level_id,
                'brand_id' => $param['brand'],
                'purchase_id' => $param['purchase'],
                'cat_b' => $param['catBig'],
                'cat_m' => $param['catMiddle'],
                'cat_s' => $param['catSmall'],
                'season_id' => $param['season'],
                'level_id' => $param['level'],
                'wave_id' => $param['wave'],
                'type_id' => $param['type'],
            );
        }
        //修改日志文件
        $log1 = serialize($updateParam);
        $log2 = serialize($updateBaseInfo);

        //流水号修改的内容
        $arr = array();
        foreach ($updateParam as $key => $val) {
            $arr[] = $key . '=' . "'" . $val . "'";
        }
        $condition1 = implode(', ', $arr);

        //款号修改的内容
        $result = array();
        foreach ($updateBaseInfo as $key => $val) {
            $result[] = $key . '=' . "'" . $val . "'";
        }
        $condition2 = implode(', ', $result);

        //写入修改日志
        $sql_update = "INSERT INTO {{pchange_log}} (change_name, change_id, change_log, change_type, create_time) VALUES ('serial_num', '{$serialNum}', '{$log1}', 'change', '{$nowTime}'),('model_sn', '{$model_sn}','{$log2}', 'change', '{$nowTime}');";

        if ($this->updateQueryRow($condition1, "{{product}}", "serial_num='{$serialNum}'") && $this->updateQueryRow($condition2, "{{product}}", "model_sn='{$model_sn}'") && $this->ModelExecute($sql_update)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 下架所有错误的商品
     * @param $model_sn
     */
    public function disabledErrorProduct($model_sn){
       $this->Execute("UPDATE {{product}} SET disabled='true' WHERE model_sn='{$model_sn}' AND is_error='true'");
    }

    /**
     * get model_sn by serial_num
     * @param $serial_num
     * @return mixed
     */
    public function getThisModelSnBySerialNum($serial_num)
    {
        $sql = "SELECT model_sn FROM {{product}} WHERE serial_num='{$serial_num}' AND disabled='false' AND is_down='0'";
        $res = $this->QueryRow($sql);
        return $res['model_sn'];
    }

    /**
     * 检查此款是否被买
     * @param $style_sn
     * @return bool
     */
    private function _checkThisHasBeenBrought($style_sn)
    {
        $res = $this->selectQueryRow("COUNT(*)", "{{order_items}}", " style_sn='{$style_sn}'");
        if ($res['COUNT(*)'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 转换color_id变成color_no
     * @param $color_id
     * @return mixed
     */
    public function _transColorIdToNo($color_id)
    {
        $color = new Color();
        $colorTrans = $color->transColor();
        return $colorTrans[$color_id];
    }

    /**
     * disabled => false
     * @param $serialNum
     * @param $moreData
     * @return string
     */
    private function _checkDoHaveThisProduct($serialNum, $moreData)
    {
        $nowTime = time();
        foreach ($moreData as $k => $v) {
            //如果该流水号对应的尺码存在错误 is_error 直接跳过
            $errorProduct = $this->selectQueryRow("product_id,product_sn", "{{product}}", "serial_num='{$serialNum}' AND size_id='{$v}' AND is_error='true'");
            if(!empty($errorProduct)){
                unset($moreData[$k]);
            }else{
                //如果该流水号和尺码
                $product_info = $this->selectQueryRow("product_id,product_sn", "{{product}}", "serial_num='{$serialNum}' AND size_id='{$v}' AND is_error='false'");
                if($product_info){
                    $sql_update = "UPDATE {{product}} SET disabled='false' WHERE serial_num='{$serialNum}' AND size_id='{$v}' AND is_error='false';";
                    $sql_update .= "INSERT INTO {{pchange_log}} (change_name, change_id, change_log, change_type, create_time) VALUES ('product_id', '{$product_info['product_id']}', '恢复商品disabled=false', 'disabled', '{$nowTime}');";
                    $this->ModelExecute($sql_update);
                    unset($moreData[$k]);
                }
            }
        }
        return $moreData;
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
    public function selectQueryRow($select, $table, $where = "", $groupBy = "", $orderBy = "")
    {
        if (!empty($where)) {
            $where = " WHERE " . $where;
        }
        if (!empty($orderBy)) {
            $orderBy = " ORDER BY {$orderBy} ";
        }
        if (!empty($groupBy)) {
            $groupBy = " GROUP BY {$groupBy} ";
        }
        $sql = "SELECT {$select} FROM {$table} " . $where . " {$groupBy} {$orderBy}";
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
    public function selectQueryRows($select, $table, $where = "", $groupBy = "", $orderBy = "")
    {
        if (!empty($where)) {
            $where = " WHERE " . $where;
        }
        if (!empty($orderBy)) {
            $orderBy = " ORDER BY {$orderBy} ";
        }
        if (!empty($groupBy)) {
            $groupBy = " GROUP BY {$groupBy} ";
        }
        $sql = "SELECT {$select} FROM {$table} " . $where . " {$groupBy} {$orderBy}";
        return $this->QueryAll($sql);
    }

    /**
     * 跳转
     * @param string $msg
     * @param $url
     */
    private function _checkAndSkip($msg = "", $url)
    {
        echo "<script>alert('{$msg}');location.href = '{$url}'</script>";
        die;
    }

    /**
     * 修改操作
     * @param string $set
     * @param $table
     * @param string $where
     * @return bool
     */
    public function updateQueryRow($set = "", $table, $where = "")
    {
        if (empty($where)) {
            echo "没有where限制,谨慎";
        }
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $sql = "UPDATE {$table} SET " . $set . " WHERE " . $where;
            $this->ModelExecute($sql);
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * sql 新增操作
     * @param $table
     * @param $key
     * @param $values
     * @return bool
     */
    public function insertIntoTale($table, $key, $values)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $sql = "INSERT INTO {$table} ({$key}) VALUES {$values} ";
            $this->ModelExecute($sql);
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 检查大类，中类，小类是否正确
     * @param $big
     * @param $middle
     * @param $small
     * @return bool
     */
    public function checkSizeIsRight($big, $middle, $small)
    {
        $sql = "SELECT m.middle_id FROM {{cat_middle}} AS m LEFT JOIN {{cat_big}} AS b ON m.parent_id = b.big_id WHERE b.cat_name='{$big}' AND m.cat_name='{$middle}'";
        $res = $this->QueryRow($sql);
        if (empty($res)) {
            return false;
        }
        $sql = "SELECT * FROM {{cat_small}} AS s LEFT JOIN {{cat_middle}} AS m ON m.middle_id=s.parent_id WHERE m.cat_name='{$middle}' AND s.cat_name='{$small}'";
        $result = $this->QueryRow($sql);
        if (empty($result)) {
            return false;
        }
        return true;
    }

    /**
     * 值检查大类与小类
     * @param $big
     * @param $small
     * @return bool
     */
    public function checkSizeBigAndSmall($big, $small){
        $sql = "SELECT * FROM {{cat_big_small}} WHERE big_cat_name='{$big}' AND small_cat_name='{$small}'";
        $res = $this->queryRow($sql);
        if(empty($res)){
            return false;
        }else{
            return true;
        }
    }
    /**
     * 检查这个款号、颜色、尺码是否存在
     * @param $model_sn
     * @return bool
     */
    public function checkThisModelColorSizeIsValue($model_sn, $serial_num)
    {
        $sql = "SELECT COUNT(*) AS nums FROM {{product}} WHERE model_sn='{$model_sn}' OR serial_num='{$serial_num}'";
        $res = $this->QueryRow($sql);
        if ($res['nums'] > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 不区分订货会输出客户总订单
     * @return array
     */
    public function exportMasterAndSlave()
    {
        $sql = "SELECT c.relation_code,c.customer_id FROM {{order}} AS o LEFT JOIN {{customer}} AS c ON c.customer_id=o.customer_id WHERE o.disabled='false' AND c.disabled='false'";
        $res = $this->QueryAll($sql);
        if (empty($res)) return array();
        foreach($res as $pre => $suf){
            $trans[$suf['customer_id']] = $suf;
        }
        $order = new Orders();
        foreach($trans as $pres => $double){
            $sql = "SELECT o.order_id,c.target,c.customer_id,c.code,c.relation_code,c.name,pu.purchase_name FROM {{order}} AS o LEFT JOIN {{customer}} AS c ON c.customer_id=o.customer_id LEFT JOIN {{purchase}} AS pu ON pu.purchase_id = c.purchase_id WHERE c.customer_id='{$double['customer_id']}'";
            $result = $this->QueryRow($sql);
            $result['count'] = $order->getCustomerNewCount($result['order_id']);
            $rest[$pres]  = $result;
        }
        foreach($rest as $key => $val){
            $arr[$val['relation_code']]['target'] = isset($arr[$val['relation_code']]['target']) ? $arr[$val['relation_code']]['target'] : 0;
            $arr[$val['relation_code']]['count'] = isset($arr[$val['relation_code']]['count']) ? $arr[$val['relation_code']]['count'] : 0;
            $arr[$val['relation_code']]['order_id'] = isset($arr[$val['relation_code']]['order_id']) ? $arr[$val['relation_code']]['order_id'] : "";
            $arr[$val['relation_code']]['customer_id'] = isset($arr[$val['relation_code']]['customer_id']) ? $arr[$val['relation_code']]['customer_id'] : "";
            $arr[$val['relation_code']]['code'] = isset($arr[$val['relation_code']]['code']) ? $arr[$val['relation_code']]['code'] : "";
            $arr[$val['relation_code']]['name'] = isset($arr[$val['relation_code']]['name']) ? $arr[$val['relation_code']]['name'] : "";
            $arr[$val['relation_code']]['purchase_name'] = isset($arr[$val['relation_code']]['purchase_name']) ? $arr[$val['relation_code']]['purchase_name'] : "";
            $arr[$val['relation_code']]['target'] += $val['target'];
            $arr[$val['relation_code']]['count'] += $val['count'];
            $arr[$val['relation_code']]['order_id'] .= $val['order_id'].",";
            $arr[$val['relation_code']]['customer_id'] .= $val['customer_id'].",";
            $arr[$val['relation_code']]['code'] .= $val['code'].",";
            $arr[$val['relation_code']]['name'] .= $val['name'].",";
            $arr[$val['relation_code']]['purchase_name'] .= $val['purchase_name'].",";
        }
        return $arr;
    }

    public function getCustomerOrderInfo(){
        $order = new Orders();
        //所有用户的订单真实订货量
        $sql = "SELECT o.order_id FROM {{order}} AS o LEFT JOIN {{customer}} AS c ON c.customer_id=o.customer_id WHERE o.disabled='false' AND c.disabled='false'";
        $real_target = $this->QueryAll($sql);
        $real_tar = 0;
        foreach($real_target as $val){
            $real_tar += $order->getCustomerNewCount($val['order_id']);
        }
        //用户的原先指标
        $sql = "SELECT SUM(target) AS target FROM {{customer}} WHERE disabled='false'";
        $target = $this->QueryRow($sql);
        //所有用户提交审核的订货量
        $sql = "SELECT o.order_id FROM {{order}} AS o LEFT JOIN {{customer}} AS c ON c.customer_id=o.customer_id WHERE o.status='finish' AND o.disabled='false' AND c.disabled='false'";
        $real_target = $this->QueryAll($sql);
        $finish_tar = 0;
        foreach($real_target as $val){
            $finish_tar += $order->getCustomerNewCount($val['order_id']);
        }
        $result['real_target'] = $real_tar;
        $result['des_target'] = $target['target'];
        $result['fin_target'] = $finish_tar;
        return $result;
    }

    /**
     * 检查product_sn 是否重复
     * @return array|mixed
     */
    public function checkProductSnIsRight(){
        $res = $this->QueryAll("SELECT COUNT(*) AS counts, product_sn,model_sn,serial_num,style_sn,product_id FROM {{product}} GROUP BY product_sn HAVING counts>1");
        if(empty($res)) return array();
        foreach($res as $val){
            $val['why'] = "product_sn 重复";
            $sqlData = $this->selectQueryRow("COUNT(*) AS counts", "{{product}}", "product_sn='{$val['product_sn']}' AND disabled='false'");
            if($sqlData['counts'] <= 1){
                $val['status'] = "已处理";
            }else{
                $val['status'] = "未处理";
            }
            $arr[] = $val;
        }
        return $arr;
    }

    /**
     * 检查色号与尺码是否重复
     * @return array
     */
    public function checkModelSizeColorRepeat(){
        $sql = "SELECT * FROM {{product}}";
        $productLists = $this->QueryAll($sql);
        if(empty($productLists)) return array();
        foreach ($productLists as $v) {
            $items[$v['product_id']] = $v['style_sn'].'_'.$v['size_id'];
        }
        $list = array_diff_assoc($items,array_unique($items));
        if(empty($list)) return array();
        foreach($list as $key => $val){
            $product_info = explode("_", $val);
            $sqlData = $this->selectQueryRow("COUNT(*) AS counts", "{{product}}", "style_sn='{$product_info[0]}' AND size_id='{$product_info[1]}' AND disabled='false'");
            if(empty($sqlData['counts'])){
                $res['status'] = "已处理";
            }else{
                $sqlData = $this->selectQueryRow("COUNT(*) AS counts", "{{product}}", "style_sn='{$product_info[0]}' AND size_id='{$product_info[1]}' AND is_down='0'");
                if(empty($sqlData)){
                    $res['status'] = "已处理";
                }else{
                    $res['status'] = "未处理";
                }
            }
            $sqlData = $this->selectQueryRow("product_sn,model_sn,serial_num,style_sn,product_id", "{{product}}", "style_sn='{$product_info[0]}' AND size_id='{$product_info[1]}' ");
            if(empty($sqlData)){
                continue;
            }
            $res['product_id'] = $sqlData['product_id'];
            $res['model_sn'] = $sqlData['model_sn'];
            $res['serial_num'] = $sqlData['serial_num'];
            $res['style_sn'] = $sqlData['style_sn'];
            $res['product_sn'] = $sqlData['product_sn'];
            $res['why'] = "款号、颜色、尺寸重复";
            $arr[] = $res;
        }
        return $arr;
    }

    /**
     * 合并上述错误
     * @param $array1
     * @param $array2
     * @return array
     */
    public function checkThisProductListsError($array1, $array2){
        $i = 1;
        $arr = array();
        if(!empty($array1)){
            foreach($array1 as $val){
                $arr[$i] = $val;
                $i++;
            }
        }
        if(!empty($array2)){
            foreach($array2 as $val){
                $arr[$i] = $val;
                $i++;
            }
        }
        return $arr;
    }

    /**
     * 检查是否有错误信息
     * @return bool
     */
    public function checkDoHaveErrorProducts(){
        $res = $this->selectQueryRow("COUNT(*) AS counts", "{{product}}", "is_error='true' AND disabled='false'");
        if($res['counts'] >= 1){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 一键处理错误商品
     * @return bool
     */
    public function dealWithErrorProduct(){
        $this->ModelExecute("UPDATE {{product}} SET disabled='true' WHERE is_error='true'");
        return true;
    }

    /**
     * 获取重复的product_sn
     * @return string
     */
    public function getAllErrorProducts(){
        $sql = "SELECT COUNT(*) AS counts,product_sn FROM {{product}} WHERE is_error='false' GROUP BY product_sn HAVING counts>=2";
        $res = $this->QueryAll($sql);
        if(empty($res)){
            return array();
        }
        $product_sn = "";
        foreach($res as $val) {
            $product_sn .= "," . $val['product_sn'];
        }
        $product_sn = substr($product_sn, 1);
        return $product_sn;
    }

    public function getAllErrorSerialNumRepeat(){
        $sql = "SELECT * FROM {{product}} WHERE is_error='false'";
        $productLists = $this->QueryAll($sql);
        if(empty($productLists)) return array();
        foreach ($productLists as $v) {
            $items[$v['product_id']] = $v['style_sn'].'_'.$v['size_id'];
        }
        $list = array_diff_assoc($items,array_unique($items));
        if(empty($list)) return array();
        foreach($list as $key => $val){
            $product_info = explode("_", $val);
            $sql = "SELECT product_id,is_error,product_sn,serial_num,name FROM {{product}} WHERE style_sn='{$product_info[0]}' AND size_id='{$product_info[1]}'";
            $result = $this->QueryAll($sql);
            foreach($result as $v){
                $arr[] = $v;
            }
        }
        return $arr;
    }

    /**
     * 判断此商品是否被购买
     * @param $product_id
     * @return bool
     */
    public function deleteErrorProducts($product_id){
        $sql = "SELECT COUNT(*) AS counts FROM {{order_items}} WHERE product_id='{$product_id}' AND disabled='false'";
        $res = $this->QueryRow($sql);
        if(empty($res['counts'])){
            $sql = "UPDATE {{product}} SET is_error='true' WHERE product_id='{$product_id}'";
            if($this->Execute($sql)){
                return array('code' =>'200','msg'=>'处理成功');
            }else{
                return array('code' =>'400','msg'=>'处理失败');
            }
        }else{
            return array('code' =>'400','msg'=>'商品已被购买');
        }
    }
}

