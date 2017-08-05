<?php
$this->breadcrumbs = array(
    '订单',
    '订单详细汇总',
    '订单统计'
);
?>
<style>
    .sidebar:before{
        position: initial;
    }
    .star{
        width: 1920px;
        height: 400px;
    }
    table td {
        width: 84px !important;
    }
    .header-fixed>tbody>tr:nth-child(odd)>td,.header-fixed>tbody>tr>td{
        background-color: #438eb9;
        color:#fff;
    }
</style>
<div class="row star">
    <div class="col-xs-12">
        <div class="table-header">
            <!--S 汇总-->
            <label class="col-sm-3"><?php echo $order_info['purchase_name'] ?></label>
            <label class="col-sm-3">客户名:<?php echo $order_info['customer_name']; ?></label>
            <label class="col-sm-3">客户代码:<?php echo $order_info['customer_id']; ?> </label>
            <input type="button" class="btn btn-sm btn-danger  Dialogue"
                   onclick="window.location.href='admin.php?r=order/order/detail&order_id=<?php echo $order_info['order_id']; ?>'"
                   value="订单明细">
            <input type="button" class="btn btn-sm btn-primary Dialogue"
                   onclick="window.location.href='admin.php?r=order/order/statistics&order_id=<?php echo $order_info['order_id']; ?>'"
                   value="订单统计">
            <input type="button" class="btn btn-sm btn-danger"
                   onclick="window.location.href='admin.php?r=order/order/statistics&order_id=<?php echo $order_info['order_id']; ?>&type=download'"
                   value="导出">
            <!--E 汇总-->
        </div>

        <div class="table-responsive">
            <div role="grid" class="dataTables_wrapper" id="sample-table-2_wrapper" style="padding-right: 17px;">
                <table class="table table-striped table-bordered table-hover dataTable header-fixed" bgcolor="#faebd7">
                    <colgroup>
                        <col style="width: 84px !important;" />
                        <col />
                    </colgroup>
                    <tbody role="alert" aria-live="polite" aria-relevant="all">
                    <tr class="odd text-center">
                        <td rowspan="2">大类</td>
                        <td rowspan="2">小类</td>
                        <td colspan="3">款式数量</td>
                        <td colspan="6">订货数量</td>
                        <td colspan="6">订货金额</td>
                        <td colspan="2">指标</td>
                    </tr>
                    <tr class="odd" style="background-color: #f9f9f9">
                        <!-- 款式数量-->
                        <td><?php echo Yii::app()->params['season_one_name']; ?></td>
                        <td><?php echo Yii::app()->params['season_two_name']; ?></td>
                        <td>合计</td>
                        <!-- 订货数量-->
                        <td><?php echo Yii::app()->params['season_one_name']; ?></td>
                        <td>占比</td>
                        <td><?php echo Yii::app()->params['season_two_name']; ?></td>
                        <td>占比</td>
                        <td>总计</td>
                        <td>占比</td>
                        <!-- 订货金额-->
                        <td><?php echo Yii::app()->params['season_one_name']; ?></td>
                        <td>占比</td>
                        <td><?php echo Yii::app()->params['season_two_name']; ?></td>
                        <td>占比</td>
                        <td>总计</td>
                        <td>占比</td>
                        <!--订货指标-->
                        <td>订货指标</td>
                        <td>达成率</td>
                    </tr>
                    <tr class="odd">
                        <td>订货总计</td>
                        <td></td>
                        <!-- 款式数量-->
                        <td>
                            <?php echo $style_season_1 = count(array_unique($item['model_s_1'])); ?>
                        </td>
                        <td>
                            <?php echo $style_season_2 = count(array_unique($item['model_s_2'])); ?>
                        </td>
                        <td>
                            <?php echo $style_season_1 + $style_season_2; ?>
                        </td>
                        <!-- 订货数量-->
                        <td>
                            <?php echo $num_season_1 = $item['num_s_1']; ?>
                        </td>
                        <td>
                            <?php if ($item['all_num'] == 0) {
                                echo "0%";
                            } else {
                                echo round($num_season_1 / $item['all_num'] * 100, 1) . "%";
                            } ?>
                        </td>
                        <td>
                            <?php echo $num_season_2 = $item['num_s_2']; ?></td>
                        <td>
                            <?php if ($item['all_num'] == 0) {
                                echo "0%";
                            } else {
                                echo round($num_season_2 / $item['all_num'] * 100, 1) . "%";
                            } ?></td>
                        <td>
                            <?php echo $num_season_all = $num_season_1 + $num_season_2; ?>
                        </td>
                        <td>
                            <?php
                            if ($item['all_num'] == 0) {
                                echo "0%";
                            } else {
                                echo round($num_season_all / $item['all_num'] * 100, 1) . "%";
                            } ?>
                        </td>
                        <!-- 订货金额-->
                        <td>
                            <?php echo $amount_season_1 = $item['amount_s_1']; ?>
                        </td>
                        <td>
                            <?php if ($item['all_amount'] == 0) {
                                echo "0%";
                            } else {
                                echo round($amount_season_1 / $item['all_amount'] * 100, 1) . "%";
                            } ?>
                        </td>
                        <td>
                            <?php echo $amount_season_2 = $item['amount_s_2']; ?>
                        </td>
                        <td>
                            <?php if ($item['all_amount'] == 0) {
                                echo "0%";
                            } else {
                                echo round($amount_season_2 / $item['all_amount'] * 100, 1) . "%";
                            } ?>
                        </td>
                        <td>
                            <?php echo $amount_season_all = $amount_season_1 + $amount_season_2; ?>
                        </td>
                        <td>
                            <?php
                            if ($item['all_amount'] == 0) {
                                echo "0%";
                            } else {
                                echo round($amount_season_all / $item['all_amount'] * 100, 1) . "%";
                            } ?>
                        </td>
                        <!--订货指标-->
                        <td>
                            <?php echo $item['target']; ?>
                        </td>
                        <td>
                            <?php echo $item['target_percent']; ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div role="grid" class="dataTables_wrapper" id="sample-table-2_wrapper" style="padding-top: 10px;overflow-y : scroll;height: 400px">
                <table class="table table-striped table-bordered table-hover dataTable">
                    <colgroup>
                        <col style="width: 84px !important;" />
                        <col />
                    </colgroup>
                    <tbody role="alert" aria-live="polite" aria-relevant="all">
                    <?php foreach ($cat_arr as $cat) { ?>
                        <tr class="odd">
                            <td rowspan="<?php echo count($cat['cat_small']); ?>">
                                <?php echo $cat['big_cat_name']; ?>
                            </td>
                            <td>
                                <?php echo $cat['cat_small'][0]['small_cat_name']; ?>
                            </td>
                            <td>
                                <?php echo $small_season_1 = count(array_unique($cat['cat_small'][0]['style_season_1'])); ?>
                            </td>
                            <td>
                                <?php echo $small_season_2 = count(array_unique($cat['cat_small'][0]['style_season_2'])); ?>
                            </td>
                            <td>
                                <?php echo $small_season_1 + $small_season_2; ?>
                            </td>
                            <td>
                                <?php echo $small_season_1_num = $cat['cat_small'][0]['num_season_1']; ?>
                            </td>
                            <td>
                                <?php if ($item['all_num'] == 0) {
                                    echo "0%";
                                } else {
                                    echo round(($small_season_1_num / $item['all_num']) * 100, 1) . "%";
                                } ?>
                            </td>
                            <td>
                                <?php echo $small_season_2_num = $cat['cat_small'][0]['num_season_2']; ?>
                            </td>
                            <td>
                                <?php if ($item['all_num'] == 0) {
                                    echo "0%";
                                } else {
                                    echo round(($small_season_2_num / $item['all_num']) * 100, 1) . "%";
                                } ?>
                            </td>
                            <td>
                                <?php echo $small_season_all_num = $small_season_1_num + $small_season_2_num ?>
                            </td>
                            <td>
                                <?php if ($item['all_num'] == 0) {
                                    echo "0%";
                                } else {
                                    echo round(($small_season_all_num / $item['all_num']) * 100, 1) . "%";
                                } ?>
                            </td>
                            <td>
                                <?php echo $small_season_all_num; ?>
                            </td>
                            <td>
                                <?php if ($item['all_num'] == 0) {
                                    echo "0%";
                                } else {
                                    echo round(($small_season_all_num / $item['all_num']) * 100, 1) . "%";
                                } ?>
                            </td>
                            <td>
                                <?php echo $small_season_1_amount = $cat['cat_small'][0]['amount_season_1']; ?>
                            </td>
                            <td>
                                <?php if ($item['all_amount'] == 0) {
                                    echo "0%";
                                } else {
                                    echo round(($small_season_1_amount / $item['all_amount']) * 100, 1) . "%";
                                } ?>
                            </td>
                            <td>
                                <?php echo $small_season_2_amount = $cat['cat_small'][0]['amount_season_2']; ?>
                            </td>
                            <td>
                                <?php if ($item['all_amount'] == 0) {
                                    echo "0%";
                                } else {
                                    echo round(($small_season_2_amount / $item['all_amount']) * 100, 1) . "%";
                                } ?>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php unset($cat['cat_small'][0]);
                        foreach ($cat['cat_small'] as $small) {
                            ?>
                            <tr class="odd <?php echo ($small['small_cat_name'] == '设限款') ? 'success' : '' ?>">
                                <td>
                                    <?php echo $small['small_cat_name']; ?>
                                </td>
                                <td>
                                    <?php echo $small_season_cat_1 = count(array_unique($small['style_season_1'])); ?>
                                </td>
                                <td>
                                    <?php echo $small_season_cat_2 = count(array_unique($small['style_season_2'])); ?>
                                </td>
                                <td>
                                    <?php echo $small_season_cat_1 + $small_season_cat_2; ?>
                                </td>
                                <td>
                                    <?php echo $small_num_season_one = $small['num_season_1']; ?>
                                </td>
                                <td>
                                    <?php if ($item['all_num'] == 0) {
                                        echo "0%";
                                    } else {
                                        echo round(($small_num_season_one / $item['all_num']) * 100, 1) . "%";
                                    } ?>
                                </td>
                                <td>
                                    <?php echo $small_num_season_two = $small['num_season_2']; ?>
                                </td>
                                <td>
                                    <?php if ($item['all_num'] == 0) {
                                        echo "0%";
                                    } else {
                                        echo round(($small_num_season_two / $item['all_num']) * 100, 1) . "%";
                                    } ?>
                                </td>
                                <td>
                                    <?php echo $small_num_season_all = $small_num_season_one + $small_num_season_two ?></td>
                                <td>
                                    <?php if ($item['all_num'] == 0) {
                                        echo "0%";
                                    } else {
                                        echo round(($small_num_season_all / $item['all_num']) * 100, 1) . "%";
                                    } ?>
                                </td>

                                <td>
                                    <?php echo $small_amount_season_1 = $small['amount_season_1']; ?>
                                </td>
                                <td>
                                    <?php if ($item['all_amount'] == 0) {
                                        echo "0%";
                                    } else {
                                        echo round(($small_amount_season_1 / $item['all_amount']) * 100, 1) . "%";
                                    } ?>
                                </td>
                                <td>
                                    <?php echo $small_amount_season_2 = $small['amount_season_2']; ?>
                                </td>
                                <td>
                                    <?php if ($item['all_amount'] == 0) {
                                        echo "0%";
                                    } else {
                                        echo round(($small_amount_season_2 / $item['all_amount']) * 100, 1) . "%";
                                    } ?>
                                </td>
                                <td>
                                    <?php echo $small_amount_season_all = $small_amount_season_1 + $small_amount_season_2; ?>
                                </td>
                                <td>
                                    <?php if ($item['all_amount'] == 0) {
                                        echo "0%";
                                    } else {
                                        echo round(($small_amount_season_all / $item['all_amount']) * 100, 1) . "%";
                                    } ?>
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php } ?>
                        <tr class="odd">
                            <td class="success">
                                <?php echo $cat['big_cat_name']; ?>总计：</td>
                            <td class="success"></td>
                            <td class="success">
                                <?php echo $season_1 = count(array_unique($cat['res_style_season_1'])); ?>
                            </td>
                            <td class="success">
                                <?php echo $season_2 = count(array_unique($cat['res_style_season_2'])); ?>
                            </td>
                            <td class="success">
                                <?php echo $season_1 + $season_2; ?>
                            </td>
                            <td class="success">
                                <?php echo $season_1_num = $cat['res_num_season_1']; ?>
                            </td>
                            <td class="success">
                                <?php if ($item['all_num'] == 0) {
                                    echo "0%";
                                } else {
                                    echo round($season_1_num / $item['all_num'] * 100, 1) . "%";
                                } ?>
                            </td>
                            <td class="success">
                                <?php echo $season_2_num = $cat['res_num_season_2']; ?>
                            </td>
                            <td class="success">
                                <?php if ($item['all_num'] == 0) {
                                    echo "0%";
                                } else {
                                    echo round($season_2_num / $item['all_num'] * 100, 1) . "%";
                                } ?>
                            </td>
                            <td class="success">
                                <?php echo $season_all_num = $season_1_num + $season_2_num ?>
                            </td>
                            <td class="success">
                                <?php if ($item['all_num'] == 0) {
                                    echo "0%";
                                } else {
                                    echo round($season_all_num / $item['all_num'] * 100, 1) . "%";
                                } ?>
                            </td>
                            <td class="success">
                                <?php echo $amount_1 = $cat['res_amount_season_1'] ?>
                            </td>
                            <td class="success">
                                <?php if ($item['all_amount'] == 0) {
                                    echo "0%";
                                } else {
                                    echo round($amount_1 / $item['all_amount'] * 100, 1) . "%";
                                } ?>
                            </td>
                            <td class="success">
                                <?php echo $amount_2 = $cat['res_amount_season_2'] ?>
                            </td>
                            <td class="success">
                                <?php if ($item['all_amount'] == 0) {
                                    echo "0%";
                                } else {
                                    echo round($amount_2 / $item['all_amount'] * 100, 1) . "%";
                                } ?>
                            </td>
                            <td class="success">
                                <?php echo $amount_all = $amount_1 + $amount_2 ?>
                            </td>
                            <td class="success">
                                <?php if ($item['all_amount'] == 0) {
                                    echo "0%";
                                } else {
                                    echo round($amount_all / $item['all_amount'] * 100, 1) . "%";
                                } ?>
                            </td>
                            <td class="success">
                                <?php echo $cat['target']; ?>
                            </td>
                            <td class="success"><?php echo $cat['target_percent']; ?>
                            </td>
                        </tr>
                        <tr class="odd">
                            <td colspan="19"></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <!--S  FOOT--->
            <div class="table-responsive">
                <div role="grid" class="dataTables_wrapper" id="sample-table-2_wrapper">
                    <table class="table table-striped table-bordered table-hover dataTable">
                        <tbody role="alert" aria-live="polite" aria-relevant="all">
                        <tr class="odd">
                            <td colspan="4">
                                <span class="btn-group">
                                <a class="btn btn-sm btn-info" href="javascript:history.go(-1)">返回</a>
                                </span>
                            </td>

                            <td colspan="2">
                                订货指标:<?php echo ($order_info['target'] == 0) ? '' : number_format($order_info['target'], 1); ?>
                            </td>
                            <td colspan="2">
                                已订货:<?php echo number_format($order_info['cost_item'], 1); ?>
                            </td>
                            <td colspan="2">
                                达成率:<?php echo ($order_info['target'] == 0) ? '' : number_format($order_info['cost_item'] / $order_info['target'] * 100, 2) . "%"; ?>
                            </td>
                            <td></td>
                            <td>
                                <span class="btn-group col-sm-3"
                                      style=" display: <?php if ($order_info['status'] == 'finish') {
                                          echo 'block';
                                      } else {
                                          echo 'none';
                                      } ?>">
                                <input type="button" class="btn btn-sm btn-danger checkOrder" data-status="confirm"
                                       data-val="<?php echo $order_info['order_id']; ?>" value="取消审核">
                                </span>
                                <span class="btn-group col-sm-3"
                                      style=" display: <?php if ($order_info['status'] == 'confirm') {
                                          echo 'block';
                                      } else {
                                          echo 'none';
                                      } ?>">
                                <input type="button" class="btn btn-sm btn-warning checkOrder" data-status="finish"
                                       data-val="<?php echo $order_info['order_id']; ?>" value="订单审核">
                                </span>
                            </td>
                            </td>
                            <td></td>

                            <td></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>


</div>
<script>
    $(function ($) {
        $(".checkOrder").click(function () {
            if (confirm('确认订单审核通过吗？')) {
                var btn = $(this);

                var order_id = $(this).attr('data-val');
                var status = $(this).attr('data-status');
                $.post('admin.php?r=order/order/check', {'order_id': order_id, 'status': status}, function (data) {
                    if (data.code !== 400) {
                        alert('操作成功');
                        btn.parent().hide();
                        btn.parent().siblings().show();
                    } else {
                        alert('操作失败');
                    }

                }, 'json');
            }

        });
    });

</script>


