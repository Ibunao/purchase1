<!--主导航-->
<div id="nav_shade" class="none"></div>
<?php echo $this->renderPartial('/layouts/_nav', array('down' => 'bydownprice', 'purchase_id' => $purchase_id, 'customer_id' => $customer_id )); ?>
<!--主导航-->
<!--右侧主区域-->
<div class="order_dt_table_all">
<!--表头-->
<ul class="normal_line">
    <!--第一行 first_l 第一列 first_v-->
    <li class="div10 fl first_v first_l">大类</li>
    <li class="div12 fl first_l">价格带</li>
    <li class="div12 fl first_l">款式数量</li>
    <li class="div14 fl first_l">款式占比</li>
    <li class="div14 fl first_l">订货数量</li>
    <li class="div14 fl first_l">数量占比</li>
    <li class="div14 fl first_l">订货金额</li>
    <li class="div10 fl first_l">金额占比</li>
</ul>
<!--表头-->
<?php foreach ($list as $v):?>
<!--大类1-->
<ul class="normal_line">
    <li class="div10 fl first_v"><?php echo $v['b_name'];?></li>
    <li class="div12 fl">
        <!--小类-->
        <ul class="semi_level">
            <!--二级的首行first_l-->
            <?php foreach ($v['dpj'] as $kk=>$vv):?>
            <li><?php echo $vv['name'];?></li>
            <?php endforeach;?>
        </ul>
        <!--小类-->
    </li>
    <li class="div12 fl">
        <!--款数-->
        <ul class="semi_level">
            <?php foreach ($v['dpj'] as $kk=>$vv):?>
                <li><?php echo count(array_unique($vv['model']));?></li>
            <?php endforeach;?>
        </ul>
        <!--款数-->
    </li>

    <li class="div14 fl">
        <!--占比-->
        <ul class="semi_level">
            <?php foreach ($v['dpj'] as $kk=>$vv):?>
                <li><?php if($result['all']==0){echo '0%';}else{  echo round(count(array_unique($vv['model']))/$result['all']*100,1)."%"; }?></li>
            <?php endforeach;?>
        </ul>
        <!--占比-->
    </li>

    <li class="div14 fl">
        <!--订单数量-->
        <ul class="semi_level">
            <?php foreach ($v['dpj'] as $kk=>$vv):?>
                <li><?php echo $vv['nums'];?></li>
            <?php endforeach;?>
        </ul>
        <!--订单数量-->
    </li>
    <li class="div14 fl">
        <!--数量占比-->
        <ul class="semi_level">
            <?php foreach ($v['dpj'] as $kk=>$vv):?>
                <li><?php if($result['total_nums']==0){echo '0%';}else{  echo round(($vv['nums']/$result['total_nums'])*100,1).'%';}?></li>
            <?php endforeach;?>
        </ul>
        <!--数量占比-->
    </li>
    <li class="div14 fl">
        <!--订货金额-->
        <ul class="semi_level">
            <?php foreach ($v['dpj'] as $kk=>$vv):?>
                <li><?php echo $vv['amount'];?></li>
            <?php endforeach;?>
        </ul>
        <!--订货金额-->
    </li>
    <li class="div10 fl last_v">
        <!--金额占比-->
        <ul class="semi_level">
            <?php foreach ($v['dpj'] as $kk=>$vv):?>
                <li><?php if($result['amount']==0){echo '0%';}else{  echo round(($vv['amount']/$result['amount'])*100,1).'%';}?></li>
            <?php endforeach;?>
        </ul>
        <!--金额占比-->
    </li>
</ul>
<ul class="statistics_line">
    <li class="div22 fl first_v"><?php echo $v['b_name'];?></li>
    <li class="div12 fl word_red"><?php echo count(array_unique($v['model']))?></li>
    <li class="div14 fl word_red"><?php if($result['all']==0){echo '0%';}else{  echo round(count(array_unique($v['model']))/$result['all'],2)*100.."%";} ?></li><!--占比 -->
    <li class="div14 fl word_red"><?php echo $v['nums'];?></li>
    <li class="div14 fl word_red"><?php if($result['total_nums']==0){echo '0%';}else{ echo round(($v['nums']/$result['total_nums'])*100,1).'%';}?></li>
    <li class="div14 fl word_red"><?php echo $v['amount'];?></li>
    <li class="div10 fl word_red last_v"><?php  if($result['amount']==0){echo '0%';}else{ echo round(($v['amount']/$result['amount'])*100,1).'%';}?></li>
</ul>
<!--大类1-->
<?php endforeach;?>

<!--总计-->
<ul class="statistics_line">
    <li class="div22 fl first_v">订货总计</li>
    <li class="div12 fl word_red last_l"><?php echo $result['all']?></li>
    <li class="div14 fl word_red last_l"><?php echo count(array_unique($result['model']))?'100%':'0%'; ?></li>
    <li class="div14 fl word_red last_l"><?php echo $result['total_nums']?></li>
    <li class="div14 fl word_red last_l"><?php echo $result['total_nums']?'100%':'0%';?></li>
    <li class="div14 fl word_red last_l"><?php echo $result['amount'];?></li>
    <li class="div10 fl word_red last_v last_l"><?php echo $result['total_nums']?'100%':'0%';?></li>
</ul>
<!--总计-->
</div>
<?php echo $this->renderPartial('/common/_footer_order',array());?>