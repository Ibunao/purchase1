<?php
/**
 *
 * @author       zangmiao <838881690@qq.com>
 * @copyright    Copyright (c) 2011-2016 octmami.All Rights Reserved.
 * @link         http://mall.octmami.com
 * @version      v1.2
 */
$this->breadcrumbs=array(
    '商品管理',
    '商品管理'=>'/admin.php?r=order/product/index',
);
?>
<h3>TIP:请先选择：<b class="text-info">标示错误</b>,后在刷新页面，选择：一键处理错误商品</h3>
<h4>当商品ID下方没有出现“<b class="text-info">标示错误</b>“时表示此错误产品已被购买无法做任何操作</h4>
<?php if($is_error){ ?><a href="/admin.php?r=order/product/dealerror"><button class="btn btn-sm btn-success">一键处理错误商品</button></a><?php } ?>
<table class="table table-hover">
    <tr>
        <td>商品id</td>
        <td>商品流水号</td>
        <td>商品名称</td>
        <td>商品货号</td>
        <td>商品处理结果</td>
    </tr>
    <?php
    if(!empty($result)){
        foreach($result as $val){
    ?>
        <tr>
            <td><?php if($val['is_order'] <= 0 ){ ?><button data-product="<?php echo $val['product_id']; ?>" class="btn delete btn-sm btn-danger">标示错误</button><?php } ?></td>
            <td><?php echo $val['serial_num']; ?></td>
            <td><?php echo $val['name']; ?></td>
            <td><?php echo $val['product_sn']; ?></td>
            <td><?php if($val['is_error']){echo "<b class='text-danger'>未处理</b>";}else{echo "已处理";} ?></td>
        </tr>
    <?php
        }}
    ?>
</table>
<script>
    $(document).ready(function () {
       $(".delete").click(function () {
         var product_id = $(this).attr("data-product");
           $.ajax({
              data :{product_id:product_id},
               dataType:'json',
               type:"post",
               url:"/admin.php?r=order/product/deleteErrorProduct",
               success:function(data){
                    if(data.code=='200'){
                        alert(data.msg);
                    }else if(data.code=='400'){
                        alert(data.msg);
                    }
                   history.go(0);
               }
           });
       });
    });
</script>
