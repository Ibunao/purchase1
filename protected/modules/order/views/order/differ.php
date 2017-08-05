<?php
/**
 *
 * @author       zangmiao <838881690@qq.com>
 * @copyright    Copyright (c) 2011-2016 octmami.All Rights Reserved.
 * @link         http://mall.octmami.com
 */
$this->breadcrumbs = array(
    '订单',
    '订单商品价格修改详情'
);
?>
<table class="table table-striped">
    <thead>
        <tr>
            <td>商品ID</td>
            <td>商品名</td>
            <td>款号</td>
            <td>订购价格</td>
            <td>现在价格</td>
        </tr>
    </thead>
    <?php foreach($result as $val){ ?>
    <tr>
        <td><?php echo $val['product_id']; ?></td>
        <td><?php echo $val['name']; ?></td>
        <td><?php echo $val['model_sn']; ?></td>
        <td class="text-info"><?php echo $price['old'][$val['model_sn']]; ?></td>
        <td class="text-danger"><?php echo $price['new'][$val['model_sn']]; ?></td>
    </tr>
    <?php } ?>
</table>
