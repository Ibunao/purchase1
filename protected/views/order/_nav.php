<div class="order_nav w100" id="hdNav">
    <ul>
        <li class="order_nav_title fl">我的订单</li>
        <li class="order_total"><a href="/order/bycount" class="<?php echo $sel == 'bycount' ? 'selected': ''; ?>"><?php echo Yii::app()->params['season_title']; ?>汇总</a></li>
        <li class="order_total"><a href="/order/byprice" class="<?php echo $sel == 'byprice' ? 'selected': ''; ?>">价格汇总</a></li>
        <li class="order_nav_bt_area fr"><a href="/order/bydetail" class="order_nav_bt <?php echo $sel == 'bydetail' ? 'selected': ''; ?>">订单明细</a></li>
        <li class="fr"><a href="/order/bycount" class="order_nav_bt <?php echo $sel == 'bycount' ? 'selected': ''; ?>">订单统计</a></li>
        <li class="fr"><a href="/order/bydownuser" class="order_nav_bt <?php echo $sel == 'bydownuser' ? 'selected': ''; ?>">我的分销</a></li>
    </ul>
</div>