<?php

/**
 * Created by PhpStorm.
 * User: zangmiao
 * Date: 2015/7/16
 * Time: 9:57
 */
class ProductController extends BaseController
{
    public $admin;
    public $currentDir = '';

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
                'layer/layer.min'
            )
        );
    }

    /**
     * 清缓存
     */
    public function _clear()
    {
        $res = Yii::app()->params['flush_cache_url'];
        foreach ($res as $val) {
            file_get_contents($val . '/user/cache');
        }
    }

    /**
     * 一键处理错误的商品
     */
    public function actionDealError()
    {
        $product = new Product();
        if ($product->dealWithErrorProduct()) {
            echo "<script>alert('处理完成');history.go(-1);</script>";
        }
    }

    /**
     * 管理商品控制器
     *
     */
    public function actionIndex()
    {
        $pageIndex = isset($_GET['page']) ? $this->get('page') : 1;
        $param = $this->get("param");

        $productModel = new Product();
        $selectFilter = $productModel->getIndexFilter($param);   //获取筛选下拉框中的数据

        $resultData = $productModel->manageSelectLikeSearch($param, $pageIndex); //查询

        $countAll = $productModel->countAllData($param);      //统计结果总数
        $pages = new CPagination($countAll['countAll']);   //获取整个数据的条目数量

        $res = $productModel->checkDoHaveErrorProducts();
        $this->render('index', array(
            'param' => $param,             //get数据
            'selectFilter' => $selectFilter,      //下拉框自带参数
            'select_option' => $resultData,        //显示搜索的结果
            'pages' => $pages,             //分页需要
            'pageIndex' => $pageIndex - 1,     //分页参数
            'is_error' => $res
        ));
    }

    /**
     * 添加商品模块控制器
     */
    public function actionAdd()
    {
        $productModel = new Product();
        $guestModel = new GuestManage();
        $param = $this->post('param');
        if (!empty($param)) {
            $res = $productModel->addProductOperation($param);
            if ($res) {
                $this->_clear();
                $guestModel->breakAction('添加成功', "/admin.php?r=order/product/index");
            } else {
                $guestModel->breakActions('添加失败');
            }
        }
        $result = $productModel->getProductFilter();
        $this->render('add', array(
            'selectFilter' => $result
        ));
    }

    /**
     * 商品复制控制器(根据流水号添加商品)
     */
    public function actionCopy()
    {
        $productModel = new Product();
        $guestModel = new GuestManage();
        $serialNum = $this->get("serial_num");
        if (empty($serialNum)) {
            $guestModel->breakActions('ERROR : 流水号为空');
        }
        //数据库的显示数据
        $results = $productModel->selectQueryRow("p.purchase_id, p.brand_id, p.model_sn, p.name, p.cat_b, p.cat_m, p.cat_s, p.season_id, p.level_id, p.wave_id, p.scheme_id, p.cost_price, p.memo, p.is_down, p.size_id, p.type_id, s.group_id AS sizeGroup", "{{product}} AS p LEFT JOIN {{size}} AS s ON s.size_id=p.size_id", "p.serial_num = '{$serialNum}' AND p.disabled='false'");
        $param = $this->post('param');
        if (!empty($param)) {
            $param['brand'] = $results['brand_id'];
            $param['catBig'] = $results['cat_b'];
            $param['catMiddle'] = $results['cat_m'];
            $param['catSmall'] = $results['cat_s'];
            $param['season'] = $results['season_id'];
            $param['scheme'] = $results['scheme_id'];
            $param['level'] = $results['level_id'];
            $param['wave'] = $results['wave_id'];
            $param['purchase'] = $results['purchase_id'];
            $param['type'] = $results['type_id'];
            $param['costPrice'] = $results['cost_price'];
            $res = $productModel->addProductOperation($param);
            if ($res) {
                $this->_clear();
                $guestModel->breakAction('添加成功', "/admin.php?r=order/product/index");
            } else {
                $guestModel->breakActions('添加失败');
            }
        }
        $result = $productModel->getProductFilter($results);
        $this->render('copy', array(
            'selectFilter' => $result,
            'param' => $results,
        ));
    }

    /**
     * 商品修改控制器
     */
    public function actionUpdate()
    {
        $productModel = new Product();
        $guestModel = new GuestManage();
        $serialNum = $this->get("serial_num");
        if (empty($serialNum)) {
            echo "没有流水号";
            die;
        }

        //该流水号的商品信息
        $param = $productModel->selectQueryRow("p.*, s.group_id AS sizeGroup", "{{product}} AS p LEFT JOIN {{size}} AS s ON s.size_id=p.size_id", "p.serial_num = '{$serialNum}' AND p.disabled='false'");

        //size 已选尺码
        $param['size'] = array();

        $paramSize = $productModel->selectQueryRows("size_id, product_sn", "{{product}}", "serial_num='{$serialNum}' AND disabled='false' GROUP BY size_id");
        foreach ($paramSize as $val) {
            $param['size'][] = $val['size_id'];
        }

        //自带下拉列表
        $result = $productModel->getProductFilter($param);
        $this->render('update', array(
            'selectFilter' => $result,
            'param' => $param,
        ));

        //post数据
        $postParam = $this->post("param");
        if (!empty($postParam)) {
            $moreData = array_diff($postParam['size'], $param['size']);//新多出的size数据
            $lessData = array_diff($param['size'], $postParam['size']); //少了的size数据
            $res = $productModel->updateProductOperation($postParam, $moreData, $lessData, $serialNum);
            $this->_clear();
            if ($res) {
                $guestModel->breakAction('修改成功', "/admin.php?r=order/product/index");
            } else {
                $guestModel->breakAction('此款号出现多个货号，禁止修改', '/admin.php?r=order/product/update&serial_num=' . $serialNum);
            }
        }
    }

    /**
     * 以款号添加
     */
    public function actionChange()
    {
        $productModel = new Product();
        $guestModel = new GuestManage();
        $modelSn = $this->get('modelSn');
        if (empty($modelSn)) {
            $guestModel->breakActions('ERROR : 款号为空');
        }
        $results = $productModel->selectQueryRow("p.purchase_id, p.brand_id, p.model_sn, p.name, p.cat_b, p.cat_m, p.cat_s, p.season_id, p.level_id, p.wave_id, p.scheme_id, p.cost_price, p.memo, p.is_down, p.size_id, p.type_id, s.group_id AS sizeGroup", "{{product}} AS p LEFT JOIN {{size}} AS s ON s.size_id=p.size_id", "p.model_sn = '{$modelSn}' AND p.disabled='false'");
        $param = $this->post('param');
        if (!empty($param)) {
            $param['type'] = $results['type_id'];
            $param['purchase'] = $results['purchase_id'];
            $param['brand'] = $results['brand_id'];
            $param['catBig'] = $results['cat_b'];
            $param['catMiddle'] = $results['cat_m'];
            $param['catSmall'] = $results['cat_s'];
            $param['season'] = $results['season_id'];
            $param['scheme'] = $results['scheme_id'];
            $param['level'] = $results['level_id'];
            $param['wave'] = $results['wave_id'];
            $param['costPrice'] = $results['cost_price'];
            $res = $productModel->addProductOperation($param);
            if ($res) {
                $this->_clear();
                $guestModel->breakAction('添加成功', "/admin.php?r=order/product/index");
            } else {
                $guestModel->breakActions('添加失败');
            }
        }
        $result = $productModel->getProductFilter($results);
        $this->render('change', array(
            'selectFilter' => $result,
            'param' => $results,
        ));
    }

    /**
     * 标记错误商品
     */
    public function actionDeleteErrorProduct()
    {
        $product_id = $this->post("product_id");
        $product = new Product();
        $res = $product->deleteErrorProducts($product_id);
        echo json_encode($res);
    }

    /**
     * 检查错误页面
     */
    public function actionCheck()
    {
        $product = new Product();
        $product_sn = $product->getAllErrorProducts();
        $product_info = $product->getAllErrorSerialNumRepeat();
        $result = array();
        if (!empty($product_sn)) {
            $result = $product->selectQueryRows("product_id,is_error,product_sn,serial_num,name", "{{product}}", "product_sn IN (" . $product_sn . ")");
        }
        $all = array_merge($result, $product_info);
        if (empty($all)) {
            echo "<script>alert('暂无错误');location.href='/admin.php?r=order/product/index';</script>";
            die;
        }
        foreach ($all as $val) {
            $res = $product->selectQueryRow("COUNT(*) AS counts", "{{order_items}}", "product_id='{$val['product_id']}' AND disabled='false'");
            $val['is_order'] = $res['counts'];
            $arr[] = $val;
        }
        $res = $product->checkDoHaveErrorProducts();
        $this->render('check', array(
            'result' => $arr,
            'is_error' => $res,
        ));
    }

    /**
     * 导入界面
     */
    public function actionImport()
    {
        if (!Yii::app()->params['product_include']) {
            echo "502 forbidden";
            die;
        }
        $this->render('import');
    }

    /**
     * 上传CSV
     */
    public function actionImportFiles()
    {

        $postFile = isset($_FILES["file"]) ? $_FILES['file'] : exit("请上传文件");

        $postFileType = pathinfo($postFile['name'], PATHINFO_EXTENSION);
        $allowExt = array('xls');
        if (empty($postFile)) {
            exit("请上传文件");
        }

        if (!in_array($postFileType, $allowExt)) {
            exit("上传文件不支持类型，仅限传xls后缀名文件,请先下载导入模板再执行操作");
        }

        if (!is_uploaded_file($postFile['tmp_name'])) {
            exit("不是通过HTP POST上传的文件");
        }
        $productModel = new Product();
        $nowTime = time();
        $newFileName = $nowTime . "." . $postFileType;
        $newFolder = date("Ymd", time());
        $transData = $newFolder . "/" . $newFileName;   //上传文件地址
        $newFolderPath = "images/" . $newFolder . "/"; //新地址
        if (!file_exists($newFolderPath)) mkdir($newFolderPath, 0777);
        $newFile = Yii::app()->basePath . "/../" . $newFolderPath . "/" . $newFileName;
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $newFile)) {
            //CSV
//            $handle = fopen($newFile, 'r');
//            $result = ErpCsv::input_csv($handle);

            //xls
            Yii::$enableIncludePath = false; // 不自动加载
            Yii::import('application.extensions.PHPExcel', 1);
            $objPHPExcel = new PHPExcel();
            $objPHPExcel = PHPExcel_IOFactory::load($newFile);
            $result = $objPHPExcel->getActiveSheet()->toArray();

            $len_result = count($result);
            if ($len_result <= 1) {
                echo "<script>alert('表中没有相关数据，请检查');</script>";
                die;
            }

            $purchase = array(
                Yii::app()->params['purchase_oct'],
                Yii::app()->params['purchase_uki'],
            );
            $res_str = "";
            $export = new Export();
            for ($i = 1; $i < $len_result; $i++) {

                $warning = "";

                if (empty($result[$i][0])) {
                    $warning .= "<span><b>款号为空</b></span>";
                } elseif (strlen($result[$i][0]) < 7) {
                    $warning .= "<span><b>款号小于7位数</b></span>";
                } else {
                    $model_sn = $result[$i][0];
                }

                if (!in_array($result[$i][1], $purchase)) {
                    $warning .= "<span>订货会<b>" . $result[$i][1] . "</b>有错误</span>";
                }

                if (!isset($export->brand[$result[$i][2]]['brand_id'])) {
                    $warning .= "<span>品牌<b>" . $result[$i][2] . "</b>有错误</span>";
                }

                if (empty($result[$i][3])) {
                    $warning .= "<span><b>品名为空</b></span>";
                }

                if (empty($result[$i][4])) {
                    $warning .= "<span><b>流水号为空</b></span>";
                }

                $color = 2;
                if (!isset($export->color[$result[$i][5]]['color_id'])) {
                    $warning .= "<span>颜色<b>" . $result[$i][5] . "</b>有错误</span>";
                    $color--;
                }

                if (!isset($export->size[$result[$i][6]]['size_id'])) {
                    $warning .= "<span>尺码<b>" . $result[$i][6] . "</b>有错误</span>";
                }

                $size = 3;
                if (!isset($export->cat_b[$result[$i][7]]['big_id'])) {
                    $warning .= "<span>大类<b>" . $result[$i][7] . "</b>有错误</span>";
                    $size--;
                }

                if (!isset($export->cat_m[$result[$i][8]]['middle_id'])) {
                    $warning .= "<span>中类<b>" . $result[$i][8] . "</b>有错误</span>";
                    $size--;
                }

                if (!isset($export->cat_s[$result[$i][9]]['small_id'])) {
                    $warning .= "<span>小类<b>" . $result[$i][9] . "</b>有错误</span>";
                    $size--;
                }

//                if ($size == "3") {
//                    if (!$productModel->checkSizeBigAndSmall($result[$i][7], $result[$i][8], $result[$i][9])) {
//                        $warning .= "<span><b>大类，中类，小类 不匹配</b></span>";
//                    }
//                }

                if($size == '3'){
                    if (!$productModel->checkSizeBigAndSmall($result[$i][7], $result[$i][9])) {
                        $warning .= "<span><b>大类, 小类 不匹配</b></span>";
                    }
                }

                if (!isset($export->season[$result[$i][10]]['season_id'])) {
                    $warning .= "<span>季节<b>" . $result[$i][10] . "</b>有错误</span>";
                }

                if (!isset($export->wave[$result[$i][11]]['wave_id'])) {
                    $warning .= "<span>波段<b>" . $result[$i][11] . "</b>有错误</span>";
                }

                if (!isset($export->level[$result[$i][12]]['level_id'])) {
                    $warning .= "<span>等级<b>" . $result[$i][12] . "</b>有错误</span>";
                }

                if (!isset($export->scheme[$result[$i][13]]['scheme_id'])) {
                    $warning .= "<span>色系<b>" . $result[$i][13] . "</b>有错误</span>";
                    $color--;
                } else {
                    $scheme_id = $export->scheme[$result[$i][13]]['scheme_id'];
                }

                if ($color == 2) {
                    $color_id = $export->color[$result[$i][5]]['scheme_id'];
                    if ($color_id != $scheme_id) {
                        $warning .= "<span><b>色系与颜色不对应</b></span>";
                    }
                }

                $price_isset = 0;
                if (!isset($export->parice_level[$result[$i][14]])) {
                    $warning .= "<span>价格带<b>" . $result[$i][14] . "</b>数据有误</span>";
                    $price_isset++;
                }

                if ($result[$i][15] < 0 || empty($result[$i][15])) {
                    $warning .= "<span><b>吊牌价小于0</b></span>";
                    $price_isset++;
                }

                if (empty($price_isset)) {
                    if ($productModel->_transCostPriceToLevel($result[$i][15]) != $export->parice_level[$result[$i][14]]) {
                        $warning .= "<span><b>吊牌价与价格带不匹配</b></span>";
                    }
                }

                if (empty($result[$i][16])) {
                    $warning .= "<span><b>描述为空</b></span>";
                }

                if(!isset($export->type[$result[$i][17]])){
                    $warning .= "<span>商品类型<b>{$result[$i][17]}</b>有错误</span>";
                }

                //检查同款号下的数据是否相同
                if ($color == 2) {
                    $checkCatRepeat[$result[$i][0]]['purchase'][] = $result[$i][1];
                    $checkCatRepeat[$result[$i][0]]['brand'][] = $result[$i][2];
                    $checkCatRepeat[$result[$i][0]]['cat_big'][] = $result[$i][7];
                    $checkCatRepeat[$result[$i][0]]['cat_middle'][] = $result[$i][8];
                    $checkCatRepeat[$result[$i][0]]['cat_small'][] = $result[$i][9];
                    $checkCatRepeat[$result[$i][0]]['season'][] = $result[$i][10];
                    $checkCatRepeat[$result[$i][0]]['cost_price'][] = $result[$i][15];
                    $checkCatRepeat[$result[$i][0]]['price_lv'][] = $result[$i][14];
                    $checkCatRepeat[$result[$i][0]]['type_id'][] = $result[$i][17];
                }

                //检查流水号下的颜色是否重复
                $checkSerialRepeat[$result[$i][4]][$result[$i][5]] = $result[$i][5];

                //检查该款号下的颜色是否有多个流水号
                $checkModelRepeat[$result[$i][0]][$result[$i][5]][] = $result[$i][4];

                //判断一个颜色只能对应一个流水号
                $checkSerialModelRepeat[$result[$i][4]][] = $result[$i][0];

                //检查此流水号下的尺码是否重复
                $checkSerialSizeRepeat[$result[$i][4]][] = $result[$i][6];

                //检查同一流水号下的商品名是否统一
                $checkSerialName[$result[$i][4]][] = $result[$i][3];

                //检查款号下的大类是否对应的季节
                $checkCatBigSeason[$result[$i][0]][] = $result[$i][7]."_".$result[$i][10];

                if (empty($warning)) {
                    if (!$productModel->checkThisModelColorSizeIsValue($model_sn, $result[$i][4])) {
                        $warning .= "<span><b>此产品流水号/款号已存在,请到商品管理添加修改</b></span>";
                    }
                }

                if (!empty($warning)) {
                    $res_str .= "<p>第" . ($i + 1) . "行 &nbsp;&nbsp;&nbsp;&nbsp;".$result[$i][3] .'&nbsp;&nbsp;&nbsp;&nbsp;'. $warning . "</p>";
                }
            }

            //检查流水号下有多种尺码，请检查！
            if (empty($res_str)) {
                $size = new Size();
                $groupSize = $size->getGroupSize();
                foreach ($checkSerialSizeRepeat as $serialNum => $val) {
                    $group_id = $productModel->selectQueryRow("group_id", "{{size}}", "size_name='{$val[0]}'");
                    $countSize = count($val);
                    $i = 0;
                    foreach ($val as $v) {
                        if (in_array($v, $groupSize[$group_id['group_id']])) {
                            $i++;
                        }
                    }
                    if ($countSize != $i) {
                        if ($i != 0) {
                            $res_str .= "<p><span><b>{$serialNum}流水号下</b>有多种尺码，请检查！</span></p>";
                        }
                    }
                }
            }
            //检查同一流水号下的商品名是否统一
            foreach ($checkSerialName as $serialModel => $val) {
                if (count(array_unique($val)) >= 2) {
                    $res_str .= "<p><span><b>{$serialModel}流水号下</b>有不同的商品名</span></p>";
                }
            }

            //检查同款号下的数据是否相同
            if (empty($res_str)) {
                foreach ($checkCatRepeat as $modelSnKey => $modelValue) {
                    foreach ($modelValue as $nameKey => $resValue) {
                        if (count(array_unique($resValue)) >= 2) {
                            $res_str .= "<p><span><b>{$modelSnKey}款号下</b>有不同的商品数据</span></p>";
                        }
                    }
                }
            }

            //检查流水号下的颜色是否重复
            if (empty($res_str)) {
                foreach ($checkSerialRepeat as $re => $peat) {
                    if (count($peat) >= 2) {
                        $res_str .= "<p><span><b>{$re}流水号</b>下有多种颜色</span></p>";
                    }
                }
            }

            //检查该款号下的颜色是否有多个流水号
            if (empty($res_str)) {
                foreach ($checkModelRepeat as $model => $serial) {
                    foreach ($serial as $val) {
                        if (count(array_unique($val)) >= 2) {
                            $res_str .= "<p><span><b>{$model}款号</b>下的一种颜色有多个流水号</span></p>";
                        }
                    }
                }
            }

            //判断一个颜色只能对应一个流水号
            if (empty($res_str)) {
                foreach ($checkSerialModelRepeat as $key => $val) {
                    if (count(array_unique($val)) >= 2) {
                        $res_str .= "<p><span><b>{$key}流水号</b>下有多个款号</span></p>";
                    }
                }
            }

            //检查此流水号下的尺码是否重复
            if (empty($res_str)) {
                foreach ($checkSerialSizeRepeat as $sizeKey => $sizeValue) {
                    if (count($sizeValue) != count(array_unique($sizeValue))) {
                        $res_str .= "<p><span><b>{$sizeKey}流水号</b>下有重复的尺码</span></p>";
                    }
                }
            }

            //检查此款号下的季节是否相同、对应
            if(empty($res_str)){
                $product = new SeasonBig();
                $season_big = $product->getTrans();
                foreach($checkCatBigSeason as $modelCat => $modelBig){
                    if(count(array_unique($modelBig)) != 1){
                        $res_str .= "<p><span><b>{$modelCat}款号</b>下的有多的大类与季节</span></p>";
                    }else{
                        if(!in_array(array_unique($modelBig), $season_big)){
                            $res_str .= "<p><span><b>{$modelCat}款号</b>下的大类与季节不对应</span></p>";
                        }
                    }
                }
            }

            if (empty($res_str)) {
                $result_upload = $this->uploadThisFile($newFile);
                if ($result_upload) {
                    echo "<script>alert('上传成功');location.href='/admin.php?r=order/product/index'</script>";
                } else {
                    echo "<script>alert('上传失败');location.href='/admin.php?r=order/product/import'</script>";
                }
            } else {
                $this->render('importdetail', array(
                    'warning' => $res_str,
                ));
            }
        } else {
            exit("上传失败");
        }
    }

    /**
     * 导入数据
     *
     * @param $trans
     * @return bool
     */
    public function uploadThisFile($trans)
    {
        //csv

//        $handle = fopen($trans, 'r');
//        $result = ErpCsv::input_csv($handle);

        //xls
        Yii::$enableIncludePath = false; // 不自动加载
        Yii::import('application.extensions.PHPExcel', 1);
        $objPHPExcel = new PHPExcel();
        $objPHPExcel = PHPExcel_IOFactory::load($trans);
        $result = $objPHPExcel->getActiveSheet()->toArray();
        $len_result = count($result);
        $data_values = '';
        $export = new Export();
        $keys = 'purchase_id,product_sn,style_sn,model_sn,serial_num,name,img_url,color_id,size_id,brand_id,cat_b,
        cat_m,cat_s,season_id,level_id,wave_id,scheme_id,cost_price,price_level_id,memo,type_id';
        for ($i = 1, $num = 1; $i < $len_result; $i++, $num++) {
            $purchase_id = $result[$i][1] == Yii::app()->params['purchase_oct'] ? 1 : 2;
            $color_id = $export->color[$result[$i][5]]['color_id'];
            $size_id = $export->size[$result[$i][6]]['size_id'];
            $brand_id = $export->brand[$result[$i][2]]['brand_id'];
            $b_cat_id = $export->cat_b[$result[$i][7]]['big_id'];
            $m_cat_id = $export->cat_m[$result[$i][8]]['middle_id'];
            $s_cat_id = $export->cat_s[$result[$i][9]]['small_id'];
            $season_id = $export->season[$result[$i][10]]['season_id'];
            $wave_id = $export->wave[$result[$i][11]]['wave_id'];
            $level_id = $export->level[$result[$i][12]]['level_id'];
            $scheme_id = $export->scheme[$result[$i][13]]['scheme_id'];
            $price_level_id = $export->parice_level[$result[$i][14]];
            $type_id = $export->type[$result[$i][17]]['type_id'];
            $serial_num = $result[$i][4];
            $cost_price = $result[$i][15];
            $memo = $result[$i][16];
            $name = $result[$i][3];
            $model_sn = $result[$i][0];
            if ($i > 1 && $model_sn != $result[$i - 1][0]) {
                $num = 1;
            }
            $style_sn = $model_sn . sprintf('%04d', $export->color[$result[$i][5]]['color_no']);
            $product_sn = $style_sn . sprintf('%03d', $num);
            $img_url = '/images/' . $model_sn . '_' . $export->color[$result[$i][5]]['color_no'] . '.jpg';
            $data_values[] = "('{$purchase_id}','{$product_sn}','{$style_sn}','{$model_sn}','{$serial_num}','{$name}','{$img_url}',
            '{$color_id}','{$size_id}','{$brand_id}','{$b_cat_id}','{$m_cat_id}','{$s_cat_id}','{$season_id}',
            '{$level_id}','{$wave_id}','{$scheme_id}','{$cost_price}','{$price_level_id}','{$memo}','{$type_id}')";
        }
        $data = implode(',', $data_values);
        $transaction = Yii::app()->db->beginTransaction();
        try {
            $sql = "INSERT INTO {{product}} ($keys) VALUES {$data};";
            $export->ModelExecute($sql);
            //导入日志
            $nowTime = time();
            $manage = new GuestManage();
            $manage->import_log($trans, $nowTime, 'product');
            $this->_clear();
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 导出颜色色号
     */
    public function actionExportColor()
    {
        $color = new Color();
        $result = $color->getColor();
        $export = new Io_xls();
        foreach ($result as $v) {
            $item[] = $v['color_no'];
            $item[] = $v['color_name'];
            $data[] = $item;
            unset($item);
        }
        $filename = '颜色列表' . date('Y_m_d', time());
        $keys = array('颜色代码', '颜色名称');
        $export->export_begin($keys, $filename, count($data));
        $export->export_rows($data);
        $export->export_finish();
    }

    /**
     * 导出商品
     */
    public function actionExport()
    {
        $filename = '商品列表' . date('Y_m_d', time());
        $keys = array('样品代码', '样品名称', '助记符', '颜色明细', '尺码明细', '单位名称', '推荐度', '备注', '年份', '大类', '大类名称',
            '季节', '季节名称', '品牌', '品牌名称', '中类', '中类名称', '服装用品小类', '服装用品小类名称', '面料', '面料名称', '护肤辅料小类',
            '护肤辅料小类名称', '款式划分', '款式划分名称', '波段划分', '波段划分名称', '设计师', '设计师名称', '商品属性8', '商品属性8名称',
            '商品属性9', '商品属性9名称', '商品属性10', '商品属性10名称', '商品属性11', '商品属性11名称', '商品属性12', '商品属性12名称',
            '商品属性13', '商品属性13名称', '商品属性14', '商品属性14名称', '标签商品名称', '标签商品名称名称', '执行标准', '执行标准名称',
            '标准售价', '成本价', '关联订货会', '尺码档', '建档日期', '修档日期', '默认装箱数', '默认配码范围标识', '原货号', '摘要', '停止使用',
            '订货会特价品', '订货会默认交货日期', '必订款', '保留款', '商品代码', '规格分配', '供货商代码', '供货商名称', '主面料', '面料名称',
            '面料单耗', '幅宽', '面料成份', '款号', '款式', '明细异价');
        $productModel = new Product();
        $result = $productModel->getListModel();
        if(empty($result)){
            echo "<script>alert('暂无数据');location.href='/admin.php?r=order/product/index'</script>";
            die;
        }
        $export = new Io_xls();
        foreach ($result as $v) {
            $v['color_str'] = array_unique($v['color_str']);
            $v['size_str'] = array_unique($v['size_str']);
            $item[] = $v['model_sn'];                                   //样品代码
            $item[] = $v['name'];                                       //样品名称
            $item[] = $v['serial_num'];                                 //助记符
            $item[] = implode(',', $v['color_str']);                     //颜色明细
            $item[] = implode(',', $v['size_str']);                      //尺码明细
            $item[] = '';                                               //单位名称
            $item[] = '0';                                                //推荐度
            $item[] = '';                                               //备注
            $item[] = '2016';                                             //年份
            $item[] = $v['cat_b_id'];                                   //大类
            $item[] = $v['cat_b'];                                      //大类名称
            $item[] = $v['season_id'];                                  //季节
            $item[] = $v['season_name'];                                //季节名称
            $item[] = $v['brand_id'];                                   //品牌
            $item[] = $v['brand_name'];                                 //品牌名称
            $item[] = $v['cat_m_id'];                                   //中类
            $item[] = $v['cat_m'];                                      //中类名称
            $item[] = $v['cat_s_id'];                                   //服装用品小类
            $item[] = $v['cat_s'];                                      //服装用品小类名称
            $item[] = '000';                                            //面料
            $item[] = '未定义';                                         //面料名称
            $item[] = '000';                                            //护肤辅料小类
            $item[] = '未定义';                                          //护肤辅料小类名称
            $item[] = '000';                                            //款式划分
            $item[] = '未定义';                                          //款式划分名称
            $item[] = $v['wave_no'];                                    //波段划分
            $item[] = $v['wave_name'];                                  //波段划分名称
            $item[] = '000';                                      //设计师
            $item[] = '未定义';                                      //设计师名称
            $item[] = '000';                                      //商品属性8
            $item[] = '未定义';                                      //商品属性8名称
            $item[] = '000';                                      //商品属性9
            $item[] = '未定义';                                      //商品属性9名称
            $item[] = '000';                                      //商品属性10
            $item[] = '未定义';                                      //商品属性10名称
            $item[] = '000';                                      //商品属性11
            $item[] = '未定义';                                      //商品属性11名称
            $item[] = '000';                                      //商品属性12
            $item[] = '未定义';                                      //商品属性12名称
            $item[] = '000';                                      //商品属性13
            $item[] = '未定义';                                      //商品属性13名称
            $item[] = '000';                                      //商品属性14
            $item[] = '未定义';                                      //商品属性14名称
            $item[] = '000';                                      //标签商品名称
            $item[] = '未定义';                                      //标签商品名称名称
            $item[] = '000';                                      //执行标准
            $item[] = '未定义';                                      //执行标准名称
            $item[] = $v['cost_price'];                                      //标准售价
            $item[] = '0';                                      //成本价
            $item[] = $v['purchase_id'] == 1 ? '036' : '';                                      //关联订货会
            $item[] = '';                                      //尺码档
            $item[] = date('Y/m/d', time());                                      //建档日期
            $item[] = date('Y/m/d', time());;                                      //修档日期
            $item[] = '0';                                      //默认装箱数
            $item[] = '';                                      //默认配码范围标识
            $item[] = '';                                      //原货号
            $item[] = '';                                      //摘要
            $item[] = '0';                                      //停止使用
            $item[] = '0';                                      //订货会特价品
            $item[] = '1900-01-01';                                      //订货会默认交货日期
            $item[] = '0';                                      //必订款
            $item[] = '0';                                      //保留款
            $item[] = $v['model_sn'];                                      //商品代码
            $item[] = '0';                                      //规格分配
            $item[] = '';                                      //供货商代码
            $item[] = '';                                      //供货商名称
            $item[] = '';                                      //主面料
            $item[] = '';                                      //面料名称
            $item[] = '0';                                      //面料单耗
            $item[] = '0';                                      //幅宽
            $item[] = '';                                      //面料成份
            $item[] = '';                                      //款号
            $item[] = '';                                      //款式
            $item[] = '0';                                      //明细异价

            $data[] = $item;
            unset($item);
        }
        $export->export_begin($keys, $filename, count($data));
        $export->export_rows($data);
        $export->export_finish();
    }

    /********************************* AJAX 控制器 ********************************************************/


    /**
     * 根据大类获取中类
     */
    public function actionAjaxCatMiddle()
    {
        $catBig = $this->get("catBig");
        if (empty($catBig)) {
            echo json_encode(array('code' => 400, 'msg' => '出错'));
            die;
        }
        $productModel = new Product();
        //$result = $productModel->selectQueryRows("middle_id,cat_name", "{{cat_middle}}", "parent_id = '{$catBig}'");
        $result['middle'] = $productModel->selectQueryRows("middle_id,cat_name", "{{cat_middle}}");
        $result['small'] = $productModel->selectQueryRows("small_id,small_cat_name AS cat_name", "{{cat_big_small}}", "big_id = '{$catBig}'");
        $result['season'] = $productModel->selectQueryRows("season_id,season_name", "{{season_big}}", "big_id = '{$catBig}'");
        echo json_encode(array('code' => 200, 'data' => $result));
    }

    /**
     * 根据大类获取季节
     */
    public function actionAjaxCatSeason()
    {
        $catBig = $this->get("catBig");
        if (empty($catBig)) {
            echo json_encode(array('code' => 400, 'msg' => '出错'));
            die;
        }
        $productModel = new Product();
        $result = $productModel->selectQueryRows("season_id,season_name", "{{season_big}}", "big_id = '{$catBig}'");
        if (empty($result)) {
            echo json_encode(array('code' => 400));
        }
        echo json_encode(array('code' => 200, 'data' => $result));
    }

    /**
     * 根据中类获取小类
     */
    public function actionAjaxCatSmall()
    {
        $catMiddle = $this->get("catSmall");
        if (empty($catMiddle)) {
            echo json_encode(array('code' => 400, 'msg' => '出错'));
            die;
        }
        $productModel = new Product();
        $result = $productModel->selectQueryRows("small_id,cat_name", "{{cat_small}}", "parent_id = '{$catMiddle}'");
        echo json_encode(array('code' => 200, 'data' => $result));
    }

    /**
     * 根据大类获取小类
     */
    public function actionAjaxCatBigSmall()
    {
        $catBig = $this->get("bigCatSmall");
        if (empty($catMiddle)) {
            echo json_encode(array('code' => 400, 'msg' => '出错'));
            die;
        }
        $productModel = new Product();
        $result = $productModel->selectQueryRows("small_id,small_cat_name AS cat_name", "{{cat_big_small}}", "big_id = '{$catBig}'");
        echo json_encode(array('code' => 200, 'data' => $result));
    }

    /**
     * 根据色系获取色号
     */
    public function actionAjaxSchemeGetColor()
    {
        $scheme = $this->get("scheme");
        if ($scheme == '') {
            echo json_encode(array('code' => 400, 'msg' => '出错'));
            die;
        }
        $scheme = substr($scheme, 0, 1);
        $productModel = new Product();
        $result = $productModel->selectQueryRows("color_id, color_no, color_name", "{{color}}", "color_no LIKE '{$scheme}%'");
        if (empty($result)) {
            echo json_encode(array('code' => 400));
        }
        echo json_encode(array('code' => 200, 'data' => $result));
    }

    /**
     * 根据尺码组获得尺码
     */
    public function actionAjaxSizeGroupGetSize()
    {
        $sizeGroup = (int)$this->get("sizeGroup");
        if (empty($sizeGroup)) {
            echo json_encode(array('code' => 400, 'msg' => '出错'));
            die;
        }
        $productModel = new Product();
        $result = $productModel->selectQueryRows("size_id , size_name", "{{size}}", "group_id='{$sizeGroup}' ORDER BY size_id ASC");
        echo json_encode(array('code' => 200, 'data' => $result));
    }

    /**
     * 检查该款号是否存在
     */
    public function actionAjaxCheckModelSnExist()
    {
        $modelSn = $this->get("modelSn");
        if (empty($modelSn)) {
            die;
        }
        $productModel = new Product();
        $result = $productModel->selectQueryRow('COUNT(*) AS num', '{{product}}', "model_sn = '{$modelSn}'");
        if ($result['num'] > 0) {
            echo json_encode(array('code' => 200, 'data' => $modelSn));
        }
    }

    /**
     * 检查此款号与色号是否存在
     */
    public function actionAjaxCheckModelSnAndColor()
    {
        $modelSn = $this->get("model_sn");
        $color = $this->get("color");
        if (empty($modelSn) || empty($color)) {
            die;
        }
        $productModel = new Product();
        $result = $productModel->selectQueryRow('serial_num', '{{product}}', "model_sn='{$modelSn}' AND color_id='{$color}'");
        if ($result) {
            echo json_encode(array('code' => 200, 'data' => $result['serial_num']));
        }
    }

    /**
     * 判断此色系与色号是否选择对应
     */
    public function actionAjaxCheckSchemeColorExist()
    {
        $color_id = $this->get("color");
        $scheme = $this->get("scheme");
        if ($scheme == "" || $color_id == "") {
            echo json_encode(array('code' => 400, 'msg' => '请先选择色系在选择色号'));
            die;
        }
        $table = new Table();
        $res = $table->colorSchemeTrans();
        $scheme_id = $res[$color_id]['scheme_id'];
        if ($scheme_id != $scheme) {
            echo json_encode(array('code' => 400, 'msg' => '色系与色号不匹配，请重试'));
            die;
        }
    }
}


