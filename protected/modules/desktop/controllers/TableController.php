<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 15-3-6
 * Time: 上午11:34
 */

class TableController extends BaseController
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
    public $parice_level;
    public function init()
    {
        $tableModel = new Table();
        $this->purchase = $tableModel->purchaseList();
        $this->brand = $tableModel->brandList();
        $this->cat_b = $tableModel->bigCatList();
        $this->cat_m = $tableModel->middleCatList();
        $this->cat_s = $tableModel->smallCatList();
        $this->wave = $tableModel->waveList();
        $this->scheme = $tableModel->schemeList();
        $this->season = $tableModel->seasonList();
        $this->color = $tableModel->colorList();
        $this->size = $tableModel->sizeList();
        $this->level = $tableModel->levelList();

        $this->parice_level = array(
            '0-99'=>1,
            '100-199'=>2,
            '200-299'=>3,
            '300-399'=>4,
            '400-499'=>5,
            '500-999'=>6,
            '1000-1499'=>7,
            '1500-2000'=>8,
            '2000以上'=>9,
        );
    }
    public function actionIndex()
    {
        /*
         * 数据库整表缓存
         */
        Yii::app()->cache->delete('purchase-list');
        Yii::app()->cache->delete('brand-list');
        Yii::app()->cache->delete('big-cat-list');
        Yii::app()->cache->delete('middle-cat-list');
        Yii::app()->cache->delete('small-cat-list');
        Yii::app()->cache->delete('season-list');
        Yii::app()->cache->delete('scheme-list');
        Yii::app()->cache->delete('wave-list');
        Yii::app()->cache->delete('level-list');
        Yii::app()->cache->delete('color-list');
        Yii::app()->cache->delete('size-list');

        /*
         * 首页左侧分类缓存
         */
        Yii::app()->cache->delete('cat_big_small_list');
    }


    /**
     * 商品数据导入
     */
    public function actionProduct()
    {
        $tableModel = new Table();
        $filename = 'product16.csv';
        $result = ErpCsv::importCsvData($filename);
        $len_result = count($result);
        $data_values = '';

        $keys = 'purchase_id,product_sn,style_sn,model_sn,serial_num,name,img_url,color_id,size_id,brand_id,cat_b,
        cat_m,cat_s,season_id,level_id,wave_id,scheme_id,cost_price,price_level_id,memo';
        for ($i = 1,$num=1; $i < $len_result; $i++,$num++) {
            $purchase_id = $result[$i][1]=='2016OCT春夏订货会'?1:2;
            $color_id = $this->color[$result[$i][5]]['color_id'];
            $size_id = $this->size[$result[$i][6]]['size_id'];
            $brand_id = $this->brand[$result[$i][2]]['brand_id'];
            $b_cat_id = $this->cat_b[$result[$i][7]]['big_id'];
            $m_cat_id = $this->cat_m[$result[$i][8]]['middle_id'];
            $s_cat_id = $this->cat_s[$result[$i][9]]['small_id'];
            $season_id = $this->season[$result[$i][10]]['season_id'];
            $wave_id = $this->wave[$result[$i][11]]['wave_id'];
            $level_id = $this->level[$result[$i][12]]['level_id'];
            $scheme_id = $this->scheme[$result[$i][13]]['scheme_id'];
            $price_level_id = $this->parice_level[$result[$i][14]];
            $serial_num = $result[$i][4];
            $cost_price = $result[$i][15];
            $memo = $result[$i][16];
            $name = $result[$i][3];
            $model_sn = $result[$i][0];
            if ($i>1 && $model_sn != $result[$i-1][0]){
                $num = 1;
            }
            $style_sn = $model_sn.sprintf('%04d',$this->color[$result[$i][5]]['color_no']);
            $product_sn = $style_sn.sprintf('%03d',$num);
            $img_url = '/images/'.$model_sn.'_'.$this->color[$result[$i][5]]['color_no'].'.jpg';
            $data_values[] = "('{$purchase_id}','{$product_sn}','{$style_sn}','{$model_sn}','{$serial_num}','{$name}','{$img_url}',
            '{$color_id}','{$size_id}','{$brand_id}','{$b_cat_id}','{$m_cat_id}','{$s_cat_id}','{$season_id}',
            '{$level_id}','{$wave_id}','{$scheme_id}','{$cost_price}','{$price_level_id}','{$memo}')";
        }
        $data = implode(',',$data_values);
        $table=new Table();
        $connection=Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try
        {
            $sqls = "INSERT INTO {{product}} ($keys) VALUES {$data};";
            $tableModel->ModelExecute($sqls);
            $js=$table->productLists();
            if($js!='ok'){
                $transaction->rollBack();
            }
            echo "上传成功";
            $transaction->commit();
        }
        catch(Exception $e)
        {
            $transaction->rollBack();
        }
    }

    /**
     *
     * 再次检查product表是否有有重复 !important
     *
     */
    public function actionCheck()
    {
        $table=new Table();
        $table->productLists();
    }


    /**
     * 上传用户资料
     */
    public function actionPassword()
    {
        $tableModel = new Table();
        $filename = 'customer2.csv';
        $result = ErpCsv::importCsvData($filename);
        $len_result = count($result);
        $data_values = '';
        $keys = 'purchase_id,code,name,password,mobile,type,province,area,target,leader,leader_name,agent,department,parent_id';
        for ($i = 1; $i < $len_result; $i++) {
            //$customer_id = '10000'.sprintf('%04d',$i);
            $purchase_id = $result[$i][3]=='2016OCT春夏订货会'?1:2;
            $code = ltrim($result[$i][0],"'");
            $name = $result[$i][1];
            $password=md5(md5(substr($result[$i][2] ,-4)));
            $mobile = $result[$i][2];
            $type = $result[$i][5];
            $province = $result[$i][7];
            $area = $result[$i][6];
            $target = $result[$i][4];
            $leader=$result[$i][8];
            $leader_name=$result[$i][9];
            $agent=ltrim($result[$i][10],"'");
            if($code==$agent){
                $parent_id=1;
            }else{
                $parent_id=0;
            }
            $department=$result[$i][11];
            $data_values[] = "({$purchase_id},'{$code}','{$name}','{$password}','{$mobile}','{$type}','{$province}','{$area}','{$target}','{$leader}','{$leader_name}','{$agent}','{$department}','{$parent_id}')";
        }
        $data = implode(',',$data_values);
        $connection=Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try
        {
            $sql = "INSERT INTO {{customer}} ($keys) VALUES {$data};";
            $tableModel->ModelExecute($sql);
            echo "上传成功";
            $transaction->commit();
        }
        catch(Exception $e)
        {
            $transaction->rollBack();
        }
    }

    public function actionOrder()
    {
        $table = new Table();
        $list = $table->items();
        print_r ($list);
    }
} 