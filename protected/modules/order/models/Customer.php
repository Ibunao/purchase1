<?php

/**
 * "ectools_payments" 数据表模型类.
 *
 * @author        chenfenghua <843958575@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       mall.model
 * @license       http://www.octmami.com/license
 * @version       v1.0.0
 */
class Customer extends BaseModel
{
    /**
     * @return string 相关的数据库表的名称
     */
	public function tableName()
	{
		return '{{customer}}';
	}


    /**
     * @return array 对模型的属性验证规则.
     */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

    public function getList($select=array(),$order='',$group='',$where=' 1=1 ' ){

        $sql = " select $select from {{customer}} where $where  group by  $group";
//        $criteria=new CDbCriteria;
//        $criteria->addCondition("disabled = 'false'");
//        $criteria->order = $order;
//        $criteria->group = $group;
        //$criteria->params($select);
//        $list = self::model()->findAll($criteria);
        $list = $this->QueryAll($sql);
        return $list;
    }


    //获取客户总订货指标 目标达成率
    public function  getCustomerTargets($param){
        $where = '';
        if(!empty($param['purchase'])){
            $where .=' and purchase_id= '.$param['purchase'];
        }

        if(!empty($param['type'])){
            $where .=' and type= "'.$param['type'].'"';
        }

        $sql = " select sum(target) as target_sum from meet_customer where disabled='false' $where";
        $res = $this->QueryRow($sql);
        if($res['target_sum']){

            return $res['target_sum'];
        }
        return 0;
    }

    //获取已选客户总订货指标
    public function getChooseCustomerTargets($params){
        //var_dump($param);die;
        $where = '';
        if(!empty($params['purchase'])){
            $where .= " and purchase_id= '".$params['purchase']."' ";
        }
        if(!empty($params['department'])){
            $where  .= " and department= '".$params['department']."' ";
        }

        if(!empty($params['leader'])){
            $where  .= " and leader= '".$params['leader']."' ";
        }
        if(!empty($params['name'])){
            $where  .= " and  name like '%".$params['name']."%'";
        }
        if(!empty($params['leader_name'])){
            $where  .= " and  agent like '%".$params['leader_name']."%' or leader_name like  '%".$params['leader_name']."%'   ";
        }
        if(!empty($params['code'])){
            $where .= " and code='{$params['code']}'";
        }
        //判断顾客类型
        if(!empty($params['type'])){
            $where  .= " and type= '".$params['type']."' ";
        }
        if(!empty($params['area'])){
            $where .=' and area= "'.$params['area'].'"';
        }
        if(!empty($params['login'])) {
            if($params['login'] == 1) {
                $where .= ' and login is not null';
            }elseif($params['login'] == 2 ) {
                $where .= ' and login is null';
            }
        }
        $sql = " select sum(target) as target_sum from meet_customer where disabled='false'  $where";
        $res = $this->QueryRow($sql);
        if($res['target_sum']){
            return $res['target_sum'];
        }
        return 0;
    }

    public function  getCustomerInfo($code){
        $sql = "select * from meet_customer where code='".$code."'";
        return $this->QueryRow($sql);
    }


    public function  getCustomerOrder($customer_id){
        $sql = "select * from meet_order where customer_id='".$customer_id."'";
        $row = $this->QueryRow($sql);
        return $row;
    }

    public function  getDepartment($department){
        $sql = "select * from meet_customer where department='".$department."'";
        $row = $this->QueryRow($sql);
        return $row;
    }

    /**
     * 返回指定的AR类的静态模型.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Admin the static model class
     */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
