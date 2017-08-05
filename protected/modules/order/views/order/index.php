<?php
$this->breadcrumbs = array(
    '订单',
    '订单详细汇总'
);
?>
<div id="filter_form">
    <?php echo $this->renderPartial('filter', array('params' => $params, 'selectOption' => $selectOption, 'statistics' => $statistics)); ?>
</div>


<div class="row">
    <div>
        <!--        <div class="table-header">-->
        <!--<!--            S 汇总-->
        <!--                <label class="col-sm-3">客户总订货指标:-->
        <?php //echo number_format( $statistics['target_sum'],2);?><!--  </label>-->
        <!--                <label class="col-sm-3">已订货金额：-->
        <?php //echo number_format( $statistics['amount'],2);?><!--  </label>-->
        <!--                <label                 >达成率：-->
        <?php //echo empty( $statistics['amount'])?0:number_format($statistics['amount']/$statistics['target_sum']*100,2);?><!--%  </label>-->
        <!--<!--            E 汇总-->
        <!--        </div>-->
        <div class="table-responsive">
            <div role="grid" class="dataTables_wrapper col-xs-12" id="sample-table-2_wrapper">
                <table class="table table-striped table-bordered table-hover dataTable" id="goods-list"
                       aria-describedby="sample-table-2_info">
                    <thead>
                    <tr role="row">
                        <!--                        <th class="center sorting_disabled" role="columnheader" rowspan="1" colspan="1" style="width: 30px;" aria-label="">-->
                        <!--                            <label>-->
                        <!--                                <input type="checkbox" class="ace">-->
                        <!--                                <span class="lbl"></span>-->
                        <!--                            </label>-->
                        <!--                        </th>-->
                        <th tabindex="0" style="width: 300px;">客户/店铺名称</th>
                        <th tabindex="0" style="width: 140px;">客户/店铺代码</th>
                        <th tabindex="0" style="width: 60px;">订货会</th>
                        <th tabindex="0" style="width: 109px;">订货指标</th>
                        <th tabindex="0" style="width: 109px;">已订货金额</th>
                        <th tabindex="0" style="width: 100px;">达成率</th>
                        <th tabindex="0" style="width: 180px;">未完成金额</th>
                        <th tabindex="0" style="width: 180px;">下线已定货金额</th>
                        <th tabindex="0" style="width: 300px;">审核人/审核时间</th>
                        <th tabindex="0" style="width: 300px;"></th>
                        <th tabindex="0" style="width: 400px;"></th>
                    </tr>
                    </thead>

                    <tbody role="alert" aria-live="polite" aria-relevant="all">
                    <?php foreach ($result['item'] as $v) { ?>
                        <tr class="odd">

                            <td><?php echo $v['customer_name']; ?> </td>
                            <td><?php echo $v['code']; ?></td>
                            <td><?php echo ($v['purchase_id'] == 1) ? 'oct' : 'uki'; ?></td>
                            <td><?php echo ($v['target'] == 0) ? '' : number_format($v['target'], 2); ?></td>
                            <td><?php echo number_format($v['cost_item'], 2); ?></td>
                            <td><?php echo ($v['target'] == 0) ? '' : number_format($v['cost_item'] / $v['target'] * 100, 2) . '%'; ?> </td>
                            <td><?php echo ($v['target'] - $v['cost_item'] <= 0) ? 0.00 : number_format($v['target'] - $v['cost_item'], 2); ?> </td>
                            <td><?php echo number_format($v['xxydhje'], 2); ?></td>
                            <td><?php echo $v['check_user'] . '/' . $v['check_time']; ?> </td>

                            <td>
                                <span class="btn-group col-sm-5">
                                <input type="button" class="btn btn-sm btn-info "
                                       data-val="<?php echo $v['order_id']; ?>"
                                       onclick="window.location.href='admin.php?r=order/order/detail&order_id=<?php echo $v['order_id']; ?>'"
                                       value="明细">
                                </span>
                                <span class="btn-group col-sm-4" style=" display: <?php if ($v['status'] == 'confirm') {
                                    echo 'block';
                                } else {
                                    echo 'none';
                                } ?>">
                                <input type="button" class="btn btn-sm btn-warning checkOrder" data-status="finish"
                                       data-val="<?php echo $v['order_id']; ?>" value="订单审核">
                                </span>
                                <span class="btn-group col-sm-4" style=" display: <?php if ($v['status'] == 'finish') {
                                    echo 'block';
                                } else {
                                    echo 'none';
                                } ?>">
                                <input type="button" class="btn btn-sm btn-danger checkOrder" data-status="confirm"
                                       data-val="<?php echo $v['order_id']; ?>" value="取消审核">
                                </span>
                            </td>
                            <td>
                                <span class="btn-group">
                                <button type="button" class="btn btn-sm btn-info Dialogue "
                                        data-val="<?php echo $v['code']; ?>" value="">复制
                                </button>
                                <button type="button" class="btn btn-sm btn-info "
                                        onclick="window.location.href='admin.php?r=order/order/downloadorderitems2&order_id=<?php echo $v['order_id']; ?>'"
                                        value="">下载
                                </button>
                                <?php if($v['is_diff']){ ?>
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="window.location.href='admin.php?r=order/order/differ&order_id=<?php echo $v['order_id']; ?>'"
                                            value="">价格变动
                                    </button>
                                <?php } ?>
                                <?php if($v['parent_id'] == '1'){ ?>
                                    <button type="button" class="btn btn-sm btn-success" onclick="window.location.href='admin.php?r=order/order/index&param[leader_name]=<?php echo $v['code']; ?>'">
                                        查看下线
                                    </button>
                                <?php } ?>
                                </span>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <div class="row">
                    <?php $this->widget(
                        'bootstrap.widgets.TbLinkPager',
                        array(
                            'pages' => $pages,
                            'currentPage' => $pageIndex,
                            'pageSize' => $this->pagesize
                        )
                    ); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="Dialogue" style="display: none">
    <div style="margin-top: 140px;margin-left: 50px;">
        <?php echo $this->renderPartial('copy'); ?>
    </div>
</div>

<script>
    $(function ($) {

        var html = $("#Dialogue").html();
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


        $(".Dialogue").click(function () {
            $("#Dialogue").html('')
            var from = $(this).attr('data-val');
            $.layer({
                type: 1,   //0-4的选择,
                title: false,
                border: [0],
                closeBtn: [0],
                shadeClose: true,
                area: ['800px', '300px'],
                page: {
                    html: html
                }
            });
            $("#from").val(from);
        });

    });

</script>
