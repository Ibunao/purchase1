<?php

/**
 * "b2c_delivery" 数据表模型类.
 *
 * @author        chenfenghua <843958575@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       mall.model
 * @license       http://www.octmami.com/license
 * @version       v1.0.0
 */
class Purchase extends BaseModel
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{purchase}}';
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}


	public function getPurchase(){
		$result = Yii::app()->cache->get("purchase");
		if(!$result){
			$sql = "SELECT * FROM {{purchase}}";
			$result = $this->QueryAll($sql);
			Yii::app()->cache->set("purchase", $result, 86400);
		}
		return $result;
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
