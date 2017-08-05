<?php

/**
 * "ectools_order_bills" 数据表模型类.
 *
 * @author        chenfenghua <843958575@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       mall.model
 * @license       http://www.octmami.com/license
 * @version       v1.0.0
 */
class Cat_big extends BaseModel
{


    /**
     * @return string
     */
    public function tableName()
	{
		return '{{cat_big}}';
	}


    public function getList($select=array(),$order='',$group='' ){
//        $criteria=new CDbCriteria;
//        $criteria->order = $order;
//        $criteria->group = $group;
        //$criteria->params($select);
        //$list = self::model()->findAll($criteria);
        $sql ="select * from  {{cat_big}} ";
        $list = $this->QueryAll($sql);
        return $list;
    }



    public function cat_big_small(){
        $cat = array();
        $sql = " select * from meet_cat_big";
        $big = $this->QueryAll($sql);
        foreach($big as $k=> $v){
            $sql ="select small_id,small_cat_name from meet_cat_big_small where big_id=".$v['big_id'] ." GROUP BY small_id";
//            $sql ="select small_id, cat_name as small_cat_name from meet_cat_small where parent_id=".$v['big_id'];
            $small = $this->QueryAll($sql);
            if(!empty($small)){
                $cat[$v['big_id']]['big_cat_id'] =$v['big_id'];
                $cat[$v['big_id']]['big_cat_name'] =$v['cat_name'];
                $cat[$v['big_id']]['cat_small'] =$small;
            }
        }
        return  $cat ;
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

    /**
     * 获取大类
     */
    public function getCatBig(){
        $result = Yii::app()->cache->get("cat_big");
        if(!$result){
            $sql = "SELECT big_id, cat_name FROM {{cat_big}}";
            $result = $this->QueryAll($sql);
            Yii::app()->cache->set("cat_big", $result, 86400);
        }
        return $result;
    }
}
