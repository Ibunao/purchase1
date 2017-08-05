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
class Cat_small extends BaseModel
{


    /**
     * @return string
     */
    public function tableName()
	{
		return '{{cat_small}}';
	}



    public function getList($select=array(),$order='',$group='' ){
        $criteria=new CDbCriteria;
        $criteria->order = $order;
        $criteria->group = $group;
        //$criteria->params($select);
        $list = self::model()->findAll($criteria);
        return $list;
    }

	/**
	 * 获取小类
	 * @return mixed
	 */
	public function getCatSmall(){
		$result = Yii::app()->cache->get("cat_small");
		if(!$result){
			$sql = "SELECT * FROM {{cat_small}}";
			$result = $this->QueryAll($sql);
			Yii::app()->cache->set("cat_small", $result, 86400);
		}
		return $result;
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


}
