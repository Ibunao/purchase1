<div class="order_nav w100" id="hdNav">
    <ul>
        <?php if(isset($sel) && $sel): ?>
            <li class="order_nav_title fl">我的订单</li>
            <li class="order_total"><a href="/order/bycount" class="<?php echo $sel == 'bycount' ? 'act': ''; ?>"><?php echo Yii::app()->params['season_title']; ?>汇总</a></li>
            <li class="order_total"><a href="/order/byprice" class="<?php echo $sel == 'byprice' ? 'act': ''; ?>">价格汇总</a></li>
            <li class="order_nav_bt_area fr"><a href="/order/bydetail" class="order_nav_bt <?php echo $sel == 'bydetail' ? 'selected': ''; ?>">订单明细</a></li>
            <li class="fr"><a href="/order/bycount" class="order_nav_bt <?php echo $sel == 'bycount' ? 'selected': ''; ?>">订单统计</a></li>
            <li class="fr"><a href="/order/bydownuser" class="order_nav_bt <?php echo $sel == 'bydownuser' ? 'selected': ''; ?>">我的分销</a></li>
        <?php else: ?>
            <li class="order_nav_title fl">分销订单</li>
            <li class="order_total"><a href="/downuser/bydowncount?purchase_id=<?php echo $purchase_id; ?>&customer_id=<?php echo $customer_id; ?>" class="<?php echo $down == 'bydowncount' ? 'act': ''; ?>"><?php echo Yii::app()->params['season_title']; ?>汇总</a></li>
            <li class="order_total"><a href="/downuser/bydownprice?purchase_id=<?php echo $purchase_id; ?>&customer_id=<?php echo $customer_id; ?>" class="<?php echo $down == 'bydownprice' ? 'act': ''; ?>">价格汇总</a></li>
            <li class="order_nav_bt_area fr"><a href="/order/bydownuser" class="order_nav_bt">分销列表</a></li>
            <li class=" fr">分销名称：<?php echo $this->down_code; ?></li>
        <?php endif; ?>
    </ul>
</div>