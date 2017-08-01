<?php
/**
 * 权限角色管理
 */
class XAdminiAcl
{
    //权限配制数据
    public static $aclList = array(

        'order'=>array(
            'name'=>'订单',
            'ctl'=>array(
                array(
                    'name'=>'订单统计',
                    'list_ctl'=>array('default','order','cache','manage','product'),
                    'act'=>array(
                        'default'=>array(
                            'name'=>'商品订单汇总',
                            'default_id'=>'index',
                            'list_act'=>array('index'=>'商品订单汇总','update'=>'订单编辑','delete'=>'删除订单')
                        ),
                        'order'=>array(
                            'name'=>'客户订单汇总',
                            'default_id'=>'index',
                            'list_act'=>array('index'=>'客户订单汇总','check'=>'订单编辑','delete'=>'删除订单','import'=>'订单导入')
                        ),

                        'manage'=>array(
                            'name'=>'客户管理',
                            'default_id'=>'index',
                            'list_act'=>array('index'=>'客户管理','add'=>'客户添加')
                        ),
                        'product'=>array(
                            'name'=>'商品管理',
                            'default_id'=>'index',
                            'list_act'=>array('index'=>'商品管理','add'=>'商品添加')
                        ),
                    )

                )
            )
        ),
//        'goods'=>array(
//            'name'=>'商品',
//            'ctl'=>array(
//                array(
//                    'name'=>'商品汇总',
//                    'list_ctl'=>array('default'),
//                    'act'=>array(
//                        'default'=>array(
//                            'name'=>'商品列表',
//                            'default_id'=>'index',
//                            'list_act'=>array('index'=>'商品列表')
//                        ),
//                    )
//                ),
//            )
//        ),

//        'desktop'=>array(
//            'name'=>'系统',
//            'ctl'=>array(
//                array(
//                    'name'=>'管理员和权限',
//                    'list_ctl'=>array('role','user','table'),
//                    'act'=>array(
//                        'role'=>array(
//                            'name'=>'角色管理',
//                            'default_id'=>'index',
//                            'list_act'=>array('index'=>'角色列表','update'=>'角色编辑')
//                        ),
//                        'user'=>array(
//                            'name'=>'操作员管理',
//                            'default_id'=>'index',
//                            'list_act'=>array('index'=>'操作员列表')
//                        ),
//                        'table'=>array(
//                            'name'=>'缓存管理',
//                            'default_id'=>'index',
//                            'list_act'=>array('index'=>'缓存列表')
//                        ),
//                    )
//                )
//            )
//        ),

    );

    /**
     * 后台菜单过滤
     *
     */
    static public function filterMenu($acl_list,$super)
    {
        $item = self::$aclList;
        if ($super == 1) return $item;
        foreach ($item as $k=>$v) {
            foreach ($v['ctl'] as $kk=>$vv) {
                foreach ($vv['act'] as $kkk=>$vvv) {
                    $acl = $k.'_'.$kkk.'_'.$vvv['default_id'];
                    if (!in_array($acl,$acl_list)) {
                        unset($item[$k]['ctl'][$kk]['act'][$kkk]);
                    }
                }
                if (empty($item[$k]['ctl'][$kk]['act'])) unset($item[$k]['ctl'][$kk]);
            }
            if (empty($item[$k]['ctl'])) unset($item[$k]);
        }
        return $item;
    }

    /**
     * 系统角色管理
     *
     * @return array
     */
    public static function RoleMenu()
    {
        return self::$aclList;
    }
}

