<?php //var_dump($list);die; ?>
<!--主导航-->
<div id="nav_shade" class="none"></div>
<div class="order_nav w100" id="hdNav">
    <ul>
        <li class="order_nav_title fl">我的订单</li>
        <li class="order_total"><a href="/order/show" class="selected"><?php echo Yii::app()->params['season_title']; ?>汇总</a></li>
        <li class="order_total"><a href="/order/index6">价格汇总</a></li>
        <li class="order_nav_bt_area fr"><a href="/order/index2" class="order_nav_bt ">订单明细</a></li>
    <li class=" fr"><a href="/order/show" class="order_nav_bt selected">订单统计</a></li>
    </ul>
</div>
<!--主导航-->
<!--右侧主区域-->
<div class="order_dt_table_all">
<!--表头-->
<ul class="normal_line">
    <!--第一行 first_l 第一列 first_v-->
    <li class="div10 fl first_v first_l">大类</li>
    <li class="div12 fl first_l">小类</li>
    <li class="div14 fl first_l">款式数量</li>
    <li class="div14 fl first_l">占比</li>
    <li class="div14 fl first_l">订货数量</li>
    <li class="div12 fl first_l">数量占比</li>
    <li class="div14 fl first_l">订货金额</li>
    <li class="div10 fl first_l">金额占比</li>
</ul>
<!--表头-->
<?php if(!empty($list[1])){ ?>
<!--大类1-->
<ul class="normal_line">
    <li class="div10 fl first_v"><?php echo $list[1]['b_name'];?></li>
    <li class="div12 fl">
        <!--小类-->
        <ul class="semi_level">
            <!--二级的首行first_l-->
            <?php foreach ($list[1]['small'] as $kk=>$vv):?>
            <li><?php echo $vv['name'];?></li>
            <?php endforeach;?>
        </ul>
        <!--小类-->
    </li>
    <li class="div14 fl">
        <!--款数-->
        <ul class="semi_level">
            <?php foreach ($list[1]['small'] as $kk=>$vv):?>
                <li><?php echo count(array_unique($vv['model']));?></li>
            <?php endforeach;?>
        </ul>
        <!--款数-->
    </li>
    <li class="div14 fl">
        <!--冬季-->
        <ul class="semi_level">
            <?php foreach ($list[1]['small'] as $kk=>$vv):?>
                <li><?php if($result['all']==0){echo "0%";}{ echo round((count(array_unique($vv['model']))/$result['all'])*100,2)."%";}?></li>
            <?php endforeach;?>
        </ul>
        <!--冬季-->
    </li>
    <li class="div14 fl">
        <!--订单数量-->
        <ul class="semi_level">
            <?php foreach ($list[1]['small'] as $kk=>$vv):?>
                <li><?php echo $vv['nums'];?></li>
            <?php endforeach;?>
        </ul>
        <!--订单数量-->
    </li>
    <li class="div12 fl">
        <!--数量占比-->
        <ul class="semi_level">
            <?php foreach ($list[1]['small'] as $kk=>$vv):?>
                <li><?php if($result['total_nums']==0){ echo "0%";}else{  echo round(($vv['nums']/$result['total_nums'])*100,2).'%';}?></li>
            <?php endforeach;?>
        </ul>
        <!--数量占比-->
    </li>
    <li class="div14 fl">
        <!--订货金额-->
        <ul class="semi_level">
            <?php foreach ($list[1]['small'] as $kk=>$vv):?>
                <li><?php echo sprintf('%.2f',$vv['amount']);?></li>
            <?php endforeach;?>
        </ul>
        <!--订货金额-->
    </li>
    <li class="div10 fl last_v">
        <!--金额占比-->
        <ul class="semi_level">
            <?php foreach ($list[1]['small'] as $kk=>$vv):?>
                <li><?php if($result['total_nums']==0){ echo "0%";}else{  echo round(($vv['amount']/$result['amount'])*100,2).'%'; }?></li>
            <?php endforeach;?>
        </ul>
        <!--金额占比-->
    </li>
</ul>
<ul class="statistics_line">
    <li class="div22 fl first_v"><?php echo $list[1]['b_name'];?></li>
    <li class="div14 fl word_red"><?php echo count(array_unique($list[1]['model']))?></li>
    <li class="div14 fl word_red"><?php echo round(count(array_unique($list[1]['model']))/$result['all'],2)*100.."%"; ?></li>
    <li class="div14 fl word_red"><?php echo $list[1]['nums'];?></li>
    <li class="div12 fl word_red"><?php if($result['total_nums']==0){ echo "0%";}else{  echo round(($list[1]['nums']/$result['total_nums'])*100,2).'%';}?></li>
    <li class="div14 fl word_red"><?php echo sprintf('%.2f',$list[1]['amount']);?></li>
    <li class="div10 fl word_red last_v"><?php if($result['amount']==0){ echo "0%";}else{  echo round(($list[1]['amount']/$result['amount'])*100,2).'%';}?></li>
</ul>
<!--大类1-->
<?php } ?>
    <?php if(!empty($list[2])){ ?>
        <!--大类1-->
        <ul class="normal_line">
            <li class="div10 fl first_v"><?php echo $list[2]['b_name'];?></li>
            <li class="div12 fl">
                <!--小类-->
                <ul class="semi_level">
                    <!--二级的首行first_l-->
                    <?php foreach ($list[2]['small'] as $kk=>$vv):?>
                        <li><?php echo $vv['name'];?></li>
                    <?php endforeach;?>
                </ul>
                <!--小类-->
            </li>
            <li class="div14 fl">
                <!--款数-->
                <ul class="semi_level">
                    <?php foreach ($list[2]['small'] as $kk=>$vv):?>
                        <li><?php echo count(array_unique($vv['model']));?></li>
                    <?php endforeach;?>
                </ul>
                <!--款数-->
            </li>
            <li class="div14 fl">
                <!--冬季-->
                <ul class="semi_level">
                    <?php foreach ($list[2]['small'] as $kk=>$vv):?>
                        <li><?php if($result['all']==0){echo "0%";}{ echo round((count(array_unique($vv['model']))/$result['all'])*100,2)."%";}?></li>
                    <?php endforeach;?>
                </ul>
                <!--冬季-->
            </li>
            <li class="div14 fl">
                <!--订单数量-->
                <ul class="semi_level">
                    <?php foreach ($list[2]['small'] as $kk=>$vv):?>
                        <li><?php echo $vv['nums'];?></li>
                    <?php endforeach;?>
                </ul>
                <!--订单数量-->
            </li>
            <li class="div12 fl">
                <!--数量占比-->
                <ul class="semi_level">
                    <?php foreach ($list[2]['small'] as $kk=>$vv):?>
                        <li><?php if($result['total_nums']==0){ echo "0%";}else{  echo round(($vv['nums']/$result['total_nums'])*100,2).'%';}?></li>
                    <?php endforeach;?>
                </ul>
                <!--数量占比-->
            </li>
            <li class="div14 fl">
                <!--订货金额-->
                <ul class="semi_level">
                    <?php foreach ($list[2]['small'] as $kk=>$vv):?>
                        <li><?php echo sprintf('%.2f',$vv['amount']);?></li>
                    <?php endforeach;?>
                </ul>
                <!--订货金额-->
            </li>
            <li class="div10 fl last_v">
                <!--金额占比-->
                <ul class="semi_level">
                    <?php foreach ($list[2]['small'] as $kk=>$vv):?>
                        <li><?php if($result['total_nums']==0){ echo "0%";}else{  echo round(($vv['amount']/$result['amount'])*100,2).'%'; }?></li>
                    <?php endforeach;?>
                </ul>
                <!--金额占比-->
            </li>
        </ul>
        <ul class="statistics_line">
            <li class="div22 fl first_v"><?php echo $list[2]['b_name'];?></li>
            <li class="div14 fl word_red"><?php echo count(array_unique($list[2]['model']))?></li>
            <li class="div14 fl word_red"><?php echo round(count(array_unique($list[2]['model']))/$result['all'],2)*100.."%"; ?></li>
            <li class="div14 fl word_red"><?php echo $list[2]['nums'];?></li>
            <li class="div12 fl word_red"><?php if($result['total_nums']==0){ echo "0%";}else{  echo round(($list[2]['nums']/$result['total_nums'])*100,2).'%';}?></li>
            <li class="div14 fl word_red"><?php echo sprintf('%.2f',$list[2]['amount']);?></li>
            <li class="div10 fl word_red last_v"><?php if($result['amount']==0){ echo "0%";}else{  echo round(($list[2]['amount']/$result['amount'])*100,2).'%';}?></li>
        </ul>
        <!--大类1-->
<?php }?>
    <?php if(!empty($list[3])){ ?>
            <ul class="normal_line">
                <li class="div10 fl first_v"><?php echo $list[3]['b_name'];?></li>
                <li class="div12 fl">
                    <!--小类-->
                    <ul class="semi_level">
                        <!--二级的首行first_l-->
                        <?php foreach ($list[3]['small'] as $kk=>$vv):?>
                            <li><?php echo $vv['name'];?></li>
                        <?php endforeach;?>
                    </ul>
                    <!--小类-->
                </li>
                <li class="div14 fl">
                    <!--款数-->
                    <ul class="semi_level">
                        <?php foreach ($list[3]['small'] as $kk=>$vv):?>
                            <li><?php echo count(array_unique($vv['model']));?></li>
                        <?php endforeach;?>
                    </ul>
                    <!--款数-->
                </li>
                <li class="div14 fl">
                    <!--冬季-->
                    <ul class="semi_level">
                        <?php foreach ($list[3]['small'] as $kk=>$vv):?>
                            <li><?php if($result['all']==0){echo "0%";}{ echo round((count(array_unique($vv['model']))/$result['all'])*100,2)."%";}?></li>
                        <?php endforeach;?>
                    </ul>
                    <!--冬季-->
                </li>
                <li class="div14 fl">
                    <!--订单数量-->
                    <ul class="semi_level">
                        <?php foreach ($list[3]['small'] as $kk=>$vv):?>
                            <li><?php echo $vv['nums'];?></li>
                        <?php endforeach;?>
                    </ul>
                    <!--订单数量-->
                </li>
                <li class="div12 fl">
                    <!--数量占比-->
                    <ul class="semi_level">
                        <?php foreach ($list[3]['small'] as $kk=>$vv):?>
                            <li><?php if($result['total_nums']==0){ echo "0%";}else{  echo round(($vv['nums']/$result['total_nums'])*100,2).'%';}?></li>
                        <?php endforeach;?>
                    </ul>
                    <!--数量占比-->
                </li>
                <li class="div14 fl">
                    <!--订货金额-->
                    <ul class="semi_level">
                        <?php foreach ($list[3]['small'] as $kk=>$vv):?>
                            <li><?php echo sprintf('%.2f',$vv['amount']);?></li>
                        <?php endforeach;?>
                    </ul>
                    <!--订货金额-->
                </li>
                <li class="div10 fl last_v">
                    <!--金额占比-->
                    <ul class="semi_level">
                        <?php foreach ($list[3]['small'] as $kk=>$vv):?>
                            <li><?php if($result['total_nums']==0){ echo "0%";}else{  echo round(($vv['amount']/$result['amount'])*100,2).'%'; }?></li>
                        <?php endforeach;?>
                    </ul>
                    <!--金额占比-->
                </li>
            </ul>
            <ul class="statistics_line">
                <li class="div22 fl first_v"><?php echo $list[3]['b_name'];?></li>
                <li class="div14 fl word_red"><?php echo count(array_unique($list[3]['model']))?></li>
                <li class="div14 fl word_red"><?php echo round(count(array_unique($list[3]['model']))/$result['all'],2)*100.."%"; ?></li>
                <li class="div14 fl word_red"><?php echo $list[3]['nums'];?></li>
                <li class="div12 fl word_red"><?php if($result['total_nums']==0){ echo "0%";}else{  echo round(($list[3]['nums']/$result['total_nums'])*100,2).'%';}?></li>
                <li class="div14 fl word_red"><?php echo sprintf('%.2f',$list[3]['amount']);?></li>
                <li class="div10 fl word_red last_v"><?php if($result['amount']==0){ echo "0%";}else{  echo round(($list[3]['amount']/$result['amount'])*100,2).'%';}?></li>
            </ul>
            <!--大类1-->
    <?php }?>
    <?php if(!empty($list[4])){ ?>
        <!--大类1-->
        <ul class="normal_line">
            <li class="div10 fl first_v"><?php echo $list[4]['b_name'];?></li>
            <li class="div12 fl">
                <!--小类-->
                <ul class="semi_level">
                    <!--二级的首行first_l-->
                    <?php foreach ($list[4]['small'] as $kk=>$vv):?>
                        <li><?php echo $vv['name'];?></li>
                    <?php endforeach;?>
                </ul>
                <!--小类-->
            </li>
            <li class="div14 fl">
                <!--款数-->
                <ul class="semi_level">
                    <?php foreach ($list[4]['small'] as $kk=>$vv):?>
                        <li><?php echo count(array_unique($vv['model']));?></li>
                    <?php endforeach;?>
                </ul>
                <!--款数-->
            </li>
            <li class="div14 fl">
                <!--冬季-->
                <ul class="semi_level">
                    <?php foreach ($list[4]['small'] as $kk=>$vv):?>
                        <li><?php if($result['all']==0){echo "0%";}{ echo round((count(array_unique($vv['model']))/$result['all'])*100,2)."%";}?></li>
                    <?php endforeach;?>
                </ul>
                <!--冬季-->
            </li>
            <li class="div14 fl">
                <!--订单数量-->
                <ul class="semi_level">
                    <?php foreach ($list[4]['small'] as $kk=>$vv):?>
                        <li><?php echo $vv['nums'];?></li>
                    <?php endforeach;?>
                </ul>
                <!--订单数量-->
            </li>
            <li class="div12 fl">
                <!--数量占比-->
                <ul class="semi_level">
                    <?php foreach ($list[4]['small'] as $kk=>$vv):?>
                        <li><?php if($result['total_nums']==0){ echo "0%";}else{  echo round(($vv['nums']/$result['total_nums'])*100,2).'%';}?></li>
                    <?php endforeach;?>
                </ul>
                <!--数量占比-->
            </li>
            <li class="div14 fl">
                <!--订货金额-->
                <ul class="semi_level">
                    <?php foreach ($list[4]['small'] as $kk=>$vv):?>
                        <li><?php echo sprintf('%.2f',$vv['amount']);?></li>
                    <?php endforeach;?>
                </ul>
                <!--订货金额-->
            </li>
            <li class="div10 fl last_v">
                <!--金额占比-->
                <ul class="semi_level">
                    <?php foreach ($list[4]['small'] as $kk=>$vv):?>
                        <li><?php if($result['total_nums']==0){ echo "0%";}else{  echo round(($vv['amount']/$result['amount'])*100,2).'%'; }?></li>
                    <?php endforeach;?>
                </ul>
                <!--金额占比-->
            </li>
        </ul>
        <ul class="statistics_line">
            <li class="div22 fl first_v"><?php echo $list[4]['b_name'];?></li>
            <li class="div14 fl word_red"><?php echo count(array_unique($list[4]['model']))?></li>
            <li class="div14 fl word_red"><?php echo round(count(array_unique($list[4]['model']))/$result['all'],2)*100.."%"; ?></li>
            <li class="div14 fl word_red"><?php echo $list[4]['nums'];?></li>
            <li class="div12 fl word_red"><?php if($result['total_nums']==0){ echo "0%";}else{  echo round(($list[4]['nums']/$result['total_nums'])*100,2).'%';}?></li>
            <li class="div14 fl word_red"><?php echo sprintf('%.2f',$list[4]['amount']);?></li>
            <li class="div10 fl word_red last_v"><?php if($result['amount']==0){ echo "0%";}else{  echo round(($list[4]['amount']/$result['amount'])*100,2).'%';}?></li>
        </ul>
        <!--大类1-->
    <?php } ?>
<!--总计-->
<ul class="statistics_line">
    <li class="div22 fl first_v">订货总计</li>
    <li class="div14 fl word_red last_l"><?php echo count(array_unique($result['model']));?></li>
    <li class="div14 fl word_red last_l"><?php echo $result['all']?'100%':'0%';?></li>
    <li class="div14 fl word_red last_l"><?php echo $result['total_nums'];?></li>
    <li class="div12 fl word_red last_l"><?php echo $result['total_nums']?'100%':'0%';?></li>
    <li class="div14 fl word_red last_l"><?php echo sprintf('%.2f',$result['amount']);?></li>
    <li class="div10 fl word_red last_v last_l"><?php echo $result['total_nums']?'100%':'0%';?></li>
</ul>
<!--总计-->
</div>
<?php echo $this->renderPartial('/common/_footer_order',array());?>