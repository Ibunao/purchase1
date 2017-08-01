<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 15-3-6
 * Time: 上午11:36
 */

class Table extends B2cModel
{
    /**
     * 获取今日登陆的用户数量
     */
    public function getAllDateNewLogin(){
        $today_time = strtotime(date("Y-m-d 00:00:00"));
        $tom_time   = $today_time + 86400;
        $sql = "select * FROM {{customer}} where login>{$today_time} and login<{$tom_time} group by customer_id";
        return $this->ModelQueryAll($sql);
    }

    /**
     * 订单统计
     * @param string $today
     * @return mixed
     */
    public function getAllOrderNum($today = ''){
        if(!empty($today)) {
            $where = " AND status='finish'";
        }else{
            $where = " AND status='active'";
        }
        $sql = "SELECT SUM(cost_item) AS nums FROM {{order}} WHERE disabled='false' {$where}";
        $res = $this->ModelQueryRow($sql);
        if(empty($res)) return $res['nums'] = 0;
        return $res;
    }

    /**
     * 订单统计
     *
     * @param bool|false $type
     * @return int|mixed
     */
    public function getOrderNumbers($type = false){
        if ($type == 'jm') {
            $where = " c.type='客户' AND o.disabled='false'";
        }elseif($type == 'jm_active'){
            $where = " c.type='客户' AND o.status='active' AND o.disabled='false'";
        }elseif($type == 'jm_confirm'){
            $where = " c.type='客户' AND o.status='confirm' AND o.disabled='false'";
        }elseif($type == 'jm_finish'){
            $where = " c.type='客户' AND o.status='finish' AND o.disabled='false'";
        }elseif ($type == 'zy') {
            $where = " c.type='直营' AND o.disabled='false'";
        }else{
            $where = " o.disabled='false'";
        }
        $sql = "SELECT SUM(cost_item) AS nums FROM {{order}} AS o LEFT JOIN {{customer}} AS c ON c.customer_id=o.customer_id WHERE {$where}";
        $res = $this->ModelQueryRow($sql);
        if(empty($res)) return $res['nums'] = 0;
        return $res;
    }

    /**
     * 获取所有客户的指标
     *
     * @param bool|false $type
     * @return mixed
     */
    public function getAllUserTarget($type = false){
        if($type == 'jm'){
            $where = " type='客户' AND disabled='false'";
        }elseif ($type == 'zy') {
            $where = " type='直营' AND disabled='false'";
        }else{
            $where = " disabled='false'";
        }
        $sql = "SELECT SUM(target) AS targets FROM {{customer}} WHERE {$where}";
        return $this->ModelQueryRow($sql);
    }

    /**
     * 订货会
     *
     * @return mixed
     */
    public function purchaseList()
    {
        $items = Yii::app()->cache->get('purchase-list');
        if (!$items) {
            $sql = "SELECT * FROM {{purchase}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['purchase_name']] = $v;
            }
            Yii::app()->cache->set('purchase-list',$items,3600*24);
        }
        return $items;
    }

    /**
     * 品牌
     *
     * @return mixed
     */
    public function brandList()
    {
        $items = Yii::app()->cache->get('brand-list');
        if (!$items) {
            $sql = "SELECT * FROM {{brand}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['brand_name']] = $v;
            }
            Yii::app()->cache->set('brand-list',$items,3600*24);
        }
        return $items;
    }

    /**
     * 大分类
     *
     * @return mixed
     */
    public function bigCatList()
    {
        $items = Yii::app()->cache->get('big-cat-list');
        if (!$items) {
            $sql = "SELECT * FROM {{cat_big}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['cat_name']] = $v;
            }
            Yii::app()->cache->set('big-cat-list',$items,3600*24);
        }
        return $items;
    }

    /**
     * 中分类
     *
     * @return mixed
     */
    public function middleCatList()
    {
        $items = Yii::app()->cache->get('middle-cat-list');
        if (!$items) {
            $sql = "SELECT * FROM {{cat_middle}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['cat_name']] = $v;
            }
            Yii::app()->cache->set('middle-cat-list',$items,3600*24);
        }
        return $items;
    }

    /**
     * 小分类
     *
     * @return mixed
     */
    public function smallCatList()
    {
        $items = Yii::app()->cache->get('small-cat-list');
        if (!$items) {
            $sql = "SELECT * FROM {{cat_small}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['cat_name']] = $v;
            }
            Yii::app()->cache->set('small-cat-list',$items,3600*24);
        }
        return $items;
    }

    /**
     * 季节列表
     *
     * @return mixed
     */
    public function seasonList()
    {
        $items = Yii::app()->cache->get('season-list');
        if (!$items) {
            $sql = "SELECT * FROM {{season}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['season_name']] = $v;
            }
            Yii::app()->cache->set('season-list',$items,3600*24);
        }
        return $items;
    }

    /**
     * 色系列表
     *
     * @return mixed
     */
    public function schemeList()
    {
        $items = Yii::app()->cache->get('scheme-list');
        if (!$items) {
            $sql = "SELECT * FROM {{scheme}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['scheme_name']] = $v;
            }
            Yii::app()->cache->set('scheme-list',$items,3600*24);
        }
        return $items;
    }

    /**
     * 波段列表
     *
     * @return mixed
     */
    public function waveList()
    {
        $items = Yii::app()->cache->get('wave-list');
        if (!$items) {
            $sql = "SELECT * FROM {{wave}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['wave_name']] = $v;
            }
            Yii::app()->cache->set('wave-list',$items,3600*24);
        }
        return $items;
    }

    /**
     * 波段列表
     *
     * @return mixed
     */
    public function levelList()
    {
        $items = Yii::app()->cache->get('level-list');
        if (!$items) {
            $sql = "SELECT * FROM {{level}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['level_name']] = $v;
            }
            Yii::app()->cache->set('level-list',$items,3600*24);
        }
        return $items;
    }

    /**
     * 颜色列表
     *
     * @return mixed
     */
    public function colorList()
    {
        $items = Yii::app()->cache->get('color-list');
        if (!$items) {
            $sql = "SELECT * FROM {{color}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['color_name']] = $v;
            }
            Yii::app()->cache->set('color-list',$items,3600*24);
        }
        return $items;
    }

    public function colorListNo(){
        $items = Yii::app()->cache->get('color-list-num');
        if (!$items) {
            $sql = "SELECT * FROM {{color}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['color_no']] = $v;
            }
            Yii::app()->cache->set('color-list-num',$items,3600*24);
        }
        return $items;
    }


    public function colorSchemeTrans(){
        $items = Yii::app()->cache->get('color-scheme-list-num');
        if (!$items) {
            $sql = "SELECT * FROM {{color}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['color_id']] = $v;
            }
            Yii::app()->cache->set('color-scheme-list-num',$items,3600*24);
        }
        return $items;
    }
    /**
     * 类型列表
     *
     * @return mixed
     */
    public function typeList()
    {
        $items = Yii::app()->cache->get('type-list');
        if (!$items) {
            $sql = "SELECT * FROM {{type}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['type_name']] = $v;
            }
            Yii::app()->cache->set('type-list',$items,3600*24);
        }
        return $items;
    }

    /**
     * 尺码列表
     *
     * @return mixed
     */
    public function sizeList()
    {
        $items = Yii::app()->cache->get('size-list');
        if (!$items) {
            $sql = "SELECT * FROM {{size}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['size_name']] = $v;
            }
            Yii::app()->cache->set('size-list',$items,3600*24);
        }
        return $items;
    }

    public function items()
    {
        $item_list = $this->ModelQueryAll("select i.order_id,i.style_sn,z.size_id from meet_order_items i LEFT JOIN meet_product p ON i.product_id = p.product_id
LEFT JOIN meet_color co ON p.color_id = co.color_id LEFT JOIN meet_size z ON p.size_id = z.size_id
where i.disabled = 'false' ;");

        foreach ($item_list as $v) {
            if (!isset($items[$v['order_id'].'_'.$v['style_sn'].'_'.$v['size_id']]))
                $items[$v['order_id'].'_'.$v['style_sn'].'_'.$v['size_id']] = 1;
            else $items[$v['order_id'].'_'.$v['style_sn'].'_'.$v['size_id']] += 1;
        }

        $arr = array();
        foreach ($items as $k=>$v) {
            if ($v > 1) $arr[] = $k.'_'.$v;
        }
        return $arr;

        foreach ($arr as $v) {
            $tmp = explode('_',$v);
            $arr_new[] = $tmp[0];
        }
        return array_unique($arr_new);
    }

    public function goodsItems()
    {
        $product_list = $this->ModelQueryAll("SELECT * FROM {{product}}");
        $items = array();
        foreach ($product_list as $v) {
            if (!isset($items[$v['style_sn'].'_'.$v['size_id']]))
                $items[$v['style_sn'].'_'.$v['size_id']] = 1;
            else $items[$v['style_sn'].'_'.$v['size_id']] += 1;
        }

        $arr = array();
        foreach ($items as $k=>$v) {
            if ($v > 1) $arr[] = $k.'_'.$v;
        }

        foreach ($arr as $v) {
            $tmp = explode('_',$v);
            $arr_new[] = $tmp[0];
        }
        return array_unique($arr_new);
    }

    public function getProductList($model_sn){
        $sql = "SELECT COUNT(*) AS nums FROM {{product}} WHERE model_sn='{$model_sn}' AND is_down='0' AND disabled='false'";
        return $this->ModelQueryRow($sql);
    }


    public function productList()
    {
        $items = Yii::app()->cache->get('size-list');
        if (!$items) {
            $sql = "SELECT * FROM {{product_db}}";
            $list = $this->ModelQueryAll($sql);
            foreach ($list as $v) {
                $items[$v['product_id']] = $v['style_sn'].'_'.$v['size_id'];
            }
        }
        $jd=array_diff_assoc($items,array_unique($items));
        if(empty($jd)) {
            return 'ok';
        }else{
            echo "product_bk有重复";
            var_dump($jd);
            die;
        }
    }


    public function productLists()
    {
        $sql = "SELECT * FROM {{product}}";
        $list = $this->ModelQueryAll($sql);
        foreach ($list as $v) {
            $items[$v['product_id']] = $v['style_sn'].'_'.$v['size_id'];
        }
        $jd=array_diff_assoc($items,array_unique($items));
        if(empty($jd)) {
            echo 'ok,no repeat';
            die;
        }else{
            echo "product表有重复";
            var_dump($jd);
            die;
        }
    }

    public function checkRepeat(){
        $sql = "select * from {{product}} where model_sn not in( select * from (select max(model_sn) from {{product}} group by model_sn ) as tmp)";
        return $this->ModelQueryAll($sql);
    }

    /**
     * 检查是否有错误
     * @return array
     */
    public function getRepeatProducts(){
        $sql = "SELECT count(*) AS counts, product_sn FROM {{product}} GROUP BY product_sn HAVING counts>=2";
        $res = $this->ModelQueryAll($sql);
        if(empty($res)){
            return array();
        }
        $productSn = "";
        foreach($res as $val){
            $productSn .= ",".$val['product_sn'];
        }
        $productSn = substr($productSn, 1);
        $sql = "SELECT product_id FROM {{product}} WHERE product_sn IN (".$productSn.")";
        $result = $this->ModelQueryAll($sql);
        foreach($result as $val){
            $val['is_order'] = false;
            $res = $this->ModelQueryRow("SELECT COUNT(*) AS counts FROM {{order_items}} WHERE product_id='{$val['product_id']}' AND disabled='false'");
            if(!empty($res['counts'])){
                $val['is_order'] = true;
            }
            $arr[] = $val;
        }
        return $arr;
    }
}