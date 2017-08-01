<?php
/**
 * 客户管理的的VIEW视图
 *
 * @author        zangmiao <838881690@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       Manage.controller
 * @license       http://www.octmami.com/license
 * @version       v1.2.0
 */
$this->breadcrumbs=array(
    '内容管理',
    '客户管理'=>'/admin.php?r=order/manage/index',
);
?>
<style>
    body{
        min-width: 1301px;
    }
    caption{
        background-color: #438EB9;
        width: 100%;
        height: 40px;
        color: #fff;
        font-size: 16px;
        padding:8px 0 0 20px;
    }
    .form-group{
        margin:4px;padding: 0;
        float: left;
    }
    form{
        background-color:#f5f5f5;
        width: 100%;
        height: 110px;padding: 0 10px 0 10px;
    }
    select,#box{
        width: 150px;
        height: 35px;
        margin-top: 20px;
    }
    i.icon-pencil{
        padding: 5px;font-size:17px;
    }
</style>
<form action="" method="get">
    <input type="hidden" name="r" value="order/manage/index">
    <div class="form-group">
        <label for="name">客户代码:</label>
        <input type="text" name="param[code]"  class="se" id="box" value="<?php echo !empty($param['code'])?$param['code']:''; ?>">
    </div>
    <div class="form-group">
        <label for="name">客户名称:</label>
        <input type="text" name="param[name]"  class="se" id="box" onKeypress="javascript:if(event.keyCode == 32)event.returnValue = false;" value="<?php echo !empty($param['name'])?$param['name']:''; ?>">
    </div>
    <div class="form-group">
        <label for="name">客户类型:</label>
        <select class="se" name="param[type]">
            <option value="">请选择</option>
            <?php  foreach($insert_option['type'] as $k=>$type){ ?>
                <option value="<?php echo $k; ?>" <?php if(!empty($param['type'])&&$param['type']==$type){echo "selected";} ?>><?php echo $type; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="form-group">
        <label for="name">订货会:</label>
        <select class="se" name="param[purchase_id]">
            <option value="">请选择</option>
            <?php  foreach($insert_option['purchase'] as $k=>$purchase_id){ ?>
                <option value="<?php echo $k; ?>" <?php if(!empty($param['purchase_id'])&&$param['purchase_id']==$k){echo "selected";} ?>><?php echo $purchase_id ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="form-group">
        <label for="name">省份:</label>
        <select class="se" name="param[province]">
            <option value="">请选择</option>
            <?php  foreach($insert_option['province'] as $k=>$province){ ?>
                <option value="<?php echo $province; ?>" <?php if(!empty($param['province'])&&$param['province']==$province){echo "selected";} ?>><?php echo $province; ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="form-group">
        <label for="name">区域:</label>
        <select class="se" name="param[area]">
            <option value="">请选择</option>
            <?php  foreach($insert_option['area'] as $k=>$area){ ?>
                <option value="<?php echo $area; ?>" <?php if(!empty($param['area'])&&$param['area']==$area){echo "selected";} ?>><?php echo $area; ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="form-group">
        <label for="name">用户是否订货:</label>
        <select class="se" name="param[order]">
            <option value="">请选择</option>
            <option value="1" <?php if(!empty($param['order']) && $param['order'] == '1'){ echo 'selected';} ?>>已定货</option>
            <option value="2" <?php if(!empty($param['order']) && $param['order'] == '2'){ echo 'selected';} ?>>未定货</option>
        </select>
    </div>

    <div class="col-sm-12 form-group">
        <button class="btn btn-primary  btn-xs" type="submit">搜索</button>
        <button class="btn btn-primary clear btn-xs"><a href="?r=order/manage/index" style="color:#fff">清空</a></button>
    </div>
</form>
<script>
    $(".clear").click(function () {
        $('option:selected').removeAttr('selected');
        $("#box").attr("value","");
    });
</script>
<div  class="col-sm-12 form-group">
    <a href="/admin.php?r=order/manage/add"><button class="btn btn-default btn-sm">添加用户</button></a>
    <a href="/admin.php?r=order/manage/import"><button class="btn btn-default btn-sm">批量导入用户</button></a>
    <a href="/admin.php?r=order/manage/export"><button class="btn btn-default btn-sm">导出用户</button></a>
</div>
<table  class="org table table-hover table-bordered table-striped">
    <caption class="text-left">用户列表</caption>
    <thead>
        <tr>
            <td width="25"><input type="checkbox"></td>
            <td width="30">操作</td>
            <td width="120">客户/店铺名称</td>
            <td width="120">客户/店铺代码</td>
            <td width="120">用户手机</td>
            <td width="100">订货会</td>
            <td width="80">订货指标</td>
            <td width="80">已定货金额</td>
            <td width="80">达成率</td>
            <td width="80">未完成金额</td>
        </tr>
    </thead>
    <tbody>
        <?php
            foreach($select_result as $k=>$v){
        ?>
        <tr>
            <td width="25"><input type="checkbox"></td>
            <td width="30">
                <a href="/admin.php?r=order/manage/update&id=<?php echo $v['customer_id'];?>"><i class="icon-pencil"></i></a>
                <a href="/admin.php?r=order/manage/copy&id=<?php echo $v['customer_id']; ?>"><i class="icon-copy"></i></a>
            </td>
            <td width="120"><?php echo $v['name']; ?></td>
            <td width="120"><?php echo $v['code']; ?></td>
            <td width="120"><?php echo $v['mobile'] ?></td>
            <td width="100"><?php echo $v['purchase_id']==1?"OCT":"UKI"; ?></td>
            <td width="60"><?php echo number_format($v['target']); ?></td>
            <td width="60"><?php echo number_format($v['cost_item']);?></td>
            <td width="60"><?php if($v['target']==0||$v['cost_item']==0){echo "0%";}else{echo round(($v['cost_item']/$v['target'])*100,2).'%';}?></td>
            <td width="60"><?php  $end=$v['target']-$v['cost_item']; if((int)$end<=0){echo "0";}else{echo $end;}?></td>
        </tr>
        <?php } ?>
    <tbody>
    <?php if($count_row==0){echo "<td colspan='10' align='center'><b><i>对不起，没有查到任何信息</i></b></td>"; }?>
</table>

<div class="row"  style="width:100%;background-color: #f5f5f5;padding: 20px">
        <?php $this->widget(
            'bootstrap.widgets.TbLinkPager',
            array(
                'pages' => $pages,
                'currentPage'=>$pageIndex,
                'pageSize'=>$this->pagesize
            )
        );?>
    </div>

