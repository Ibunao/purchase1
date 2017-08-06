<?php
/**
 * 控制面板理
 *
 * @author     chenfenghua<843958575@qq.com>
 * @copyright  Copyright 2008-2015 mall.octmami.com
 * @version    1.0
 */

class DefaultController extends BaseController
{
    public function actionIndex()
    {
        $table = new Table();

        //今日用户登陆
        $login_num = $table->getAllDateNewLogin();
        $result['login_nums'] = count($login_num);

        //总订单金额 ??? 应该是除了作废的剩下的
        $all_order = $table->getAllOrderNum();
        $result['all_orders'] = $all_order['nums'];

        //已审核订单金额
        $today_order = $table->getAllOrderNum("1");
        $result['confirm_orders'] = $today_order['nums'];

        //总订货指标
        $all_target = $table->getAllUserTarget();
        $result['all_target'] = $all_target['targets'];

        //加盟指标
        $all_target = $table->getAllUserTarget('jm');
        $result['jm_target'] = $all_target['targets'];

        //加盟已定货总数
        $all_target = $table->getOrderNumbers('jm');
        $result['jm_orders'] = $all_target['nums'];

        //加盟已定货
        $all_target = $table->getOrderNumbers('jm_active');
        $result['jm_active'] = $all_target['nums'];

        //加盟已定货
        $all_target = $table->getOrderNumbers('jm_confirm');
        $result['jm_confirm'] = $all_target['nums'];

        //加盟已定货??? 这不是加盟的吧，是所有的
        $all_target = $table->getOrderNumbers('jm_all_order');
        $result['jm_all_nums'] = $all_target['nums'];

        //加盟已定货
        $all_target = $table->getOrderNumbers('jm_finish');
        $result['jm_finish'] = $all_target['nums'];

        //总达成率
        if($result['all_target'] != 0 ){
            $result['all_targets'] = round($result['confirm_orders'] / $result['all_target']*100, 2);
        }else{
            $result['all_targets'] = 0;
        }

        //加盟审核后达成率
        if($result['jm_target'] != 0 ){
            $result['jm_targets'] = round($result['jm_finish'] / $result['jm_target'] *100, 2);
        }else{
            $result['jm_targets'] = 0;
        }

        $this->render('index',array(
            'res' => $result
        ));
    }
//没用
    public function actionCheck(){
        $table = new Table();
        $result = $table->checkRepeat();
        if(!$result){
            echo "没有重复数据";
        }else{
            var_dump($result);
        }
    }
//没用，倒入数据用的
    public function actionColor(){
        $filename = 'agent.csv';
        $result = ErpCsv::importCsvData($filename);
        $len_result = count($result);

        $value = "";
        for($i = 1; $i<$len_result; $i ++){
            $color_no = $result[$i][0];
            $color_name = $result[$i][1];
            $value .= ",('".$color_no."','".$color_name."')";
        }
        $value = substr($value, 1);
        $sql = "INSERT INTO `meet_agent` (agent_name, agent_code) VALUES ".$value;
        $tableModel = new Table();
        $tableModel->ModelExecute($sql);
    }
} 