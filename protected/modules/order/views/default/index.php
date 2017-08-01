<?php
$this->breadcrumbs=array(
    '订单',
    '订单汇总',
    '商品订单汇总-列表查看'
);
?>
<div style="width: 1000px"    id="filter_form">
    <?php echo $this->renderPartial('filter',array('params'=>$params,'selectOption'=>$selectOption,'result'=>$result));?>
    <?php echo $this->renderPartial('window');?>
</div>

<!--<div style="margin-bottom: 5px;">-->
<!--    --><?php
//    echo TbHtml::link('添加促销',
//        Yii::app()->controller->createUrl("/sales/advertisement/create"),
//        array(
//            'class'=>'btn btn-sm'
//        ));
//    ?>
<!--    <input type="button" class="btn btn-sm btn-primary changestatus" data-val="A" id="btn_activity" value="批量生效">-->
<!--    <input type="button" class="btn btn-sm btn-info changestatus" data-val="D"  id="btn_dead"  value="批量作废">-->
<!--</div>-->





<div style="width: 1000px"   class="row">
    <div class="col-xs-12">


        <div class="table-responsive">
            <div role="grid" class="dataTables_wrapper col-xs-12" id="sample-table-2_wrapper">
                <table class="table table-striped table-bordered table-hover dataTable" id="goods-list" aria-describedby="sample-table-2_info" style="width:977px">
                    <thead>
                    <tr role="row">
<!--                        <th class="center sorting_disabled" role="columnheader" rowspan="1" colspan="1" style="width: 30px;" aria-label="">-->
<!--                            <label>-->
<!--                                <input type="checkbox" class="ace">-->
<!--                                <span class="lbl"></span>-->
<!--                            </label>-->
<!--                        </th>-->
                        <th>大类</th>
                        <th>中类</th>
                        <th>小类</th>
                        <th>款色</th>
                        <th>流水</th>
                        <th>商品类型</th>
                        <th>吊牌价</th>
                        <th>加盟订货</th>
                        <th>直营订货</th>
                        <th>总订货</th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody role="alert" aria-live="polite" aria-relevant="all">
                    <?php foreach ($result['item'] as $v){?>
                        <tr class="odd">
                            <td><?php echo isset($v['cat_big_name'])?$v['cat_big_name']:''; ?> </td>
                            <td><?php echo isset($v['cat_middle_name'])?$v['cat_middle_name']:''; ?> </td>
                            <td><?php echo isset($v['cat_small_name'])?$v['cat_small_name']:''; ?></td>
                            <td><?php echo $v['style_sn']; ?></td>
                            <td><?php echo $v['serial_num']; ?></td>
                            <td><?php echo $v['type_name']; ?></td>
                            <td><?php echo $v['cost_price']; ?></td>
                            <td><?php echo $v['customer']; ?></td>
                            <td><?php echo $v['self'];  ?> </td>
                            <td><?php echo $v['nums']; ?> </td>
                            <td> <input type="button" class="btn btn-sm btn-info Dialogue" data-val="<?php echo $v['style_sn'];?>"  value="明细"></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <div class="row"  style="width:977px">
                    <?php $this->widget(
                        'bootstrap.widgets.TbLinkPager',
                        array(
                            'pages' => $pages,
                            'currentPage'=>$pageIndex,
                            'pageSize'=>$this->pagesize
                        )
                    );?>
                </div>
            </div>
        </div>
    </div>
</div>


